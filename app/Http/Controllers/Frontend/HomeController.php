<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\HomeBasicContent;
use App\Models\Journal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{

    public function index()
    {
        return view('frontend.home');
    }


    public function content()
    {
        try {
            $content = HomeBasicContent::latest()->first();
            return response()->json(['status' => true, 'data' => $content]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch home content', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Failed to fetch content.'], 500);
        }
    }


    public function journalsList()
    {
        try {
            $journals = Journal::where('is_active', 1)
                ->orderBy('sequence')
                ->get()
                ->map(function ($journal) {
                    $data = $journal->toArray();
                    $data['cover_image_url'] = $journal->cover_image
                        ? Storage::url($journal->cover_image)
                        : null;
                    return $data;
                });

            return response()->json(['status' => true, 'data' => $journals]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch journals', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Failed to fetch journals.'], 500);
        }
    }


    public function announcementsList()
    {
        try {
            $announcements = Announcement::ordered()->get();
            return response()->json(['status' => true, 'data' => $announcements]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch announcements', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Failed to fetch announcements.'], 500);
        }
    }


    public function latestArticles()
    {
        try {
            $approvedArticles = DB::table('submit_articles as sa')
                ->join('article_reviews as ar', 'ar.submit_article_id', '=', 'sa.id')
                ->where('ar.editor_status', 'approved')
                ->orderByDesc('ar.updated_at')
                ->select(
                    'sa.id',
                    'sa.uuid',
                    'sa.manuscript_title',
                    'sa.full_name',
                    'sa.journal_id',
                    'ar.updated_at as created_at'
                )
                ->limit(30)
                ->get();

            $latest = $approvedArticles->take(3)->values();

            $byYear = $approvedArticles
                ->groupBy(fn($a) => \Carbon\Carbon::parse($a->created_at)->year)
                ->map(fn($group) => $group->take(3)->values())
                ->sortKeysDesc();

            return response()->json([
                'status' => true,
                'data'   => [
                    'latest'  => $latest,
                    'by_year' => $byYear,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch latest articles', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Failed to fetch articles.'], 500);
        }
    }
}