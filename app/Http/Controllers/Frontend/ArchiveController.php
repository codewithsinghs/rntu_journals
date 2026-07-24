<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Journal;

class ArchiveController extends Controller
{
    public function show(Journal $journal)
    {
        $issues = $journal->issues()
            ->with('volume')
            ->orderByDesc('year')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('year')
            ->sortKeysDesc();

        return view('frontend.archives', compact('journal', 'issues'));
    }
}