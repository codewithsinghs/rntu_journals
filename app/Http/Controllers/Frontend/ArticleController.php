<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\SubmitArticle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ArticleController extends Controller
{
   
    public function show($uuid)
    {
        SubmitArticle::where('uuid', $uuid)->firstOrFail();

        return view('frontend.articles', [
            'articleUuid' => $uuid,
        ]);
    }

    public function data($uuid)
    {
        try {
            $article = SubmitArticle::with(['journal:id,title', 'coAuthors', 'review', 'issue.volume'])
                ->where('uuid', $uuid)
                ->where('deleted_at', null)
                ->where('is_hidden', false)
                ->firstOrFail();

            $volume = $article->issue?->volume?->volume;
            $issue  = $article->issue?->issue;
            $year   = $article->issue?->volume?->year
                ?? optional($article->review?->updated_at ?? $article->submission_date)->year;

            // ── Downloads: total + trailing 6 months, oldest → newest ───
            $totalDownloads = DB::table('article_downloads')
                ->where('submit_article_id', $article->id)
                ->count();

            $downloadsByMonth = collect(range(5, 0))->map(function ($monthsAgo) use ($article) {
                $monthStart = Carbon::now()->subMonths($monthsAgo)->startOfMonth();
                $monthEnd = $monthStart->copy()->endOfMonth();

                $count = DB::table('article_downloads')
                    ->where('submit_article_id', $article->id)
                    ->whereBetween('created_at', [$monthStart, $monthEnd])
                    ->count();

                return [
                    'label' => $monthStart->format('M'),
                    'count' => $count,
                ];
            })->values();

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
                    'volume' => $volume,
                    'issue' => $issue,
                    'year' => $year,
                    'total_downloads' => $totalDownloads,
                    'downloads_by_month' => $downloadsByMonth,
                ],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['status' => false, 'message' => 'Article not found.'], 404);
        } catch (\Exception $e) {
            Log::error('Failed to fetch article (API)', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Failed to fetch article.'], 500);
        }
    }

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