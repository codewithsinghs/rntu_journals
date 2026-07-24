<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Models\Setting;
use App\Models\HomeBasicContent;
use App\Models\MenuItem;

class FooterComposer
{
    public function compose(View $view): void
    {
        $view->with('content',  HomeBasicContent::first());
        $view->with('settings', Setting::with('mediaSlots.media')->first());

        $view->with('usefulLinks',
            MenuItem::whereHas('menu', fn($q) => $q->where('name', 'Useful Links'))
                     ->where('is_active', true)
                     ->orderBy('order')
                     ->get()
        );

        $view->with('journalPolicies',
            MenuItem::whereHas('menu', fn($q) => $q->where('name', 'Journal Policies'))
                     ->where('is_active', true)
                     ->orderBy('order')
                     ->get()
        );

        $view->with('bottomLinks',
            MenuItem::whereHas('menu', fn($q) => $q->where('location', 'footer-bottom'))
                     ->where('is_active', true)
                     ->orderBy('order')
                     ->get()
        );
    }
}