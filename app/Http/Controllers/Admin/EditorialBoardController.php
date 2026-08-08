<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EditorialBoard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EditorialBoardController extends Controller
{
    // ── List all members (grouped by role) ──────────────────────────────
    public function index()
    {
        try {
            $query = EditorialBoard::where('is_active', true)
                ->with('journal')
                ->orderBy('sequence')
                ->orderBy('name');

            if (request()->filled('journal_id')) {
                $query->where('journal_id', request('journal_id'));
            }

            $members = $query->get()->groupBy('role');

            return response()->json([
                'status' => true,
                'data'   => $members,
            ]);
        } catch (\Exception $e) {
            Log::error('EditorialBoard index error', ['error' => $e->getMessage()]);
            return response()->json([
                'status'  => false,
                'message' => 'Failed to load editorial board.',
            ], 500);
        }
    }

    // ── List all members for admin (including inactive) ──────────────────
    public function adminIndex()
    {
        try {
            $query = EditorialBoard::with('journal')
                ->orderBy('role')
                ->orderBy('sequence');

            if (request()->filled('journal_id')) {
                $query->where('journal_id', request('journal_id'));
            }

            $members = $query->get();

            return response()->json([
                'status' => true,
                'data'   => $members,
            ]);
        } catch (\Exception $e) {
            Log::error('EditorialBoard adminIndex error', ['error' => $e->getMessage()]);
            return response()->json([
                'status'  => false,
                'message' => 'Failed to load editorial board.',
            ], 500);
        }
    }

    // ── Show single member ───────────────────────────────────────────────
    public function show($id)
    {
        try {
            $member = EditorialBoard::with('journal')->findOrFail($id);

            return response()->json([
                'status' => true,
                'data'   => $member,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Member not found.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('EditorialBoard show error', ['error' => $e->getMessage()]);
            return response()->json([
                'status'  => false,
                'message' => 'Failed to load member.',
            ], 500);
        }
    }

    // ── Create new member ────────────────────────────────────────────────
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'journal_id'          => 'nullable|integer|exists:journal,id',
                'role'                => 'required|string|max:100|in:Editor-in-Chief,Managing Editor,Executive Editor,Advisory Board,Editors,Associate Editors,Members',
                'name'                => 'required|string|max:255',
                'designation'         => 'nullable|string|max:255',
                'department'          => 'nullable|string|max:255',
                'institute'           => 'nullable|string|max:255',
                'university_or_org'   => 'nullable|string|max:255',
                'city'                => 'nullable|string|max:255',
                'email'               => 'nullable|email:rfc|max:255',
                'orcid_url'           => 'nullable|url|max:500',
                'scopus_url'          => 'nullable|url|max:500',
                'web_of_science_url'  => 'nullable|url|max:500',
                'profile_image'       => 'nullable|file|mimes:jpeg,jpg,png,webp|max:2048',
                'is_active'           => 'boolean',
                'sequence'            => 'integer|min:0',
            ], [
                'journal_id.exists'     => 'Selected journal does not exist.',
                'role.required'         => 'Please select a role.',
                'role.in'               => 'Invalid role selected.',
                'name.required'         => 'Member name is required.',
                'email.email'           => 'Please enter a valid email address.',
                'orcid_url.url'         => 'ORCID must be a valid URL.',
                'scopus_url.url'        => 'Scopus must be a valid URL.',
                'web_of_science_url.url'=> 'Web of Science must be a valid URL.',
                'profile_image.mimes'   => 'Profile image must be JPEG, JPG, PNG, or WEBP.',
                'profile_image.max'     => 'Profile image must not exceed 2MB.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        }

        $imagePath = null;

        try {
            if ($request->hasFile('profile_image')) {
                $imagePath = $request->file('profile_image')
                    ->store('editorial_board/images', 'public');
            }

            $member = EditorialBoard::create([
                'journal_id'         => $validated['journal_id'] ?? null,
                'role'               => $validated['role'],
                'name'               => $validated['name'],
                'designation'        => $validated['designation'] ?? null,
                'department'         => $validated['department'] ?? null,
                'institute'          => $validated['institute'] ?? null,
                'university_or_org'  => $validated['university_or_org'] ?? null,
                'city'               => $validated['city'] ?? null,
                'email'              => $validated['email'] ?? null,
                'orcid_url'          => $validated['orcid_url'] ?? null,
                'scopus_url'         => $validated['scopus_url'] ?? null,
                'web_of_science_url' => $validated['web_of_science_url'] ?? null,
                'profile_image'      => $imagePath,
                'is_active'          => $validated['is_active'] ?? true,
                'sequence'           => $validated['sequence'] ?? 0,
            ]);

            Log::info('Editorial board member created', ['id' => $member->id]);

            return response()->json([
                'status'  => true,
                'message' => 'Member added successfully.',
                'data'    => $member->load('journal'),
            ], 201);

        } catch (\Exception $e) {
            if ($imagePath) Storage::disk('public')->delete($imagePath);
            Log::error('EditorialBoard store error', ['error' => $e->getMessage()]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to add member. Please try again.',
            ], 500);
        }
    }

    // ── Update member ────────────────────────────────────────────────────
    public function update(Request $request, $id)
    {
        try {
            $member = EditorialBoard::findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Member not found.',
            ], 404);
        }

        try {
            $validated = $request->validate([
                'journal_id'         => 'nullable|integer|exists:journal,id',
                'role'               => 'sometimes|required|string|max:100|in:Editor-in-Chief,Managing Editor,Executive Editor,Editors,Associate Editors,Members',
                'name'               => 'sometimes|required|string|max:255',
                'designation'        => 'nullable|string|max:255',
                'department'         => 'nullable|string|max:255',
                'institute'          => 'nullable|string|max:255',
                'university_or_org'  => 'nullable|string|max:255',
                'city'               => 'nullable|string|max:255',
                'email'              => 'nullable|email:rfc|max:255',
                'orcid_url'          => 'nullable|url|max:500',
                'scopus_url'         => 'nullable|url|max:500',
                'web_of_science_url' => 'nullable|url|max:500',
                'profile_image'      => 'nullable|file|mimes:jpeg,jpg,png,webp|max:2048',
                'is_active'          => 'boolean',
                'sequence'           => 'integer|min:0',
            ], [
                'journal_id.exists'     => 'Selected journal does not exist.',
                'role.in'               => 'Invalid role selected.',
                'email.email'           => 'Please enter a valid email address.',
                'orcid_url.url'         => 'ORCID must be a valid URL.',
                'scopus_url.url'        => 'Scopus must be a valid URL.',
                'web_of_science_url.url'=> 'Web of Science must be a valid URL.',
                'profile_image.mimes'   => 'Profile image must be JPEG, JPG, PNG, or WEBP.',
                'profile_image.max'     => 'Profile image must not exceed 2MB.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        }

        $newImagePath = null;

        try {
            if ($request->hasFile('profile_image')) {
                if ($member->profile_image) {
                    Storage::disk('public')->delete($member->profile_image);
                }
                $newImagePath = $request->file('profile_image')
                    ->store('editorial_board/images', 'public');
                $validated['profile_image'] = $newImagePath;
            }

            $member->update($validated);

            Log::info('Editorial board member updated', ['id' => $member->id]);

            return response()->json([
                'status'  => true,
                'message' => 'Member updated successfully.',
                'data'    => $member->fresh()->load('journal'),
            ]);

        } catch (\Exception $e) {
            if ($newImagePath) Storage::disk('public')->delete($newImagePath);
            Log::error('EditorialBoard update error', ['error' => $e->getMessage()]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to update member. Please try again.',
            ], 500);
        }
    }

    // ── Delete member ────────────────────────────────────────────────────
    public function destroy($id)
    {
        try {
            $member = EditorialBoard::findOrFail($id);

            if ($member->profile_image) {
                Storage::disk('public')->delete($member->profile_image);
            }

            $member->delete();

            Log::info('Editorial board member deleted', ['id' => $id]);

            return response()->json([
                'status'  => true,
                'message' => 'Member deleted successfully.',
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Member not found.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('EditorialBoard destroy error', ['error' => $e->getMessage()]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to delete member. Please try again.',
            ], 500);
        }
    }

    // ── Toggle active status ─────────────────────────────────────────────
    public function toggleStatus($id)
    {
        try {
            $member = EditorialBoard::findOrFail($id);
            $member->update(['is_active' => !$member->is_active]);

            return response()->json([
                'status'  => true,
                'message' => 'Status updated successfully.',
                'data'    => ['is_active' => $member->is_active],
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Member not found.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('EditorialBoard toggleStatus error', ['error' => $e->getMessage()]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to update status.',
            ], 500);
        }
    }

    // ── Update sequence order ────────────────────────────────────────────

    public function updateSequence(Request $request)
    {
        try {
            $validated = $request->validate([
                'members'            => 'required|array',
                'members.*.id'       => 'required|integer|exists:editorial_board,id',
                'members.*.sequence' => 'required|integer|min:0',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        }

        try {
            foreach ($validated['members'] as $item) {
                EditorialBoard::where('id', $item['id'])
                    ->update(['sequence' => $item['sequence']]);
            }

            return response()->json([
                'status'  => true,
                'message' => 'Sequence updated successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('EditorialBoard updateSequence error', ['error' => $e->getMessage()]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to update sequence.',
            ], 500);
        }
    }
}