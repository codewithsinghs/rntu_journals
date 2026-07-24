<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use App\Models\HomeBasicContent;
use App\Models\Setting;
use Illuminate\Http\Request;

class HomeController extends Controller
{
   public function index()
    {
        $content  = HomeBasicContent::first();

        // dd($content);
        $settings = Setting::first(); // if you have a settings model

        return view('frontend.home', compact('content', 'settings'));
    }
}
