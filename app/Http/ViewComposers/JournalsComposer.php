<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Models\Journal;

class JournalsComposer
{
    public function compose(View $view): void
    {
        $view->with([
            'journals' => Journal::where('is_active', 1)
                                  ->orderBy('sequence')
                                  ->get(),
        ]);
    }
}