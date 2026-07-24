<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Models\Setting;
use App\Models\MenuItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class HeaderComposer
{
    public function compose(View $view): void
    {
        $settings = Setting::with('mediaSlots.media')->first();

        $allItems = MenuItem::whereHas('menu', fn($q) => $q->where('location', 'header'))
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('order')
            ->with($this->nestedChildrenWith(5)) // eager-load 5 levels deep
            ->get();

        $currentPage = $this->resolveCurrentPage();

        // Temporary debug log — remove once confirmed working.
        Log::info('HeaderComposer debug', [
            'route_name'    => request()->route()?->getName(),
            'url'           => request()->path(),
            'resolved_page' => $currentPage,
        ]);

        $view->with([
            'logoIcon'  => $settings?->logo,
            'settings'  => $settings,
            'menuItems' => $this->filterItemsForPage($allItems, $currentPage),
        ]);
    }

    /**
     * Build a nested "children.children.children..." relation string so we
     * eager-load every level instead of just one. Prevents Level 3+ items
     * from being silently dropped because they were never loaded.
     */
    private function nestedChildrenWith(int $depth): string
    {
        $path = 'children';
        for ($i = 1; $i < $depth; $i++) {
            $path .= '.children';
        }
        return $path;
    }

    /**
     * Resolve the current page key using, in order:
     *   1. Exact route name match against config('menu.route_page_map')
     *   2. Wildcard route name match (patterns containing '*')
     *   3. URL path fallback: first path segment matched against
     *      config('menu.pages') keys (dashes converted to underscores)
     */
    private function resolveCurrentPage(): ?string
    {
        $routeName = request()->route()?->getName();

        // Special case: the '/{journal}' catch-all route serves BOTH
        // Anusandhan and Shodhayatan (and possibly others) under the same
        // route name, so a static route_page_map entry can't distinguish
        // them. Instead, resolve the page key from the journal's own slug.
        if ($routeName === 'journal-details') {
            $slugPage = $this->resolveJournalSlugPage();
            if ($slugPage !== null) {
                return $slugPage;
            }
        }

        $map = config('menu.route_page_map', []);

        // 1. Exact route name match
        if ($routeName && isset($map[$routeName])) {
            return $map[$routeName];
        }

        // 2. Wildcard route name match (e.g. 'articles.*' => 'archives')
        if ($routeName) {
            foreach ($map as $pattern => $page) {
                if (str_contains($pattern, '*') && fnmatch($pattern, $routeName)) {
                    return $page;
                }
            }
        }

        // 3. Fallback: match by URL path segment against menu.pages keys.
        // e.g. /editorial-board -> 'editorial_board', /archives -> 'archives'
        // This catches pages you haven't added to route_page_map yet.
        $path = trim(request()->path(), '/');
        $firstSegment = explode('/', $path)[0] ?? '';
        $firstSegment = str_replace('-', '_', $firstSegment);

        $pageKeys = array_keys(config('menu.pages', []));
        if (in_array($firstSegment, $pageKeys, true)) {
            return $firstSegment;
        }

        return null;
    }

    /**
     * Resolve the page key for the '/{journal}' route by reading whichever
     * journal was bound to it. Handles both cases:
     *   - Controller type-hints `Journal $journal` -> Laravel already
     *     resolved it to a model instance via route-model binding.
     *   - Controller looks it up manually -> the route parameter is just
     *     the raw string from the URL (id or slug), so we look it up here.
     */
    private function resolveJournalSlugPage(): ?string
    {
        $param = request()->route('journal');

        if ($param === null) {
            return null;
        }

        // Already resolved to a model instance by route-model binding.
        if (is_object($param) && isset($param->slug)) {
            $slug = $param->slug;
        } elseif (is_string($param) || is_numeric($param)) {
            // Raw value from the URL — try matching it directly as a slug
            // first (covers slug-based binding or manual slug lookups).
            $slug = (string) $param;
        } else {
            return null;
        }

        $pageKeys = array_keys(config('menu.pages', []));

        return in_array($slug, $pageKeys, true) ? $slug : null;
    }

    /**
     * Recursively filter a tree of MenuItems for the given page key.
     *
     * - show_on_pages: allowlist. If non-empty, item is visible ONLY on
     *   pages in this list (requires a resolved $page to match against).
     * - hide_on_pages: blocklist. If show_on_pages is empty, item is hidden
     *   only if the current page is in this list.
     */
    private function filterItemsForPage(Collection $items, ?string $page): Collection
    {
        return $items
            ->filter(function ($item) use ($page) {
                $allowList = $item->show_on_pages ?? [];

                if (!empty($allowList)) {
                    return $page !== null && in_array($page, $allowList, true);
                }

                return $page === null || !in_array($page, $item->hide_on_pages ?? [], true);
            })
            ->values()
            ->each(function ($item) use ($page) {
                if ($item->relationLoaded('children')) {
                    $item->setRelation('children', $this->filterItemsForPage($item->children, $page));
                }
            });
    }
}