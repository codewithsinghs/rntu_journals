<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        try {

            $menus = Menu::with([
                'items.children.children'
            ])
            ->where('is_active', true)
            ->get();

            $page = $request->query('page');
            if ($page) {
                $menus->each(fn ($menu) => $menu->setRelation(
                    'items',
                    $this->filterItemsForPage($menu->items, $page)
                ));
            }

            return response()->json([
                'status'  => true,
                'message' => 'Menus fetched successfully.',
                'data'    => $menus
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status'  => false,
                'message' => 'Failed to fetch menus.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function byLocation(Request $request, $location)
    {
        try {

            $menus = Menu::with([
                'items.children.children'
            ])
            ->where('location', $location)
            ->where('is_active', true)
            ->get();

            $page = $request->query('page');
            if ($page) {
                $menus->each(fn ($menu) => $menu->setRelation(
                    'items',
                    $this->filterItemsForPage($menu->items, $page)
                ));
            }

            return response()->json([
                'status'  => true,
                'message' => 'Menu fetched successfully.',
                'data'    => $menus
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status'  => false,
                'message' => 'Failed to fetch menu.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    private function filterItemsForPage($items, string $page)
    {
        return $items
            ->filter(function ($item) use ($page) {
                $allowList = $item->show_on_pages ?? [];

                if (!empty($allowList)) {
                    return in_array($page, $allowList, true);
                }

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