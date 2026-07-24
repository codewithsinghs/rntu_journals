<?php

namespace App\Http\ViewComposers;

use App\Models\Contact;
use Illuminate\View\View;

class ContactComposer
{
    public function compose(View $view): void
    {
        $view->with('contacts', Contact::first());
    }
}