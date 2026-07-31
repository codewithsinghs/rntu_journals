<?php
// app/Http/Controllers/Api/PageResolverController.php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\CurrentPageResolver;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PageResolverController extends Controller
{
    public function __construct(private CurrentPageResolver $resolver) {}

    public function resolve(Request $request): JsonResponse
    {
        try {
            $path = $request->query('path', '/');
            $pageKey = $this->resolver->resolveFromPath($path);

            return response()->json([
                'status' => true,
                'data'   => ['page' => $pageKey],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to resolve page.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}