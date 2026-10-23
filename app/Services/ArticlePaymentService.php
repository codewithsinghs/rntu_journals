<?php

namespace App\Services;

use App\Exceptions\PaymentException;
use App\Models\ArticlePayment;
use App\Models\SubmitArticle;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class ArticlePaymentService
{
    public const STANDARD_FEE = 2000.00;
    public const CURRENCY     = 'INR';
    public const GATEWAY      = 'paytm';

    public function __construct(private PaytmGatewayService $gateway) {}

    public function payableAmount(SubmitArticle $article): float
    {
        $waiver = $article->waiver;

        if ($waiver && $waiver->partial_payment && (float) $waiver->payable_amount > 0) {
            return (float) $waiver->payable_amount;
        }

        return self::STANDARD_FEE;
    }

    public function isPaid(SubmitArticle $article): bool
    {
        return $article->payments()->where('status', 'paid')->exists();
    }

    public function assertCanPay($user, SubmitArticle $article): void
    {
        if (!$user || (int) $article->user_id !== (int) $user->id) {
            throw new PaymentException('You are not authorized to pay for this submission.', 403);
        }

        if (($article->review->current_stage ?? null) !== 'with_author_payment') {
            throw new PaymentException('This submission is not awaiting payment.', 409);
        }

        if ($this->isPaid($article)) {
            throw new PaymentException('Payment for this submission is already completed.', 409);
        }

        if ($article->payments()->where('status', 'pending')->exists()) {
            throw new PaymentException('A previous payment is still being processed. Please check again shortly.', 409);
        }
    }

    /* ───────────── Step 1: initiate (authenticated) ───────────── */

    public function initiate(SubmitArticle $article, $user): array
    {
        $this->assertCanPay($user, $article);

        $amount    = $this->payableAmount($article);
        $amountStr = number_format($amount, 2, '.', '');
        $orderId   = 'ART' . $article->id . '-' . time();

        $fields = $this->gateway->buildPaymentParams(
            $orderId,
            $amountStr,
            'CUST' . $user->id,
            route('payments.paytm.callback')
        );

        ArticlePayment::create([
            'submit_article_id' => $article->id,
            'user_id'           => $user->id,
            'gateway'           => self::GATEWAY,
            'order_id'          => $orderId,
            'amount'            => $amount,
            'currency'          => self::CURRENCY,
            'status'            => 'created',
        ]);

        Cache::put("paytm:fields:{$orderId}", $fields, now()->addMinutes(15));

        return [
            'order_id'     => $orderId,
            'amount'       => $amountStr,
            'currency'     => self::CURRENCY,
            'redirect_url' => URL::temporarySignedRoute(
                'payments.paytm.redirect',
                now()->addMinutes(15),
                ['orderId' => $orderId]
            ),
        ];
    }

    /* ───────────── Step 2: data for the auto-post form ───────────── */

    public function redirectFormData(string $orderId): array
    {
        $payment = ArticlePayment::where('order_id', $orderId)->first();

        if (!$payment || $payment->status !== 'created') {
            throw new PaymentException('This payment link is no longer valid.', 410);
        }

        $fields = Cache::get("paytm:fields:{$orderId}");

        if (!$fields) {
            throw new PaymentException('Payment session expired. Please start again.', 410);
        }

        return [
            'action' => $this->gateway->paymentPageUrl(),
            'fields' => $fields,
        ];
    }

    /* ───────────── Step 3: callback from Paytm (public) ───────────── */

    /** @return array{payment: ArticlePayment, outcome: string} */
    public function handleCallback(array $params): array
    {
        $orderId = $params['ORDERID'] ?? null;

        if (!$orderId) {
            throw new PaymentException('Invalid payment callback.', 400);
        }

        if (!$this->gateway->verifyCallbackChecksum($params)) {
            Log::warning('Paytm callback checksum mismatch', ['order_id' => $orderId]);
            throw new PaymentException('Invalid payment callback signature.', 400);
        }

        return $this->reconcile($orderId);
    }

    /**
     * Confirm an order with Paytm (server-to-server) and update our record.
     * Safe to call many times.
     *
     * @return array{payment: ArticlePayment, outcome: string}
     */
    public function reconcile(string $orderId): array
    {
        return DB::transaction(function () use ($orderId) {
            $payment = ArticlePayment::where('order_id', $orderId)->lockForUpdate()->first();

            if (!$payment) {
                throw new PaymentException('Payment record not found.', 404);
            }

            if ($payment->status === 'paid') {
                return ['payment' => $payment, 'outcome' => 'success'];
            }

            $body   = $this->gateway->orderStatus($orderId);
            $status = $body['resultInfo']['resultStatus'] ?? null;

            if ($status === 'TXN_SUCCESS') {
                $paid = (float) ($body['txnAmount'] ?? 0);

                if (abs($paid - (float) $payment->amount) > 0.001) {
                    Log::error('Paytm paid amount mismatch', [
                        'order_id' => $orderId,
                        'expected' => $payment->amount,
                        'paid'     => $paid,
                    ]);

                    $payment->update([
                        'status'         => 'failed',
                        'failure_reason' => 'Amount mismatch',
                        'response'       => $body,
                    ]);

                    return ['payment' => $payment, 'outcome' => 'failed'];
                }

                $payment->update([
                    'txn_id'       => $body['txnId'] ?? null,
                    'bank_txn_id'  => $body['bankTxnId'] ?? null,
                    'payment_mode' => $body['paymentMode'] ?? null,
                    'status'       => 'paid',
                    'paid_at'      => now(),
                    'response'     => $body,
                ]);

                Log::info('Article payment completed', [
                    'order_id' => $orderId,
                    'txn_id'   => $payment->txn_id,
                    'amount'   => $payment->amount,
                ]);

                return ['payment' => $payment, 'outcome' => 'success'];
            }

            if ($status === 'PENDING') {
                $payment->update(['status' => 'pending', 'response' => $body]);

                return ['payment' => $payment, 'outcome' => 'pending'];
            }

            $payment->update([
                'status'         => 'failed',
                'failure_reason' => $body['resultInfo']['resultMsg'] ?? 'Payment was not successful.',
                'response'       => $body,
            ]);

            return ['payment' => $payment, 'outcome' => 'failed'];
        });
    }

    /* ───────────── Receipt ───────────── */

    public function receipt(string $orderId, $user): ArticlePayment
    {
        $payment = ArticlePayment::with('article')->where('order_id', $orderId)->first();

        if (!$payment) {
            throw new PaymentException('Receipt not found.', 404);
        }

        $isOwner = $user && (int) $payment->user_id === (int) $user->id;
        if (!$isOwner && !($user && $user->can('view submit article'))) {
            throw new PaymentException('You are not authorized to view this receipt.', 403);
        }

        if (in_array($payment->status, ['pending', 'created'], true)) {
            try {
                $payment = $this->reconcile($orderId)['payment']->load('article');
            } catch (\Throwable $e) {
                Log::warning('Receipt refresh failed', ['order_id' => $orderId]);
            }
        }

        return $payment;
    }
}