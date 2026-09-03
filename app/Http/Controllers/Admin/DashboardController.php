<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ArrayExport;
use App\Http\Controllers\Controller;
use App\Models\Journal;
use App\Models\SubmitArticle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class DashboardController extends Controller
{
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

    /* ---------- shared data builders ---------- */

    private function monthlySubmissionsData($user)
    {
        return $this->scopeToUser(SubmitArticle::query(), $user)
            ->selectRaw("DATE_FORMAT(created_at, '%b') as month, COUNT(*) as total")
            ->whereYear('created_at', now()->year)
            ->groupBy(DB::raw("MONTH(created_at)"), 'month')
            ->orderBy(DB::raw("MONTH(created_at)"))
            ->get();
    }

    private function monthlyPublishedData($user)
    {
        $query = SubmitArticle::join('article_reviews', 'article_reviews.submit_article_id', '=', 'submit_articles.id')
            ->where('article_reviews.current_stage', 'approved')
            ->whereYear('article_reviews.updated_at', now()->year);

        $query = $this->scopeJoinedToUser(
            $query,
            $user,
            'submit_articles.user_id',
            'article_reviews.reviewer_id'
        );

        return $query
            ->selectRaw("DATE_FORMAT(article_reviews.updated_at, '%b') as month, COUNT(*) as total")
            ->groupBy(DB::raw("MONTH(article_reviews.updated_at)"), 'month')
            ->orderBy(DB::raw("MONTH(article_reviews.updated_at)"))
            ->get();
    }

    private function articleDownloadsData($user)
    {
        $query = DB::table('article_downloads')
            ->join('submit_articles', 'submit_articles.id', '=', 'article_downloads.submit_article_id')
            ->leftJoin('article_reviews', 'article_reviews.submit_article_id', '=', 'submit_articles.id')
            ->whereYear('article_downloads.created_at', now()->year);

        return $this->scopeJoinedToUser($query, $user, 'submit_articles.user_id', 'article_reviews.reviewer_id')
            ->selectRaw("DATE_FORMAT(article_downloads.created_at, '%b') as month, COUNT(*) as total")
            ->groupBy(DB::raw("MONTH(article_downloads.created_at)"), 'month')
            ->orderBy(DB::raw("MONTH(article_downloads.created_at)"))
            ->get();
    }

    private function recentSubmissionsQuery($user)
    {
        return $this->scopeToUser(SubmitArticle::query(), $user)
            ->with(['journal:id,title'])
            ->select('id', 'uuid', 'journal_id', 'manuscript_title', 'submission_date', 'created_at', 'user_id');
    }

    private function latestPublicationsQuery($user)
    {
        return $this->scopeToUser(
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
            }], 'approval_date');
    }

    /* ---------- JSON endpoints (unchanged behaviour) ---------- */

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
                'total_journals'     => Journal::count(),
                'total_articles'     => (clone $base())->count(),
                'submitted_articles' => (clone $base())->count(),
                'under_review'       => $countByStage('with_reviewer'),
                'pending_submission' => $countByStage('with_author'),
                'revision_requested' => $countByStage('reviewer_correction'),
                'accepted_articles'  => $countByStage('approved'),
                'rejected_articles'  => $countByStage('rejected') + $countByStage('reviewer_rejected'),
                'published_articles' => $countByStage('approved'),
            ],
        ]);
    }

    public function monthlySubmissions(Request $request)
    {
        return response()->json(['data' => $this->monthlySubmissionsData($request->user('api'))]);
    }

    public function monthlyPublished(Request $request)
    {
        return response()->json(['data' => $this->monthlyPublishedData($request->user('api'))]);
    }

    public function articleDownloads(Request $request)
    {
        return response()->json(['data' => $this->articleDownloadsData($request->user('api'))]);
    }

    public function recentSubmissions(Request $request)
    {
        $data = $this->recentSubmissionsQuery($request->user('api'))
            ->latest()
            ->take(5)
            ->get();

        return response()->json(['data' => $data]);
    }

    public function latestPublications(Request $request)
    {
        $data = $this->latestPublicationsQuery($request->user('api'))
            ->orderByDesc('approval_date')
            ->take(10)
            ->get();

        return response()->json(['data' => $data]);
    }

    /* ---------- Excel export endpoints (web-session auth) ---------- */

    public function exportMonthlySubmissions(Request $request)
    {
        $rows = $this->monthlySubmissionsData($request->user())
            ->map(fn($r) => [$r->month, $r->total])
            ->toArray();

        return Excel::download(
            new ArrayExport($rows, ['Month', 'Submissions'], 'Monthly Submissions'),
            'monthly-submissions-' . now()->year . '.xlsx'
        );
    }

    public function exportMonthlyPublished(Request $request)
    {
        $rows = $this->monthlyPublishedData($request->user())
            ->map(fn($r) => [$r->month, $r->total])
            ->toArray();

        return Excel::download(
            new ArrayExport($rows, ['Month', 'Published'], 'Monthly Published'),
            'monthly-published-' . now()->year . '.xlsx'
        );
    }

    public function exportArticleDownloads(Request $request)
    {
        $rows = $this->articleDownloadsData($request->user())
            ->map(fn($r) => [$r->month, $r->total])
            ->toArray();

        return Excel::download(
            new ArrayExport($rows, ['Month', 'Downloads'], 'Article Downloads'),
            'article-downloads-' . now()->year . '.xlsx'
        );
    }

    public function exportRecentSubmissions(Request $request)
    {
        $rows = $this->recentSubmissionsQuery($request->user())
            ->latest()
            ->get()
            ->map(fn($r) => [
                $r->id,
                $r->journal->title ?? '-',
                $r->manuscript_title,
                optional($r->submission_date ?? $r->created_at)->format('d M Y'),
            ])
            ->toArray();

        return Excel::download(
            new ArrayExport($rows, ['ID', 'Journal', 'Manuscript Title', 'Submitted'], 'Recent Submissions'),
            'recent-submissions-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function exportLatestPublications(Request $request)
    {
        $rows = $this->latestPublicationsQuery($request->user())
            ->orderByDesc('approval_date')
            ->get()
            ->map(fn($r) => [
                $r->manuscript_title,
                $r->journal->title ?? '-',
                $r->issue->volume->volume ?? '-',
                $r->issue->issue ?? '-',
                optional($r->approval_date)->format('d M Y'),
            ])
            ->toArray();

        return Excel::download(
            new ArrayExport($rows, ['Article', 'Journal', 'Volume', 'Issue', 'Published Date'], 'Latest Publications'),
            'latest-publications-' . now()->format('Y-m-d') . '.xlsx'
        );
    }
}
