<?php

namespace App\Services;

use App\Exceptions\PaymentException;
use App\Models\ArticlePayment;
use App\Models\Issue;
use App\Models\SubmitArticle;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class ArticlePaymentService
{
    /** Fallback only; the real value comes from config/payment.php */
    public const STANDARD_FEE = 1000.00;
    public const CURRENCY     = 'INR';
    public const GATEWAY      = 'paytm';

    public function __construct(private PaytmGatewayService $gateway) {}

    public function payableAmount(SubmitArticle $article): float
    {
        $waiver = $article->waiver;

        if ($waiver && $waiver->partial_payment && (float) $waiver->payable_amount > 0) {
            return (float) $waiver->payable_amount;
        }

        // Same source the popup uses (SubmitArticleController::standardFee)
        return (float) config('payment.standard_fee', self::STANDARD_FEE);
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
     * Confirm an order with Paytm and, if it is paid, publish the article.
     * Safe to call many times (callback, receipt refresh, retries).
     *
     * @return array{payment: ArticlePayment, outcome: string}
     */
    public function reconcile(string $orderId): array
    {
        $result = $this->reconcileOrder($orderId);

        // Runs after the payment transaction has committed. Also covers payments
        // that were marked "paid" earlier but never published.
        if ($result['outcome'] === 'success') {
            $this->publishPaidArticle($result['payment']);
        }

        return $result;
    }

    /**
     * Server-to-server check with Paytm + update of our payment record.
     *
     * @return array{payment: ArticlePayment, outcome: string}
     */
    private function reconcileOrder(string $orderId): array
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

    /* ───────────── Paid -> Published ───────────── */

    /**
     * Failed / pending payments never reach this, so those articles
     * stay on "Awaiting Payment".
     */
    private function publishPaidArticle(ArticlePayment $payment): void
    {
        try {
            $article = SubmitArticle::with('review')->find($payment->submit_article_id);

            if (!$article || ($article->review->current_stage ?? null) !== 'with_author_payment') {
                return; // already published or not in the payment stage
            }

            $issue = $article->issue_id
                ? Issue::find($article->issue_id)
                : Issue::where('journal_id', $article->journal_id)
                    ->orderByDesc('published_date')
                    ->first();

            if (!$issue) {
                // Payment is safe. The editor's Publish action still works once an issue exists.
                Log::error('Paid but no issue available to publish into', [
                    'article_id' => $article->id,
                    'order_id'   => $payment->order_id,
                ]);
                return;
            }

            $hasManualPages = !empty($article->page_start) && !empty($article->page_end);
            $pageCount      = $hasManualPages ? 0 : $this->countPdfPages($article->signed_manuscript_pdf);

            DB::transaction(function () use ($article, $issue, $pageCount, $hasManualPages) {
                // Lock the review row so callback + receipt refresh can't publish twice
                $review = $article->review()->lockForUpdate()->first();

                if (!$review || $review->current_stage !== 'with_author_payment') {
                    return;
                }

                $article->issue_id = $issue->id;

                if (!$hasManualPages) {
                    $lastEnd = SubmitArticle::where('issue_id', $issue->id)
                        ->lockForUpdate()
                        ->max('page_end') ?? 0;

                    $article->page_start = $lastEnd + 1;
                    $article->page_end   = $lastEnd + $pageCount;
                }

                $article->save();

                $review->update([
                    'final_status'  => 'published',
                    'current_stage' => 'published',
                    'is_published'  => true,
                    'published_at'  => now(),
                ]);
            });

            Log::info('Article published after successful payment', [
                'article_id' => $article->id,
                'order_id'   => $payment->order_id,
            ]);
        } catch (\Throwable $e) {
            // Never break the payment response because publishing failed
            Log::error('Auto-publish after payment failed', [
                'order_id' => $payment->order_id,
                'error'    => $e->getMessage(),
            ]);
        }
    }

    private function countPdfPages(?string $path): int
    {
        if (!$path) {
            return 1;
        }

        try {
            $pdf = (new \Smalot\PdfParser\Parser())->parseFile(Storage::disk('public')->path($path));

            return max(1, count($pdf->getPages()));
        } catch (\Throwable $e) {
            Log::warning('Could not count PDF pages', ['path' => $path, 'error' => $e->getMessage()]);

            return 1;
        }
    }

    /* ───────────── Receipt ───────────── */

    public function receipt(string $orderId, $user): ArticlePayment
    {
        $payment = ArticlePayment::with('article')->where('order_id', $orderId)->first();

        if (!$payment) {
            throw new PaymentException('Receipt not found.', 404);
        }

        $isOwner  = $user && (int) $payment->user_id === (int) $user->id;
        $isEditor = $user && ($user->can('view all articles') || $user->can('view submit article'));

        if (!$isOwner && !$isEditor) {
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