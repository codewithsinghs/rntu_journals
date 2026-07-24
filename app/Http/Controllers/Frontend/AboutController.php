<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use App\Models\AboutBasicContent;
use Illuminate\Http\Request;

class AboutController extends Controller
{
    public function index()
    {
        $content = AboutBasicContent::first();

        return view('frontend.about', compact('content'));
    }

}
