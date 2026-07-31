<?php
// app/Http/Controllers/Api/SettingController.php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use App\Services\SiteSettingService;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    public function __construct(private SiteSettingService $siteSettingService) {}

    public function logo(): JsonResponse
    {
        try {
            $data = $this->siteSettingService->getLogoData();

            return response()->json([
                'status'  => true,
                'message' => 'Logo fetched successfully.',
                'data'    => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to fetch logo.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}