<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Issue;
use App\Models\Journal;
use App\Models\SubmitArticle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CurrentIssuesController extends Controller
{
    /**
     * Resolve a Journal by slug or fail with 404.
     */
    private function resolveJournal(string $slug): Journal
    {
        return Journal::where('slug', $slug)->firstOrFail();
    }

    public function show(Request $request, string $journal, ?Issue $issue = null)
    {
        try {
            $journalModel = $this->resolveJournal($journal);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404);
        }

        // If an issue uuid WAS given in the URL, make sure it actually
        // belongs to this journal — otherwise someone could open
        // /anusandhan/current-issues/{uuid-of-a-different-journal}.
        if ($issue && $issue->journal_id !== $journalModel->id) {
            abort(404);
        }

        // No uuid in the URL → find this journal's latest issue and
        // redirect to its uuid URL. Nav link stays clean/uuid-less,
        // browser ends up on /{journal}/current-issues/{uuid}.
        if (!$issue) {
            $latest = Issue::where('journal_id', $journalModel->id)
                ->orderByDesc('published_date')
                ->first();

            if ($latest) {
                return redirect()->route('current-issues', [
                    'journal' => $journal,
                    'issue'   => $latest->uuid,
                ]);
            }

            // No issues exist yet for this journal — fall through and
            // render the empty-state view instead of redirecting nowhere.
        }

        return view('frontend.currentissues', [
            'journal'   => $journal,
            'issueUuid' => $issue?->uuid,
        ]);
    }

    public function articlesData(Request $request, string $journal, ?string $uuid = null)
    {
        try {
            $journalModel = $this->resolveJournal($journal);

            $issueQuery = Issue::with('volume')->where('journal_id', $journalModel->id);

            $issue = $uuid
                ? $issueQuery->where('uuid', $uuid)->firstOrFail()
                : $issueQuery->orderByDesc('published_date')->firstOrFail();

            $articles = SubmitArticle::with(['journal:id,title', 'coAuthors'])
                ->where('issue_id', $issue->id)
                ->where('journal_id', $journalModel->id)
                ->where('is_hidden', false)
                ->whereNull('deleted_at')
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