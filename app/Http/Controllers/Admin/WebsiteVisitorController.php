<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebsiteVisitor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class WebsiteVisitorController extends Controller
{
    public function adminIndex(Request $request): JsonResponse
    {
        try {
            $visitors = WebsiteVisitor::query()
                ->when($request->search, function ($query, $search) {
                    $query->where('ip_address', 'like', "%{$search}%")
                        ->orWhere('url', 'like', "%{$search}%")
                        ->orWhere('user_agent', 'like', "%{$search}%");
                })
                ->latest()
                ->paginate($request->per_page ?? 20);

            return response()->json([
                'success' => true,
                'data'    => $visitors,
            ]);
        } catch (\Throwable $e) {
            Log::error('WebsiteVisitor adminIndex failed: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(), // remove this line in production
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $visitor = WebsiteVisitor::findOrFail($id);

            return response()->json([
                'success' => true,
                'data'    => $visitor,
            ]);
        } catch (\Throwable $e) {
            Log::error('WebsiteVisitor show failed: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $visitor = WebsiteVisitor::findOrFail($id);
            $visitor->delete();

            return response()->json([
                'success' => true,
                'message' => 'Visitor record deleted successfully.',
            ]);
        } catch (\Throwable $e) {
            Log::error('WebsiteVisitor destroy failed: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}