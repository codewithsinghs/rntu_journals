<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use App\Models\SubmitArticle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Scope a SubmitArticle Eloquent query to what the logged-in user
     * is allowed to see — mirrors SubmitArticleController::adminIndex():
     *
     * - "view all articles" permission: no restriction.
     * - "review article" permission (and no "view all"): only articles
     *   where they're the assigned reviewer (review.reviewer_id).
     * - everyone else: only their own submissions (user_id).
     */
    private function scopeToUser($query, $user)
    {
        $canViewAll = $user && $user->can('view all articles');

        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        if (!$canViewAll) {
            if ($user->can('review article')) {
                $query->whereHas('review', function ($q) use ($user) {
                    $q->where('reviewer_id', $user->id);
                });
            } else {
                $query->where('user_id', $user->id);
            }
        }

        return $query;
    }

   
    private function scopeJoinedToUser($query, $user, string $userCol, string $reviewerCol)
    {
        $canViewAll = $user && $user->can('view all articles');

        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        if (!$canViewAll) {
            if ($user->can('review article')) {
                $query->where($reviewerCol, $user->id);
            } else {
                $query->where($userCol, $user->id);
            }
        }

        return $query;
    }

    public function overview(Request $request)
    {
        $user = $request->user('api');

        $base = fn() => $this->scopeToUser(SubmitArticle::query(), $user);

        $countByStage = fn($stage) => (clone $base())
            ->whereHas('review', function ($q) use ($stage) {
                $q->where('current_stage', $stage);
            })->count();

        return response()->json([
            'data' => [
                // Left unrestricted for now — let me know if authors/reviewers
                // should see a journal count scoped to journals they've
                // submitted to / reviewed for, rather than the global total.
                'total_journals'     => Journal::count(),
                'total_articles'     => (clone $base())->count(),

                'submitted_articles' => (clone $base())->count(),
                'under_review'       => $countByStage('with_reviewer'),
                'pending_submission' => $countByStage('with_author'),
                'revision_requested' => $countByStage('reviewer_correction'),

                'accepted_articles'  => $countByStage('approved'),

                'rejected_articles'  => $countByStage('rejected')
                    + $countByStage('reviewer_rejected'),

                'published_articles' => $countByStage('approved'),
            ],
        ]);
    }

    public function monthlySubmissions(Request $request)
    {
        $user = $request->user('api');

        $data = $this->scopeToUser(SubmitArticle::query(), $user)
            ->selectRaw("DATE_FORMAT(created_at, '%b') as month, COUNT(*) as total")
            ->whereYear('created_at', now()->year)
            ->groupBy(DB::raw("MONTH(created_at)"), 'month')
            ->orderBy(DB::raw("MONTH(created_at)"))
            ->get();

        return response()->json(['data' => $data]);
    }

    public function monthlyPublished(Request $request)
    {
        $user = $request->user('api');

        $query = SubmitArticle::join('article_reviews', 'article_reviews.submit_article_id', '=', 'submit_articles.id')
            ->where('article_reviews.current_stage', 'approved')
            ->whereYear('article_reviews.updated_at', now()->year);

        $query = $this->scopeJoinedToUser(
            $query,
            $user,
            'submit_articles.user_id',
            'article_reviews.reviewer_id'
        );

        $data = $query
            ->selectRaw("DATE_FORMAT(article_reviews.updated_at, '%b') as month, COUNT(*) as total")
            ->groupBy(DB::raw("MONTH(article_reviews.updated_at)"), 'month')
            ->orderBy(DB::raw("MONTH(article_reviews.updated_at)"))
            ->get();

        return response()->json(['data' => $data]);
    }

public function articleDownloads(Request $request)
{
    $user = $request->user('api');

    $query = DB::table('article_downloads')
        ->join('submit_articles', 'submit_articles.id', '=', 'article_downloads.submit_article_id')
        ->leftJoin('article_reviews', 'article_reviews.submit_article_id', '=', 'submit_articles.id')
        ->whereYear('article_downloads.created_at', now()->year);

    $data = $this->scopeJoinedToUser($query, $user, 'submit_articles.user_id', 'article_reviews.reviewer_id')
        ->selectRaw("DATE_FORMAT(article_downloads.created_at, '%b') as month, COUNT(*) as total")
        ->groupBy(DB::raw("MONTH(article_downloads.created_at)"), 'month')
        ->orderBy(DB::raw("MONTH(article_downloads.created_at)"))
        ->get();

    return response()->json(['data' => $data]);
}
    public function recentSubmissions(Request $request)
    {
        $user = $request->user('api');

        $data = $this->scopeToUser(SubmitArticle::query(), $user)
            ->with(['journal:id,title'])
            ->select('id', 'journal_id', 'manuscript_title', 'submission_date', 'created_at', 'user_id')
            ->latest()
            ->take(5)
            ->get();

        return response()->json(['data' => $data]);
    }

    public function latestPublications(Request $request)
    {
        $user = $request->user('api');

        $data = $this->scopeToUser(
            SubmitArticle::whereHas('review', function ($q) {
                $q->where('editor_status', 'approved');
            }),
            $user
        )
            ->with([
                'journal:id,title',
                'issue:id,issue,volume_id',
                'issue.volume:id,volume',
            ])
            ->withMax(['review as approval_date' => function ($q) {
                $q->where('editor_status', 'approved');
            }], 'approval_date')
            ->orderByDesc('approval_date')
            ->take(10)
            ->get();

        return response()->json(['data' => $data]);
    }
}