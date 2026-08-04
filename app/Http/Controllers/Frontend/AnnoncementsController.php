<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AnnoncementsController extends Controller
{
        // ─── Public Listing for Frontend (no auth) ─────────────────────────────────
    public function publicIndex()
    {
        try {
            $announcements = Announcement::ordered()
                ->get([
                    'id',
                    'name',
                    'attachment',
                    'link',
                    'sequence',
                    'meta',
                ]);

            Log::info('Public announcements fetched', [
                'count' => $announcements->count(),
            ]);

            return response()->json([
                'status' => true,
                'data'   => $announcements,
            ]);

        } catch (\Exception $e) {

            Log::error('Failed to fetch public announcements', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to load announcements.',
            ], 500);
        }
    }
}
