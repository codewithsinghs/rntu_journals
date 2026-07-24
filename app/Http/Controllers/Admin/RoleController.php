<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    // ─── Web (Blade view only) ─────────────────────────────────────
    public function index()
    {
        try {
            return view('admin.roles');
        } catch (\Exception $e) {
            Log::error('Failed to load roles view', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to load roles page.');
        }
    }

    // ─── API: List ─────────────────────────────────────────────────
    public function adminIndex(Request $request)
    {
        try {
            $query = Role::with('permissions');

            if ($request->filled('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            $perPage = (int) $request->get('per_page', 10);
            $roles   = $query->orderBy('id', 'desc')->paginate($perPage);

            return response()->json([
                'status' => true,
                'data'   => $roles->map(function ($role) {
                    return [
                        'id'          => $role->id,
                        'name'        => $role->name,
                        'guard_name'  => $role->guard_name,
                        'permissions' => $role->permissions->map(fn($p) => [
                            'id'   => $p->id,
                            'name' => $p->name,
                        ]),
                    ];
                }),
                'meta' => [
                    'current_page' => $roles->currentPage(),
                    'last_page'    => $roles->lastPage(),
                    'per_page'     => $roles->perPage(),
                    'total'        => $roles->total(),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch roles', ['error' => $e->getMessage()]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to fetch roles.',
            ], 500);
        }
    }

    // ─── API: Permissions List (for modals) ────────────────────────
    public function permissions()
    {
        try {
            $permissions = Permission::orderBy('name')->get(['id', 'name']);

            return response()->json([
                'status' => true,
                'data'   => $permissions,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch permissions for roles', ['error' => $e->getMessage()]);

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
                'name'          => 'required|string|unique:roles,name|max:100',
                'permissions'   => 'nullable|array',
                'permissions.*' => 'integer|exists:permissions,id',
            ]);

            $role = Role::create([
                'name'       => $validated['name'],
                'guard_name' => 'api',
            ]);

            if (!empty($validated['permissions'])) {
                $permNames = Permission::whereIn('id', $validated['permissions'])
                                       ->pluck('name')
                                       ->toArray();
                $role->syncPermissions($permNames);
            }

            Log::info('Role created', [
                'role_id'   => $role->id,
                'role_name' => $role->name,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Role "' . $role->name . '" created successfully!',
                'data'    => [
                    'id'          => $role->id,
                    'name'        => $role->name,
                    'guard_name'  => $role->guard_name,
                    'permissions' => $role->permissions,
                ],
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('Failed to create role', ['error' => $e->getMessage()]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to create role.',
            ], 500);
        }
    }

    // ─── API: Update ───────────────────────────────────────────────
    public function update(Request $request, $id)
    {
        try {
            $role = Role::findOrFail($id);

            $validated = $request->validate([
                'name'          => 'required|string|max:100|unique:roles,name,' . $role->id,
                'permissions'   => 'nullable|array',
                'permissions.*' => 'integer|exists:permissions,id',
            ]);

            $oldName = $role->name;
            $role->update(['name' => $validated['name']]);

            $permNames = !empty($validated['permissions'])
                ? Permission::whereIn('id', $validated['permissions'])->pluck('name')->toArray()
                : [];
            $role->syncPermissions($permNames);

            Log::info('Role updated', [
                'role_id'  => $role->id,
                'old_name' => $oldName,
                'new_name' => $role->name,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Role updated successfully!',
                'data'    => $role->fresh()->load('permissions'),
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Role not found.',
            ], 404);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('Failed to update role', [
                'role_id' => $id,
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to update role.',
            ], 500);
        }
    }

    
// ─── API: Destroy ──────────────────────────────────────────────
public function destroy($id)
{
    try {
        $role = Role::findOrFail($id);

        if ($role->name === 'admin') {
            return response()->json([
                'status'  => false,
                'message' => 'Admin role cannot be deleted.',
            ], 403);
        }

        // ── Check if any users are assigned this role ──────────
        $usersCount = $role->users()->count();
        if ($usersCount > 0) {
            return response()->json([
                'status'  => false,
                'message' => 'Cannot delete "' . $role->name . '". It is currently assigned to ' 
                             . $usersCount . ' user(s).',
            ], 422);
        }

        $roleName = $role->name;
        $role->delete();

        Log::info('Role deleted', [
            'role_id'   => $id,
            'role_name' => $roleName,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Role "' . $roleName . '" deleted successfully!',
        ]);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'status'  => false,
            'message' => 'Role not found.',
        ], 404);

    } catch (\Exception $e) {
        Log::error('Failed to delete role', [
            'role_id' => $id,
            'error'   => $e->getMessage(),
        ]);

        return response()->json([
            'status'  => false,
            'message' => 'Failed to delete role.',
        ], 500);
    }
}
}