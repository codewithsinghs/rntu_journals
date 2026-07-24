<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    // ─── Web (Blade view only) ─────────────────────────────────────
    public function index()
    {
        try {
            return view('admin.permissions');
        } catch (\Exception $e) {
            Log::error('Failed to load permissions view', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to load permissions page.');
        }
    }

    // ─── API: List ─────────────────────────────────────────────────
    public function adminIndex(Request $request)
    {
        try {
            $query = Permission::query();

            if ($request->filled('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            $perPage     = (int) $request->get('per_page', 10);
            $permissions = $query->orderBy('id', 'desc')->paginate($perPage);

            return response()->json([
                'status' => true,
                'data'   => $permissions->items(),
                'meta'   => [
                    'current_page' => $permissions->currentPage(),
                    'last_page'    => $permissions->lastPage(),
                    'per_page'     => $permissions->perPage(),
                    'total'        => $permissions->total(),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch permissions', ['error' => $e->getMessage()]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to fetch permissions.',
            ], 500);
        }
    }

    // ─── API: Store ────────────────────────────────────────────────
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|unique:permissions,name|max:100',
            ]);

            $permission = Permission::create([
                'name'       => $validated['name'],
                'guard_name' => 'api',
            ]);

            Log::info('Permission created', [
                'permission_id'   => $permission->id,
                'permission_name' => $permission->name,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Permission "' . $permission->name . '" created successfully!',
                'data'    => $permission,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('Failed to create permission', ['error' => $e->getMessage()]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to create permission.',
            ], 500);
        }
    }

    // ─── API: Update ───────────────────────────────────────────────
    public function update(Request $request, $id)
    {
        try {
            $permission = Permission::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:100|unique:permissions,name,' . $permission->id,
            ]);

            $oldName = $permission->name;
            $permission->update(['name' => $validated['name']]);

            Log::info('Permission updated', [
                'permission_id' => $permission->id,
                'old_name'      => $oldName,
                'new_name'      => $permission->name,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Permission updated successfully!',
                'data'    => $permission->fresh(),
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Permission not found.',
            ], 404);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('Failed to update permission', [
                'permission_id' => $id,
                'error'         => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to update permission.',
            ], 500);
        }
    }

    // ─── API: Destroy ──────────────────────────────────────────────
   // ─── API: Destroy ──────────────────────────────────────────────
public function destroy($id)
{
    try {
        $permission     = Permission::findOrFail($id);
        $permissionName = $permission->name;

        // ── Check if permission is assigned to any roles ──────────
        $rolesCount = $permission->roles()->count();

        if ($rolesCount > 0) {
            $roleNames = $permission->roles()->pluck('name')->join(', ');

            return response()->json([
                'status'  => false,
                'message' => 'Cannot delete "' . $permissionName . '". It is currently assigned to ' 
                             . $rolesCount . ' role(s): ' . $roleNames . '.',
            ], 422);
        }

        $permission->delete();

        Log::info('Permission deleted', [
            'permission_id'   => $id,
            'permission_name' => $permissionName,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Permission "' . $permissionName . '" deleted successfully!',
        ]);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'status'  => false,
            'message' => 'Permission not found.',
        ], 404);

    } catch (\Exception $e) {
        Log::error('Failed to delete permission', [
            'permission_id' => $id,
            'error'         => $e->getMessage(),
        ]);

        return response()->json([
            'status'  => false,
            'message' => 'Failed to delete permission.',
        ], 500);
    }
}
}