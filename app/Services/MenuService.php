<?php
// app/Services/MenuService.php

namespace App\Services;

use App\Models\Menu;
use Illuminate\Support\Collection;

class MenuService
{
    public function getByLocation(string $location, ?string $page = null): Collection
    {
        $menus = Menu::with(['items.children.children'])
            ->where('location', $location)
            ->where('is_active', true)
            ->get();

        $menus->each(fn ($menu) => $menu->setRelation(
            'items',
            $this->filterItemsForPage($menu->items, $page)
        ));

        return $menus;
    }

    public function getAll(?string $page = null): Collection
    {
        $menus = Menu::with(['items.children.children'])
            ->where('is_active', true)
            ->get();

        $menus->each(fn ($menu) => $menu->setRelation(
            'items',
            $this->filterItemsForPage($menu->items, $page)
        ));

        return $menus;
    }

    private function filterItemsForPage(Collection $items, ?string $page): Collection
    {
        return $items
            ->filter(function ($item) use ($page) {
                $allowList = $item->show_on_pages ?? [];

                if (!empty($allowList)) {
                    // show_on_pages set hai -> sirf usi list wale page(s) par dikhega,
                    // page null ho ya list me na ho to hide.
                    return $page !== null && in_array($page, $allowList, true);
                }

                // show_on_pages set nahi hai -> normal hide_on_pages logic
                return !in_array($page, $item->hide_on_pages ?? [], true);
            })
            ->values()
            ->each(function ($item) use ($page) {
                if ($item->relationLoaded('children')) {
                    $item->setRelation('children', $this->filterItemsForPage($item->children, $page));
                }
            });
    }
}