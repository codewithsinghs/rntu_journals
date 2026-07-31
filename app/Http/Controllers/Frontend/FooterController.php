<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\FooterService;
use Illuminate\Http\JsonResponse;

class FooterController extends Controller
{
    public function __construct(private FooterService $footerService) {}

    public function index(): JsonResponse
    {
        try {
            $data = $this->footerService->getFooterData();

            return response()->json([
                'status'  => true,
                'message' => 'Footer data fetched successfully.',
                'data'    => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to fetch footer data.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}