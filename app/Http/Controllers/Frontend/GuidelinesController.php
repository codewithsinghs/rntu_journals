<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Guideline;
use Illuminate\Http\Request;

class GuidelinesController extends Controller
{
      public function index()
    {
        $content = Guideline::first();

        return view('frontend.guidelines', compact('content'));
    }
}
