<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EditorialBoardRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class EditorialBoardRoleController extends Controller
{
    // ── List active roles (public/front-end use) ─────────────────────────
    public function index()
    {
        try {
            $query = EditorialBoardRole::where('status', true)
                ->with('journal')
                ->orderBy('role');

            if (request()->filled('journal_id')) {
                $query->where('journal_id', request('journal_id'));
            }

            $roles = $query->get();

            return response()->json([
                'status' => true,
                'data'   => $roles,
            ]);
        } catch (\Exception $e) {
            Log::error('EditorialBoardRole index error', ['error' => $e->getMessage()]);
            return response()->json([
                'status'  => false,
                'message' => 'Failed to load roles.',
            ], 500);
        }
    }

    // ── List all roles for admin (including inactive) ────────────────────
    public function adminIndex()
    {
        try {
            $query = EditorialBoardRole::with('journal')
                ->orderBy('journal_id')
                ->orderBy('role');

            if (request()->filled('journal_id')) {
                $query->where('journal_id', request('journal_id'));
            }

            $roles = $query->get();

            return response()->json([
                'status' => true,
                'data'   => $roles,
            ]);
        } catch (\Exception $e) {
            Log::error('EditorialBoardRole adminIndex error', ['error' => $e->getMessage()]);
            return response()->json([
                'status'  => false,
                'message' => 'Failed to load roles.',
            ], 500);
        }
    }

    // ── Show single role ──────────────────────────────────────────────────
    public function show($id)
    {
        try {
            $role = EditorialBoardRole::with('journal')->findOrFail($id);

            return response()->json([
                'status' => true,
                'data'   => $role,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Role not found.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('EditorialBoardRole show error', ['error' => $e->getMessage()]);
            return response()->json([
                'status'  => false,
                'message' => 'Failed to load role.',
            ], 500);
        }
    }

    // ── Create new role ───────────────────────────────────────────────────
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'journal_id' => 'nullable|integer|exists:journal,id',
                'role'       => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('editorial_board_roles', 'role')
                        ->where(fn ($q) => $q->where('journal_id', $request->input('journal_id'))),
                ],
                'status' => 'boolean',
            ], [
                'journal_id.exists' => 'Selected journal does not exist.',
                'role.required'     => 'Role name is required.',
                'role.unique'       => 'This role already exists for the selected journal.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        }

        try {
            $role = EditorialBoardRole::create([
                'journal_id' => $validated['journal_id'] ?? null,
                'role'       => $validated['role'],
                'status'     => $validated['status'] ?? true,
            ]);

            Log::info('Editorial board role created', ['id' => $role->id]);

            return response()->json([
                'status'  => true,
                'message' => 'Role added successfully.',
                'data'    => $role->load('journal'),
            ], 201);

        } catch (\Exception $e) {
            Log::error('EditorialBoardRole store error', ['error' => $e->getMessage()]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to add role. Please try again.',
            ], 500);
        }
    }

    // ── Update role ────────────────────────────────────────────────────────
    public function update(Request $request, $id)
    {
        try {
            $role = EditorialBoardRole::findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Role not found.',
            ], 404);
        }

        try {
            $journalId = $request->input('journal_id', $role->journal_id);

            $validated = $request->validate([
                'journal_id' => 'nullable|integer|exists:journal,id',
                'role'       => [
                    'sometimes',
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('editorial_board_roles', 'role')
                        ->where(fn ($q) => $q->where('journal_id', $journalId))
                        ->ignore($role->id),
                ],
                'status' => 'boolean',
            ], [
                'journal_id.exists' => 'Selected journal does not exist.',
                'role.unique'       => 'This role already exists for the selected journal.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        }

        try {
            $role->update($validated);

            Log::info('Editorial board role updated', ['id' => $role->id]);

            return response()->json([
                'status'  => true,
                'message' => 'Role updated successfully.',
                'data'    => $role->fresh()->load('journal'),
            ]);

        } catch (\Exception $e) {
            Log::error('EditorialBoardRole update error', ['error' => $e->getMessage()]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to update role. Please try again.',
            ], 500);
        }
    }

    // ── Delete role ───────────────────────────────────────────────────────
    public function destroy($id)
    {
        try {
            $role = EditorialBoardRole::findOrFail($id);
            $role->delete();

            Log::info('Editorial board role deleted', ['id' => $id]);

            return response()->json([
                'status'  => true,
                'message' => 'Role deleted successfully.',
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Role not found.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('EditorialBoardRole destroy error', ['error' => $e->getMessage()]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to delete role. Please try again.',
            ], 500);
        }
    }

    // ── Toggle status ─────────────────────────────────────────────────────
    public function toggleStatus($id)
    {
        try {
            $role = EditorialBoardRole::findOrFail($id);
            $role->update(['status' => !$role->status]);

            return response()->json([
                'status'  => true,
                'message' => 'Status updated successfully.',
                'data'    => ['status' => $role->status],
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Role not found.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('EditorialBoardRole toggleStatus error', ['error' => $e->getMessage()]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to update status.',
            ], 500);
        }
    }
}