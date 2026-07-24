<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Models\Setting;
use App\Models\AboutBasicContent;

class AboutComposer
{
    public function compose(View $view): void
    {
        $view->with('settings',     Setting::with('mediaSlots.media')->first());
        $view->with('aboutContent', AboutBasicContent::first());
    }
}