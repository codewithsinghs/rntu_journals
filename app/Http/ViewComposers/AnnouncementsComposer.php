<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Models\Announcement;

class AnnouncementsComposer
{
    public function compose(View $view): void
    {
        $view->with([
            'announcements' => Announcement::ordered()->get(),
        ]);
    }
}