<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    // ─── Helper: get JWT authenticated user ────────────────────────
    private function jwtUser()
    {
        return JWTAuth::setToken(request()->cookie('jwt_token'))->authenticate();
    }

    // ─── Helper: role names a given set of role-names is NOT allowed to assign ──
private function restrictedRoleNamesFor(array $userRoleNames): array
{
    $restrictions = [
        'super-admin' => [],                        
        'admin'       => ['super-admin'],             
        'editor'      => ['admin', 'super-admin'],
        'reviewer'    => ['admin', 'super-admin'],
        'author'      => ['admin', 'super-admin'],
    ];

    if (empty($userRoleNames)) {
        return [];
    }

    $forbiddenSets = array_map(function ($roleName) use ($restrictions) {
        $roleName = strtolower($roleName);
        return $restrictions[$roleName] ?? [];
    }, $userRoleNames);

    $intersection = array_reduce($forbiddenSets, function ($carry, $set) {
        return $carry === null ? $set : array_intersect($carry, $set);
    }, null);

    return array_values($intersection ?? []);
}
    // ─── Web view only ─────────────────────────────────────────────
    public function index()
    {
        try {
            return view('admin.users');
        } catch (\Exception $e) {
            Log::error('Failed to load users view', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to load users page.');
        }
    }

    // ─── API: current logged-in user's roles ───────────────────────
    public function me()
    {
        try {
            $user = $this->jwtUser();

            return response()->json([
                'status' => true,
                'roles'  => $user->getRoleNames(), // Collection of role name strings
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch current user', ['error' => $e->getMessage()]);
            return response()->json([
                'status'  => false,
                'message' => 'Failed to fetch current user.',
            ], 500);
        }
    }

    // ─── API: List users ───────────────────────────────────────────
    public function adminIndex(Request $request)
    {
        try {
            $currentUser = $this->jwtUser();

            $query = User::with(['roles.permissions'])
                         ->where('id', '!=', $currentUser->id);

            if ($request->filled('search')) {
                $query->where(function ($q) use ($request) {
                    $q->where('name',    'like', '%' . $request->search . '%')
                      ->orWhere('email', 'like', '%' . $request->search . '%');
                });
            }

            $perPage = (int) $request->get('per_page', 10);
            $users   = $query->orderBy('id', 'desc')->paginate($perPage);

            return response()->json([
                'status' => true,
                'data'   => $users->map(fn($u) => [
                    'id'    => $u->id,
                    'name'  => $u->name,
                    'email' => $u->email,
                    'roles' => $u->roles->map(fn($r) => [
                        'id'          => $r->id,
                        'name'        => $r->name,
                        'permissions' => $r->permissions->map(fn($p) => [
                            'id'   => $p->id,
                            'name' => $p->name,
                        ]),
                    ]),
                ]),
                'meta' => [
                    'current_page' => $users->currentPage(),
                    'last_page'    => $users->lastPage(),
                    'per_page'     => $users->perPage(),
                    'total'        => $users->total(),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch users', ['error' => $e->getMessage()]);
            return response()->json([
                'status'  => false,
                'message' => 'Failed to fetch users.',
            ], 500);
        }
    }

    // ─── API: Roles list for modals (with their permissions) ───────
    public function rolesAndPermissions()
    {
        try {
            $roles = Role::with('permissions')->orderBy('name')->get()
                ->map(fn($r) => [
                    'id'          => $r->id,
                    'name'        => $r->name,
                    'permissions' => $r->permissions->map(fn($p) => [
                        'id'   => $p->id,
                        'name' => $p->name,
                    ]),
                ]);

            return response()->json([
                'status' => true,
                'roles'  => $roles,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch roles for users meta', ['error' => $e->getMessage()]);
            return response()->json([
                'status'  => false,
                'message' => 'Failed to fetch roles.',
            ], 500);
        }
    }

    // ─── API: Store new user ───────────────────────────────────────
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'                  => 'required|string|max:255',
                'email'                 => 'required|email|unique:users,email',
                'password'              => 'required|string|min:8|confirmed',
                'roles'                 => 'nullable|array',
                'roles.*'               => 'integer|exists:roles,id',
            ]);

            // ─── Enforce role-assignment restrictions ───────────────
            if (!empty($validated['roles'])) {
                $currentUser      = $this->jwtUser();
                $currentRoleNames = $currentUser->getRoleNames()->toArray();
                $forbiddenNames   = $this->restrictedRoleNamesFor($currentRoleNames);

                $requestedRoleNames = Role::whereIn('id', $validated['roles'])
                    ->pluck('name')
                    ->map(fn($n) => strtolower($n))
                    ->toArray();

                $violations = array_intersect($requestedRoleNames, $forbiddenNames);

                if (!empty($violations)) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'You are not allowed to assign one or more of the selected roles.',
                    ], 403);
                }
            }

            $user = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            if (!empty($validated['roles'])) {
                $roleNames = Role::whereIn('id', $validated['roles'])
                                 ->pluck('name')
                                 ->toArray();
                $user->syncRoles($roleNames);
            }

            // No direct permissions — inherited from roles only
            $user->syncPermissions([]);

            Log::info('User created', [
                'by'       => $this->jwtUser()->id,
                'new_user' => $user->id,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'User "' . $user->name . '" created successfully!',
                'data'    => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                ],
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('Failed to create user', ['error' => $e->getMessage()]);
            return response()->json([
                'status'  => false,
                'message' => 'Failed to create user.',
            ], 500);
        }
    }

    // ─── API: Update roles only (roles are source of truth) ────────
    public function updateRoles(Request $request, $id)
    {
        try {
            $currentUser = $this->jwtUser();
            $user        = User::findOrFail($id);

            if ($user->id === $currentUser->id) {
                return response()->json([
                    'status'  => false,
                    'message' => 'You cannot change your own roles.',
                ], 403);
            }

            $validated = $request->validate([
                'roles'   => 'nullable|array',
                'roles.*' => 'integer|exists:roles,id',
            ]);

            // ─── Enforce role-assignment restrictions ───────────────
            if (!empty($validated['roles'])) {
                $currentRoleNames = $currentUser->getRoleNames()->toArray();
                $forbiddenNames   = $this->restrictedRoleNamesFor($currentRoleNames);

                $requestedRoleNames = Role::whereIn('id', $validated['roles'])
                    ->pluck('name')
                    ->map(fn($n) => strtolower($n))
                    ->toArray();

                $violations = array_intersect($requestedRoleNames, $forbiddenNames);

                if (!empty($violations)) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'You are not allowed to assign one or more of the selected roles.',
                    ], 403);
                }
            }

            $roleNames = Role::whereIn('id', $validated['roles'] ?? [])
                             ->pluck('name')
                             ->toArray();

            $user->syncRoles($roleNames);

            // Wipe direct permissions — roles are the single source of truth
            $user->syncPermissions([]);

            Log::info('User roles updated', [
                'by'      => $currentUser->id,
                'user_id' => $user->id,
                'roles'   => $roleNames,
            ]);

            return response()->json([
                'status'  => true,
                'message' => $user->name . "'s roles updated successfully!",
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'User not found.',
            ], 404);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('Failed to update user roles', [
                'user_id' => $id,
                'error'   => $e->getMessage(),
            ]);
            return response()->json([
                'status'  => false,
                'message' => 'Failed to update roles.',
            ], 500);
        }
    }

    // ─── API: Destroy user ─────────────────────────────────────────
    public function destroy($id)
    {
        try {
            $currentUser = $this->jwtUser();
            $user        = User::findOrFail($id);

            if ($user->id === $currentUser->id) {
                return response()->json([
                    'status'  => false,
                    'message' => 'You cannot delete yourself.',
                ], 403);
            }

            $userName = $user->name;
            $user->delete();

            Log::info('User deleted', [
                'by'        => $currentUser->id,
                'user_id'   => $id,
                'user_name' => $userName,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'User "' . $userName . '" deleted successfully!',
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'User not found.',
            ], 404);

        } catch (\Exception $e) {
            Log::error('Failed to delete user', [
                'user_id' => $id,
                'error'   => $e->getMessage(),
            ]);
            return response()->json([
                'status'  => false,
                'message' => 'Failed to delete user.',
            ], 500);
        }
    }
}