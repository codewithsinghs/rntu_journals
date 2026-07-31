<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\SubmitArticle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    /**
     * Web route: renders the Blade shell page.
     * Route: GET /article/{uuid}
     */
    public function show($uuid)
    {
        // Just confirm the article exists before rendering the shell;
        // the actual data is fetched by the JS via data().
        SubmitArticle::where('uuid', $uuid)->firstOrFail();

        return view('frontend.articles', [
            'articleUuid' => $uuid,
        ]);
    }

    /**
     * API route: returns full article details as JSON.
     * Route: GET /api/public/articles/{uuid}
     */
    public function data($uuid)
    {
        try {
            $article = SubmitArticle::with(['journal:id,title', 'coAuthors', 'review'])
                ->where('uuid', $uuid)
                ->firstOrFail();

            return response()->json([
                'status' => true,
                'data' => [
                    'uuid' => $article->uuid,
                    'manuscript_title' => $article->manuscript_title,
                    'full_name' => $article->full_name,
                    'co_authors' => $article->coAuthors->map(fn ($c) => ['name' => $c->name])->values(),
                    'abstract_summary' => $article->abstract_summary,
                    'keywords' => $article->keywords ?? [],
                    'references' => $article->references,
                    'journal_title' => $article->journal?->title,
                    'published_date' => optional($article->review?->updated_at ?? $article->submission_date)->format('Y-m-d'),
                    'has_pdf' => (bool) $article->signed_manuscript_pdf,
                    'pdf_url' => $article->signed_manuscript_pdf
                        ? route('article.download-manuscript', $article->uuid)
                        : null,
                ],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['status' => false, 'message' => 'Article not found.'], 404);
        } catch (\Exception $e) {
            Log::error('Failed to fetch article (API)', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Failed to fetch article.'], 500);
        }
    }

    /**
     * Unchanged in behavior — still a real file download, not JSON.
     * Route: GET /article/{uuid}/download  (name: article.download-manuscript)
     * Now consistently keyed by uuid everywhere it's called from.
     */
    public function downloadManuscript($uuid)
    {
        $article = SubmitArticle::where('uuid', $uuid)->firstOrFail();

        if (!$article->signed_manuscript_pdf || !Storage::disk('public')->exists($article->signed_manuscript_pdf)) {
            abort(404, 'Manuscript file not found.');
        }

        // Log the download for dashboard stats
        DB::table('article_downloads')->insert([
            'submit_article_id' => $article->id,
            'ip_address'        => request()->ip(),
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        $downloadName = Str::slug($article->manuscript_title) . '.pdf';

        return Storage::disk('public')->download($article->signed_manuscript_pdf, $downloadName);
    }
}