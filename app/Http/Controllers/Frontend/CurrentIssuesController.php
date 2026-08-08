<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Issue;
use App\Models\SubmitArticle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CurrentIssuesController extends Controller
{

    public function show(Request $request, ?Issue $issue = null)
    {
        return view('frontend.currentissues', [
            'issueUuid' => $issue?->uuid,
        ]);
    }

    public function articlesData(Request $request, ?string $uuid = null)
    {
        try {
            if ($uuid) {
                $issue = Issue::with('volume')->where('uuid', $uuid)->firstOrFail();
            } else {
                $issue = Issue::with('volume')
                    ->orderByDesc('published_date')
                    ->firstOrFail();
            }

            $articles = SubmitArticle::with(['journal:id,title', 'coAuthors'])
                ->where('issue_id', $issue->id)
                ->where('is_hidden', false)
                ->whereHas('review', function ($q) {
                    $q->where('editor_status', 'approved');
                })
                ->orderByDesc('id')
                ->paginate(8);

            $articlesData = collect($articles->items())->map(function ($article) {
                return [
                    'id' => $article->id,
                    'uuid' => $article->uuid,
                    'manuscript_title' => $article->manuscript_title,
                    'full_name' => $article->full_name,
                    'co_authors' => $article->coAuthors->map(fn($c) => ['name' => $c->name])->values(),
                    'pdf_url' => $article->signed_manuscript_pdf
                        ? route('article.download-manuscript', $article->uuid)
                        : null,
                ];
            });

            return response()->json([
                'status' => true,
                'data' => [
                    'issue' => [
                        'uuid' => $issue->uuid,
                        'issue' => $issue->issue,
                        'year' => $issue->year,
                        'published_date' => $issue->published_date,
                        'volume' => $issue->volume ? ['volume' => $issue->volume->volume] : null,
                    ],
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
            return response()->json(['status' => false, 'message' => 'Issue not found.'], 404);
        } catch (\Exception $e) {
            Log::error('Failed to fetch current issue articles (API)', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Failed to fetch articles.'], 500);
        }
    }
}
