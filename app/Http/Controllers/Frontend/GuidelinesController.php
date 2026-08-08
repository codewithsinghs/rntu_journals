<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Guideline;
use Illuminate\Support\Facades\Log;

class GuidelinesController extends Controller
{
    
    public function index()
    {
        return view('frontend.guidelines');
    }

    
    public function content()
    {
        try {
            $content = Guideline::latest()->first();

            return response()->json(['status' => true, 'data' => $content]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch guidelines content', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Failed to fetch content.'], 500);
        }
    }
}