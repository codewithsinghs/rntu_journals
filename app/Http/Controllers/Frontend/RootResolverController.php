<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use App\Models\Page;

class RootResolverController extends Controller
{
    public function resolve($slug)
    {
        $page = Page::where('slug', $slug)
            ->where('status', 'published')
            ->first();

        if ($page) {
            return view('frontend.page', ['slug' => $slug]);
        }

        // 2. Fall back to journal lookup
        $journal = Journal::where('slug', $slug)
            ->orWhere('uuid', $slug)
            ->first();

        if ($journal) {
            return app(JournalDetailController::class)->show($journal);
        }

        abort(404);
    }
}