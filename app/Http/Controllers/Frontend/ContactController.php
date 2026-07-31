<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
  
    public function index()
    {
        return view('frontend.contact');
    }

    
    public function content()
    {
        try {
            $content = Contact::latest()->first();

            return response()->json(['status' => true, 'data' => $content]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch contact content', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Failed to fetch content.'], 500);
        }
    }
}