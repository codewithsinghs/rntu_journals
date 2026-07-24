<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LatestJournalComposer
{
    public function compose(View $view): void
    {
        
        $approvedArticles = DB::table('submit_articles as sa')
            ->join('article_reviews as ar', 'ar.submit_article_id', '=', 'sa.id')
            // ->where('ar.final_status', 'published')
            ->where('ar.editor_status', 'approved')
            ->orderByDesc('ar.updated_at')
            ->select('sa.id','sa.uuid', 'sa.manuscript_title', 'sa.full_name', 'sa.journal_id', 'ar.updated_at as created_at')
            ->limit(30)
            ->get();

        $latestArticles = $approvedArticles->take(3);

        $articlesByYear = $approvedArticles
            ->groupBy(fn ($a) => Carbon::parse($a->created_at)->year)
            ->map(fn ($group) => $group->take(3))
            ->sortKeysDesc();

        $view->with([
            'latestArticles' => $latestArticles,
            'articlesByYear' => $articlesByYear,
        ]);
    }
}