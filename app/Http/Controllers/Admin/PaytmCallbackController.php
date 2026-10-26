<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\PaymentException;
use App\Http\Controllers\Controller;
use App\Services\ArticlePaymentService;
use App\Support\ReceiptData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaytmCallbackController extends Controller
{
    public function __construct(private ArticlePaymentService $payments) {}

    /** GET /api/payments/paytm/redirect/{orderId} (signed, no JWT) */
    public function redirect(string $orderId)
    {
        try {
            return view('admin.payment.paytm-redirect', $this->payments->redirectFormData($orderId));
        } catch (PaymentException $e) {
            return $this->backToApp('failed', $orderId, $e->getMessage());
        }
    }

    /**
     * POST|GET /api/payments/paytm/callback (public)
     *
     * POST = Paytm result, checksum verified -> render receipt page directly.
     * GET  = unverified browser visit -> reconcile with Paytm, then redirect.
     */
    public function callback(Request $request)
    {
        $orderId = (string) ($request->input('ORDERID') ?? $request->input('order_id', ''));

        try {
            if ($request->isMethod('post')) {
                $result = $this->payments->handleCallback($request->all());

                return view('admin.payment.receipt-page', [
                    'r'       => ReceiptData::build($result['payment']->load('article')),
                    'outcome' => $result['outcome'],
                ]);
            }

            if ($orderId === '') {
                return $this->backToApp('failed', '', 'Payment was cancelled or not completed.');
            }

            $result = $this->payments->reconcile($orderId);

            return $this->backToApp($result['outcome'], $orderId);
        } catch (PaymentException $e) {
            Log::warning('Paytm callback rejected', ['order_id' => $orderId, 'reason' => $e->getMessage()]);

            return $this->backToApp('failed', $orderId, $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Paytm callback error', ['order_id' => $orderId, 'error' => $e->getMessage()]);

            return $this->backToApp('failed', $orderId, 'Something went wrong while verifying the payment.');
        }
    }

    /** 303 so the browser turns Paytm's POST into a GET on your page. */
    private function backToApp(string $outcome, string $orderId, ?string $message = null)
    {
        $base = (string) config('paytm.result_url');

        if ($base === '' || str_contains($base, 'payments/paytm')) {
            Log::warning('paytm.result_url is missing or invalid, falling back to site root', ['value' => $base]);
            $base = url('/');
        }

        $query = http_build_query(array_filter([
            'status'   => $outcome,
            'order_id' => $orderId,
            'message'  => $message,
        ]));

        $url = $base . (str_contains($base, '?') ? '&' : '?') . $query;

        return redirect()->away($url, 303);
    }
}