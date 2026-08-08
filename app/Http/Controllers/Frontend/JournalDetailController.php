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
    /**
     * List all active journals (JSON).
     * Route: GET /api/public/journals  (or wherever this is mounted)
     */
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

    /**
     * Return a single journal's raw data (JSON, no articles).
     */
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

    /**
     * Web route: renders the Blade shell page.
     * Route: GET /{journal}  (name: journal-details)
     * Laravel resolves {journal} via implicit route-model binding.
     */
    public function show(Journal $journal)
    {
        return view('frontend.journal-detail', [
            'journalId' => $journal->id,
        ]);
    }

    /**
     * API route: returns journal + paginated approved articles as JSON.
     * Route: GET /api/public/journals/{id}/detail
     * This is what journal-detail.js fetches.
     */
    public function detail($id)
    {
        try {
            $journal = Journal::findOrFail($id);

            $articles = DB::table('submit_articles as sa')
                ->join('article_reviews as ar', 'ar.submit_article_id', '=', 'sa.id')
                ->where('sa.journal_id', $journal->id)
                ->where('sa.is_hidden', false)
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
                ? Storage::url($journal->cover_image)
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