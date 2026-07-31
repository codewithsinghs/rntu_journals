<?php
namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use App\Services\MenuService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MenuController extends Controller
{
    public function __construct(private MenuService $menuService) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $menus = $this->menuService->getAll($request->query('page'));

            return response()->json([
                'status'  => true,
                'message' => 'Menus fetched successfully.',
                'data'    => $menus,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to fetch menus.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function byLocation(Request $request, string $location): JsonResponse
    {
        try {
            $menus = $this->menuService->getByLocation($location, $request->query('page'));

            return response()->json([
                'status'  => true,
                'message' => 'Menu fetched successfully.',
                'data'    => $menus,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to fetch menu.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    
}