<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Issue;
use App\Models\SubmitArticle;
use Illuminate\Http\Request;

class CurrentIssuesController extends Controller
{
    public function index(Request $request, ?Issue $issue = null)
    {
        
        if (!$issue) {
            $issue = Issue::with('volume')
                ->orderByDesc('published_date')
                ->firstOrFail();
        } else {
            $issue->load('volume');
        }

        $articles = SubmitArticle::with(['journal:id,title', 'coAuthors'])
            ->where('issue_id', $issue->id)
            ->whereHas('review', function ($q) {
                $q->where('editor_status', 'approved');
            })
            ->orderByDesc('id')
            ->paginate(8);

        return view('frontend.currentissues', [
            'issue'    => $issue,
            'articles' => $articles,
        ]);
    }
}