<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use Illuminate\Support\Facades\DB;

class JournalDetailController extends Controller
{
    public function show(Journal $journal)
    {
       $articles = DB::table('submit_articles as sa')
            ->join('article_reviews as ar', 'ar.submit_article_id', '=', 'sa.id')
            ->where('sa.journal_id', $journal->id)
            ->where('ar.editor_status', 'approved')
            ->whereYear('ar.created_at', now()->year)
            ->orderByDesc('ar.created_at')
            ->select(
                'sa.id',
                'sa.uuid',
                'sa.manuscript_title',
                'sa.full_name',
                'sa.signed_manuscript_pdf',
                DB::raw("(
                    SELECT GROUP_CONCAT(aca.name ORDER BY aca.order SEPARATOR ', ')
                    FROM article_co_authors aca
                    WHERE aca.submit_article_id = sa.id
                ) as co_authors")
            )
            ->paginate(9);

        return view('frontend.journal-detail', compact('journal', 'articles'));
    }
}