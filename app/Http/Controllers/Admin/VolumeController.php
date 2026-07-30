<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Volume;
use App\Models\MainJournal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VolumeController extends Controller
{
    /**
     * Validation rules shared by store() and update().
     */
    private function rules(): array
    {
        return [
            'journal_id'      => 'required|exists:journal,id',
            'volume'          => 'required|string|max:100',
            'year'            => 'nullable|string|max:10',
            'issues_count'    => 'nullable|integer|min:0',
            'status'          => 'required|in:draft,published,archived',
            'is_current'      => 'nullable|boolean',
        ];
    }

    // ─── List All Volumes (Admin) ─────────────────────────────────
    public function adminIndex(Request $request)
    {
        Log::info(' request'. json_encode($request->all()) );
        try {
            $volumes = Volume::with('journal:id,title')
                ->orderBy('id', 'desc')
                ->paginate(10);

            return response()->json([
                'status'  => true,
                'message' => 'Volumes fetched successfully.',
                'data'    => $volumes,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch volumes', ['error' => $e->getMessage()]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to fetch volumes.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─── Create Volume ─────────────────────────────────────────────
    public function store(Request $request)
    {
        try {
            $validated = $request->validate($this->rules());

            if (!empty($validated['is_current'])) {
                Volume::where('journal_id', $validated['journal_id'])
                    ->update(['is_current' => false]);
            }

            $volume = Volume::create([
                'journal_id'   => $validated['journal_id'],
                'volume'       => $validated['volume'],
                'year'         => $validated['year'] ?? null,
                // 'issues_count' => $validated['issues_count'] ?? 0,
                'status'       => $validated['status'],
                'is_current'   => $request->boolean('is_current', false),
            ]);

            Log::info('Volume created successfully', [
                'volume_id'  => $volume->id,
                'journal_id' => $volume->journal_id,
            ]);

            $volume->load('journal:id,title');

            return response()->json([
                'status'  => true,
                'message' => "Volume \"{$volume->volume}\" created successfully.",
                'data'    => $volume,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('Failed to create volume', [
                'input' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to create volume. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─── View Single Volume ────────────────────────────────────────
    public function show($id)
    {
        try {
            $volume = Volume::with('journal:id,title', 'issues')->findOrFail($id);

            Log::info('Volume fetched', ['volume_id' => $id]);

            return response()->json([
                'status' => true,
                'data'   => $volume,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Volume not found', ['volume_id' => $id]);

            return response()->json([
                'status'  => false,
                'message' => 'Volume not found.',
            ], 404);

        } catch (\Exception $e) {
            Log::error('Failed to fetch volume', [
                'volume_id' => $id,
                'error'     => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong.',
            ], 500);
        }
    }

    // ─── Update Volume ─────────────────────────────────────────────
    public function update(Request $request, $id)
    {
        try {
            $volume = Volume::findOrFail($id);

            $validated = $request->validate($this->rules());

            $oldData = $volume->toArray();

            if (!empty($validated['is_current'])) {
                Volume::where('journal_id', $validated['journal_id'])
                    ->where('id', '!=', $volume->id)
                    ->update(['is_current' => false]);
            }

            $volume->journal_id   = $validated['journal_id'];
            $volume->volume       = $validated['volume'];
            $volume->year         = $validated['year'] ?? null;
            // $volume->issues_count = $validated['issues_count'] ?? 0;
            $volume->status       = $validated['status'];
            $volume->is_current   = $request->boolean('is_current', false);
            $volume->save();

            Log::info('Data updated successfully', [
                'volume_id' => $id,
                'before'    => $oldData,
                'after'     => $volume->toArray(),
            ]);

            $volume->load('journal:id,title');

            return response()->json([
                'status'  => true,
                'message' => "Data updated successfully.",
                'data'    => $volume,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Volume not found for update', ['volume_id' => $id]);

            return response()->json([
                'status'  => false,
                'message' => 'Volume not found.',
            ], 404);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('Failed to update volume', [
                'volume_id' => $id,
                'input'     => $request->all(),
                'error'     => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to update volume. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─── Delete Volume ─────────────────────────────────────────────
    public function destroy($id)
    {
        try {
            $volume = Volume::findOrFail($id);
            $name   = $volume->volume;

            $volume->delete();

            Log::info('Volume deleted successfully', [
                'volume_id' => $id,
                'volume'    => $name,
            ]);

            return response()->json([
                'status'  => true,
                'message' => "Volume \"{$name}\" deleted successfully.",
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Volume not found for deletion', ['volume_id' => $id]);

            return response()->json([
                'status'  => false,
                'message' => 'Volume not found.',
            ], 404);

        } catch (\Exception $e) {
            Log::error('Failed to delete volume', [
                'volume_id' => $id,
                'error'     => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to delete volume. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─── Toggle Current Volume ─────────────────────────────────────
    public function toggleCurrent($id)
    {
        try {
            $volume = Volume::findOrFail($id);

            Volume::where('journal_id', $volume->journal_id)
                ->update(['is_current' => false]);

            $volume->is_current = true;
            $volume->save();

            Log::info('Volume marked as current', ['volume_id' => $id]);

            return response()->json([
                'status'  => true,
                'message' => 'Volume marked as current successfully.',
                'data'    => $volume,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Volume not found.',
            ], 404);

        } catch (\Exception $e) {
            Log::error('Failed to set current volume', [
                'volume_id' => $id,
                'error'     => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to update current volume.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}