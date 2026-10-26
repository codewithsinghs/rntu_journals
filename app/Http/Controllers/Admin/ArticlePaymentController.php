<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\PaymentException;
use App\Http\Controllers\Controller;
use App\Models\SubmitArticle;
use App\Services\ArticlePaymentService;
use App\Support\ReceiptData;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ArticlePaymentController extends Controller
{
    public function __construct(private ArticlePaymentService $payments) {}

    /** POST /api/admin/submit-articles/{uuid}/payment/initiate */
    public function initiate(Request $request, string $uuid)
    {
        try {
            $article = SubmitArticle::with(['review', 'waiver'])->where('uuid', $uuid)->firstOrFail();
            $data    = $this->payments->initiate($article, $request->user('api'));

            return response()->json(['status' => true, 'data' => $data]);
        } catch (PaymentException $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], $e->status());
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => false, 'message' => 'Submission not found.'], 404);
        } catch (\Throwable $e) {
            Log::error('Failed to initiate payment', ['submission_id' => $uuid, 'error' => $e->getMessage()]);

            return response()->json([
                'status'  => false,
                'message' => 'Could not start the payment. Please try again.',
            ], 500);
        }
    }

    /** GET /api/admin/payments/{orderId}/receipt */
    public function receipt(Request $request, string $orderId)
    {
        try {
            $user = $request->user('api');
            $p    = $this->payments->receipt($orderId, $user);

            return response()->json(['status' => true, 'data' => ReceiptData::build($p, $user)]);
        } catch (PaymentException $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], $e->status());
        }
    }

    // downloadReceipt() was removed: the PDF is created in the browser (payment.js),
    // so DomPDF and the /receipt/download route are no longer needed.
}