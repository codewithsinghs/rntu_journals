<?php
// app/Services/CurrentPageResolver.php

namespace App\Services;

use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Http\Request as HttpRequest;

class CurrentPageResolver
{
    public function resolveFromPath(string $path): ?string
    {
        $matched = $this->matchRoute($path);
        $routeName = $matched?->getName();

        if ($routeName === 'journal-details') {
            $slugPage = $this->resolveJournalSlugPage($matched);
            if ($slugPage !== null) {
                return $slugPage;
            }
        }

        $map = config('menu.route_page_map', []);

        if ($routeName && isset($map[$routeName])) {
            return $map[$routeName];
        }

        if ($routeName) {
            foreach ($map as $pattern => $page) {
                if (str_contains($pattern, '*') && fnmatch($pattern, $routeName)) {
                    return $page;
                }
            }
        }

        $trimmed = trim($path, '/');

        // homepage special case
        if ($trimmed === '') {
            return 'home';
        }

        $firstSegment = str_replace('-', '_', explode('/', $trimmed)[0] ?? '');

        $pageKeys = array_keys(config('menu.pages', []));
        if (in_array($firstSegment, $pageKeys, true)) {
            return $firstSegment;
        }

        return null;
    }

    private function matchRoute(string $path)
    {
        try {
            return RouteFacade::getRoutes()->match(HttpRequest::create($path, 'GET'));
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function resolveJournalSlugPage($matched): ?string
    {
        if (!$matched) {
            return null;
        }

        $slug = $matched->parameter('journal');

        if ($slug === null || (!is_string($slug) && !is_numeric($slug))) {
            return null;
        }

        $slug = (string) $slug;
        $pageKeys = array_keys(config('menu.pages', []));

        return in_array($slug, $pageKeys, true) ? $slug : null;
    }
}