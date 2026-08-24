<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Page;

class PageController extends Controller
{
    public function view($slug)
    {
        return view('frontend.page', ['slug' => $slug]);
    }

    public function content($slug)
    {
        $page = Page::where('slug', $slug)
            ->where('status', 'published')
            ->first();

        if (!$page) {
            return response()->json([
                'status'  => false,
                'message' => 'Page not found.',
            ], 404);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Page fetched successfully.',
            'data'    => $page,
        ]);
    }

    public function homepage()
    {
        $page = Page::where('is_homepage', true)
            ->where('status', 'published')
            ->first();

        if (!$page) {
            return response()->json([
                'status'  => false,
                'message' => 'No homepage set.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data'   => $page,
        ]);
    }
}