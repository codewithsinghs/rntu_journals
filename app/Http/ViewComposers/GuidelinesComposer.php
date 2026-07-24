<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Models\Guideline;

class GuidelinesComposer
{
    public function compose(View $view): void
    {
        $view->with('guidelines', Guideline::first());
    }
}