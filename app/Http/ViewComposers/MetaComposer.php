<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;

class MetaComposer
{
    public function compose(View $view)
    {
        // Pull dynamic data if available
        $data = $view->getData();

        $article = $data['article'] ?? null;
        $issue   = $data['issue'] ?? null;
        $content = $data['content'] ?? null;
        $settings = $data['settings'] ?? null;

        // Fallback defaults
        $defaults = [
            'title'       => 'RNTU Journals',
            'description' => 'Official RNTU Journal publications',
            'keywords'    => 'Research,RNTU,Journal',
            'cover_image' => 'images/default-cover.jpg',
        ];

        // Choose best available source
        $meta = [
            'title'       => optional($article)->title
                            ?? optional($issue)->title
                            ?? optional($content)->title
                            ?? optional($settings)->site_name
                            ?? $defaults['title'],

            'description' => optional($article)->abstract
                            ?? optional($issue)->summary
                            ?? optional($content)->meta_description
                            ?? optional($settings)->default_description
                            ?? $defaults['description'],

            'keywords'    => optional($article)->keywords
                            ?? optional($issue)->keywords
                            ?? optional($content)->meta_keywords
                            ?? optional($settings)->default_keywords
                            ?? $defaults['keywords'],

            'cover_image' => optional($article)->cover_image
                            ?? optional($issue)->cover_image
                            ?? $defaults['cover_image'],
        ];

        $view->with('meta', (object) $meta);
    }
}
