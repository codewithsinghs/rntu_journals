<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class JournalDetailController extends Controller
{

    public function index()
    {
        try {
            $journals = Journal::where('is_active', 1)
                ->orderBy('sequence')
                ->get();

            return response()->json(['status' => true, 'data' => $journals]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch journals (API)', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Failed to fetch journals.'], 500);
        }
    }


    public function showData($id)
    {
        try {
            $journal = Journal::findOrFail($id);
            return response()->json(['status' => true, 'data' => $journal]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['status' => false, 'message' => 'Journal not found.'], 404);
        } catch (\Exception $e) {
            Log::error('Failed to fetch journal (API)', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Failed to fetch journal.'], 500);
        }
    }


    public function show(Journal $journal)
    {
        // dd($journal->latestVolume);

        // dd($journal);
        // dd($journal->latestVolume);
        // dd($journal->latestIssue);

        return view('frontend.journal-detail', [
            'journalId' => $journal->uuid,
        ]);
    }


    public function detail($identifier)
    {
        try {
            // Try to find by UUID first, fallback to ID
            $journal = Journal::where('uuid', $identifier)
                ->orWhere('id', $identifier)
                ->firstOrFail();

            // Overwrite with latest volume & issue
            $latestVolume = $journal->latestVolume()->first();
            $latestIssue  = $journal->latestIssue()->first();

            if ($latestVolume) {
                $journal->volume         = $latestVolume->volume;
                $journal->year           = $latestVolume->year;
                $journal->latest_volume  = $latestVolume->volume;
                $journal->published_date = $latestVolume->published_date
                    ? \Carbon\Carbon::parse($latestVolume->published_date)->format('M Y')
                    : null;
            }

            if ($latestIssue) {
                $journal->issue          = $latestIssue->issue;
                $journal->year           = $latestIssue->year;
                $journal->issue_date     = $latestIssue->published_date
                    ? \Carbon\Carbon::parse($latestIssue->published_date)->format('d M Y')
                    : null;
            }


            $articles = DB::table('submit_articles as sa')
                ->join('article_reviews as ar', 'ar.submit_article_id', '=', 'sa.id')
                ->where('sa.journal_id', $journal->id)
                ->where('sa.is_hidden', false)
                ->whereNull('sa.deleted_at')
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

            $journal->cover_image_url = $journal->cover_image
                    ? asset('images/' . $journal->cover_image)
                    : asset('assets/home_page/hero_1.jpg');

            $articlesData = collect($articles->items())->map(function ($article) {
                $article->pdf_url = $article->signed_manuscript_pdf
                    ? Storage::url($article->signed_manuscript_pdf)
                    : null;
                return $article;
            });

            return response()->json([
                'status' => true,
                'data' => [
                    'journal' => $journal,
                    'articles' => $articlesData,
                    'pagination' => [
                        'current_page' => $articles->currentPage(),
                        'last_page'    => $articles->lastPage(),
                        'per_page'     => $articles->perPage(),
                        'total'        => $articles->total(),
                        'from'         => $articles->firstItem(),
                        'to'           => $articles->lastItem(),
                    ],
                ],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['status' => false, 'message' => 'Journal not found.'], 404);
        } catch (\Exception $e) {
            Log::error('Failed to fetch journal detail (API)', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Failed to fetch journal detail.'], 500);
        }
    }

}
