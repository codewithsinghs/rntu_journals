<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\HomeBasicContent;
use App\Models\Journal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\WebsiteVisitor;
use Illuminate\Http\JsonResponse;


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
                    // $data['cover_image_url'] = $journal->cover_image
                    //     ? Storage::url($journal->cover_image)
                    //     : null;
                    $data['cover_image_url'] = $journal->cover_image
                        ? asset('images/' . $journal->cover_image)
                        : asset('assets/home_page/hero_1.jpg');
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
            ->leftJoin('journal as j', 'j.id', '=', 'sa.journal_id')
            ->where('ar.editor_status', 'approved')
            ->where('sa.deleted_at', null)
            ->where('sa.is_hidden', false)
            ->orderByDesc('ar.updated_at')
            ->select(
                'sa.id',
                'sa.uuid',
                'sa.manuscript_title',
                'sa.full_name',
                'sa.journal_id',
                'j.slug as journal_slug',
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
    public function visitorCount(): JsonResponse
    {
        try {
            $ip = request()->ip();

            // Scope to TODAY only — otherwise once an IP is logged once,
            // it's never counted again on any future day.
            $alreadyVisitedToday = WebsiteVisitor::where('ip_address', $ip)
                ->whereDate('created_at', now()->toDateString())
                ->exists();

            if (!$alreadyVisitedToday) {
                WebsiteVisitor::create([
                    'ip_address' => $ip,
                    'user_agent' => request()->userAgent(),
                    'url'        => request()->fullUrl(),
                    'referrer'   => request()->headers->get('referer'),
                ]);
            }

            return response()->json([
                'status' => true,
                'data'   => [
                    'count' => WebsiteVisitor::count(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log visitor / fetch count', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Failed to fetch visitor count.'], 500);
        }
    }
}
