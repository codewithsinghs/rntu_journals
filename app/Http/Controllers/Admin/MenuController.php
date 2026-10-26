<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class MenuController extends Controller
{

    public function index()
    {
        try {

            $menus = Menu::with([
                'items.children.children'
            ])
            ->where('is_active', true)
            ->get();

            $page = request('page');
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


    public function byLocation($location)
    {
        try {

            $menus = Menu::with([
                'items.children.children'
            ])
            ->where('location', $location)
            ->where('is_active', true)
            ->get();

            $page = request('page');
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

    /**
     * Admin Menu Listing (with full nested item tree)
     * Note: intentionally NOT filtered by hide_on_pages/show_on_pages —
     * admins must see everything regardless of per-page visibility rules.
     */
    public function adminIndex()
    {
        try {

            $menus = Menu::with([
                'items.children.children.children.children'
            ])
                ->latest()
                ->get();

            return response()->json([
                'status'  => true,
                'message' => 'Menus fetched successfully.',
                'data'    => $menus
            ]);

        } catch (JWTException $e) {

            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized. Token missing or invalid.'
            ], 401);
        }
    }

    /**
     * Create Menu (+ nested items)
     */
    public function store(Request $request)
    {
        try {

            $pageKeys = array_keys(config('menu.pages', []));

            $validated = $request->validate([
                'name'                    => 'required|string|max:255',
                'location'                => 'required|in:topbar,header,footer',
                'is_active'               => 'nullable|boolean',
                'meta'                    => 'nullable|array',
                'items'                   => 'nullable|array',
                'items.*.label'           => 'required_with:items|string|max:255',
                'items.*.url'             => 'nullable|string|max:500',
                'items.*.target'          => 'nullable|in:_self,_blank',
                'items.*.order'           => 'nullable|integer',
                'items.*.is_active'       => 'nullable|boolean',
                'items.*.hide_on_pages'   => 'nullable|array',
                'items.*.hide_on_pages.*' => 'string|in:' . implode(',', $pageKeys),
                // Allowlist — when non-empty, the item is visible ONLY on
                // these pages (overrides showing everywhere by default).
                'items.*.show_on_pages'   => 'nullable|array',
                'items.*.show_on_pages.*' => 'string|in:' . implode(',', $pageKeys),
                'items.*.children'        => 'nullable|array',
            ]);

            DB::beginTransaction();

            $menu = Menu::create([
                'name'      => $validated['name'],
                'location'  => $validated['location'],
                'is_active' => $request->boolean('is_active', true),
                'meta'      => $request->input('meta'),
            ]);

            $this->saveItemsTree($menu->id, null, $request->input('items', []));

            DB::commit();

            $menu->load('items.children.children.children.children');

            return response()->json([
                'status'  => true,
                'message' => 'Menu created successfully.',
                'data'    => $menu
            ], 201);

        } catch (JWTException $e) {

            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized. Invalid token.'
            ], 401);

        } catch (\Exception $e) {

            DB::rollBack();
            Log::error('Menu create failed: ' . $e->getMessage());

            return response()->json([
                'status'  => false,
                'message' => 'Failed to create menu.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update Menu (+ replace nested items)
     */
    public function update(Request $request, $id)
    {
        try {

            $menu = Menu::findOrFail($id);

            $pageKeys = array_keys(config('menu.pages', []));

            $validated = $request->validate([
                'name'                    => 'required|string|max:255',
                'location'                => 'required|in:topbar,header,footer',
                'is_active'               => 'nullable|boolean',
                'meta'                    => 'nullable|array',
                'items'                   => 'nullable|array',
                'items.*.label'           => 'required_with:items|string|max:255',
                'items.*.url'             => 'nullable|string|max:500',
                'items.*.target'          => 'nullable|in:_self,_blank',
                'items.*.order'           => 'nullable|integer',
                'items.*.is_active'       => 'nullable|boolean',
                'items.*.hide_on_pages'   => 'nullable|array',
                'items.*.hide_on_pages.*' => 'string|in:' . implode(',', $pageKeys),
                'items.*.show_on_pages'   => 'nullable|array',
                'items.*.show_on_pages.*' => 'string|in:' . implode(',', $pageKeys),
                'items.*.children'        => 'nullable|array',
            ]);

            DB::beginTransaction();

            $menu->update([
                'name'      => $validated['name'],
                'location'  => $validated['location'],
                'is_active' => $request->boolean('is_active'),
                'meta'      => $request->input('meta'),
            ]);

            // Replace the entire item tree for this menu.
            // Delete ALL items (parents AND children) by menu_id directly —
            // do NOT rely on FK cascade behavior here, since every item row
            // (regardless of depth) already carries the correct menu_id.
            // This avoids leaving orphaned child rows behind if the
            // parent_id -> menu_items.id foreign key isn't (or stopped being)
            // configured with onDelete('cascade') at the DB level.
            MenuItem::where('menu_id', $menu->id)->delete();

            $this->saveItemsTree($menu->id, null, $request->input('items', []));

            DB::commit();

            $menu->load('items.children.children.children.children');

            return response()->json([
                'status'  => true,
                'message' => 'Menu updated successfully.',
                'data'    => $menu
            ]);

        } catch (JWTException $e) {

            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized. Invalid token.'
            ], 401);

        } catch (\Exception $e) {

            DB::rollBack();
            Log::error('Menu update failed: ' . $e->getMessage());

            return response()->json([
                'status'  => false,
                'message' => 'Failed to update menu.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete Menu (items cascade via FK)
     */
    public function destroy($id)
    {
        try {

            $menu = Menu::findOrFail($id);

            // Same reasoning as update(): don't rely on FK cascade for the
            // items — delete them explicitly by menu_id first, then the menu.
            MenuItem::where('menu_id', $menu->id)->delete();
            $menu->delete();

            return response()->json([
                'status'  => true,
                'message' => 'Menu deleted successfully.'
            ]);

        } catch (JWTException $e) {

            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized. Invalid token.'
            ], 401);

        } catch (\Exception $e) {

            return response()->json([
                'status'  => false,
                'message' => 'Failed to delete menu.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recursively insert a tree of menu items.
     *
     * NOTE: `order` is always taken from the item's position in the array
     * (`$index`), never from a client-supplied `order` value. Drag-and-drop
     * reordering on the frontend only reshuffles array position — it does
     * not (and cannot reliably) recompute each item's old `order` field.
     * Trusting a stale `order` value here silently discarded every
     * drag-and-drop reorder even though the save request "succeeded".
     */
    private function saveItemsTree(int $menuId, ?int $parentId, array $items): void
    {
        foreach ($items as $index => $item) {
            if (empty($item['label'])) {
                continue;
            }

            $MenuItems = MenuItem::create([
                'menu_id'        => $menuId,
                'parent_id'      => $parentId,
                'label'          => $item['label'],
                'url'            => $item['url'] ?? null,
                'target'         => $item['target'] ?? '_self',
                'order'          => $index,
                'is_active'      => array_key_exists('is_active', $item) ? (bool) $item['is_active'] : true,
                'hide_on_pages'  => $item['hide_on_pages'] ?? [],
                'show_on_pages'  => $item['show_on_pages'] ?? [],
                'meta'           => $item['meta'] ?? null,
            ]);

            if (!empty($item['children']) && is_array($item['children'])) {
                $this->saveItemsTree($menuId, $MenuItems->id, $item['children']);
            }
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