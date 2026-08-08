<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\PRP;
use Illuminate\Support\Facades\Log;

class PrpController extends Controller
{
    
    public function index()
    {
        return view('frontend.prp');
    }

    
    public function content()
    {
        try {
            $content = PRP::latest()->first();

            return response()->json(['status' => true, 'data' => $content]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch PRP content', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Failed to fetch content.'], 500);
        }
    }
}
