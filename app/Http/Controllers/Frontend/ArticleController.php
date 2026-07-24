<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\SubmitArticle;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArticleController extends Controller
{
    public function show($uuid)
{
    $article = SubmitArticle::with(['journal:id,title', 'coAuthors', 'review'])
        ->where('uuid', $uuid)
        ->firstOrFail();

    return view('frontend.articles', compact('article'));
}

    public function downloadManuscript($uuid)
    {
        $article = SubmitArticle::findOrFail($uuid);

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

        $downloadName = \Illuminate\Support\Str::slug($article->manuscript_title) . '.pdf';

        return Storage::disk('public')->download($article->signed_manuscript_pdf, $downloadName);
    }
}
