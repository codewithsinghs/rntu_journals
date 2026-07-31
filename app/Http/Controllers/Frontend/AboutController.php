<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\AboutBasicContent;
use Illuminate\Support\Facades\Log;

class AboutController extends Controller
{
    /**
     * Render the About page shell.
     * Content is loaded client-side from the API.
     */
    public function index()
    {
        return view('frontend.about');
    }

    /**
     * Public JSON API — About page content.
     * GET /api/public/about-content
     */
    public function content()
    {
        try {
            $content = AboutBasicContent::latest()->first();

            if (!$content) {
                return response()->json(['status' => true, 'data' => null]);
            }

            $data = $content->toArray();

            foreach (['about_section_img1', 'about_section_img2', 'why_section_image'] as $field) {
                $data[$field . '_url'] = $content->$field
                    ? asset('storage/' . $content->$field)
                    : null;
            }

            return response()->json(['status' => true, 'data' => $data]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch about page content', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Failed to fetch content.'], 500);
        }
    }
}