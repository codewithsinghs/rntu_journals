<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Issue;
use App\Models\Volume;
use App\Models\Journal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class IssueController extends Controller
{
    /**
     * Validation rules shared by store() and update().
     */
    private function rules(): array
    {
        return [
            'journal_id'     => 'required|exists:journals,id',
            'volume_id'      => 'required|exists:volumes,id',
            'issue'          => 'required|string|max:100',
            'year'           => 'nullable|string|max:10',
            'published_date' => 'nullable|date',
            'status'         => 'required|in:draft,published,archived',
            'is_current'     => 'nullable|boolean',
        ];
    }

    // ─── List All Issues (Admin) ──────────────────────────────────
    public function adminIndex()
    {
        try {
            $issues = Issue::with('journal:id,title', 'volume:id,volume')
                ->orderBy('id', 'desc')
                ->paginate(10);

            // Distinct volumes (for filter dropdowns etc.)
            $volumes = Volume::select('id', 'volume', 'journal_id')
                ->distinct()
                ->orderBy('volume')
                ->get();

            return response()->json([
                'status'  => true,
                'message' => 'Issues fetched successfully.',
                'data'    => $issues,
                'volumes' => $volumes,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch issues', ['error' => $e->getMessage()]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to fetch issues.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─── Create Issue ──────────────────────────────────────────────
    public function store(Request $request)
    {
        try {
            $validated = $request->validate($this->rules());

            // Scoped by journal_id (not volume_id) so that "current" means
            // one current issue per journal overall — matching how
            // Journal::latestIssue() is scoped by journal_id, and matching
            // VolumeController's journal_id-scoped "current volume" behavior.
            if (!empty($validated['is_current'])) {
                Issue::where('journal_id', $validated['journal_id'])
                    ->update(['is_current' => false]);
            }

            $issue = Issue::create([
                'journal_id'     => $validated['journal_id'],
                'volume_id'      => $validated['volume_id'],
                'issue'          => $validated['issue'],
                'year'           => $validated['year'] ?? null,
                'published_date' => $validated['published_date'] ?? null,
                'status'         => $validated['status'],
                'is_current'     => $request->boolean('is_current', false),
            ]);

            Log::info('Issue created successfully', [
                'issue_id'  => $issue->id,
                'volume_id' => $issue->volume_id,
            ]);

            $issue->load('journal:id,title', 'volume:id,volume');

            return response()->json([
                'status'  => true,
                'message' => "Issue \"{$issue->issue}\" created successfully.",
                'data'    => $issue,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('Failed to create issue', [
                'input' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to create issue. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─── View Single Issue ─────────────────────────────────────────
    public function show($id)
    {
        try {
            $issue = Issue::with('journal:id,title', 'volume:id,volume')->findOrFail($id);

            Log::info('Issue fetched', ['issue_id' => $id]);

            return response()->json([
                'status' => true,
                'data'   => $issue,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Issue not found', ['issue_id' => $id]);

            return response()->json([
                'status'  => false,
                'message' => 'Issue not found.',
            ], 404);

        } catch (\Exception $e) {
            Log::error('Failed to fetch issue', [
                'issue_id' => $id,
                'error'    => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong.',
            ], 500);
        }
    }

    // ─── Update Issue ──────────────────────────────────────────────
    public function update(Request $request, $id)
    {
        try {
            $issue = Issue::findOrFail($id);

            $validated = $request->validate($this->rules());

            $oldData = $issue->toArray();

            // Scoped by journal_id — see note in store().
            if (!empty($validated['is_current'])) {
                Issue::where('journal_id', $validated['journal_id'])
                    ->where('id', '!=', $issue->id)
                    ->update(['is_current' => false]);
            }

            $issue->journal_id      = $validated['journal_id'];
            $issue->volume_id       = $validated['volume_id'];
            $issue->issue           = $validated['issue'];
            $issue->year            = $validated['year'] ?? null;
            $issue->published_date  = $validated['published_date'] ?? null;
            $issue->status          = $validated['status'];
            $issue->is_current      = $request->boolean('is_current', false);
            $issue->save();

            Log::info('Issue updated successfully', [
                'issue_id' => $id,
                'before'   => $oldData,
                'after'    => $issue->toArray(),
            ]);

            $issue->load('journal:id,title', 'volume:id,volume');

            return response()->json([
                'status'  => true,
                'message' => "Issue \"{$issue->issue}\" updated successfully.",
                'data'    => $issue,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Issue not found for update', ['issue_id' => $id]);

            return response()->json([
                'status'  => false,
                'message' => 'Issue not found.',
            ], 404);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('Failed to update issue', [
                'issue_id' => $id,
                'input'    => $request->all(),
                'error'    => $e->getMessage(),
                'trace'    => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to update issue. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─── Delete Issue ──────────────────────────────────────────────
    public function destroy($id)
    {
        try {
            $issue = Issue::findOrFail($id);
            $name  = $issue->issue;

            $issue->delete();

            Log::info('Issue deleted successfully', [
                'issue_id' => $id,
                'issue'    => $name,
            ]);

            return response()->json([
                'status'  => true,
                'message' => "Issue \"{$name}\" deleted successfully.",
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Issue not found for deletion', ['issue_id' => $id]);

            return response()->json([
                'status'  => false,
                'message' => 'Issue not found.',
            ], 404);

        } catch (\Exception $e) {
            Log::error('Failed to delete issue', [
                'issue_id' => $id,
                'error'    => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to delete issue. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─── Toggle Current Issue ──────────────────────────────────────
    public function toggleCurrent($id)
    {
        try {
            $issue = Issue::findOrFail($id);

            // Scoped by journal_id — see note in store().
            Issue::where('journal_id', $issue->journal_id)
                ->update(['is_current' => false]);

            $issue->is_current = true;
            $issue->save();

            Log::info('Issue marked as current', ['issue_id' => $id]);

            return response()->json([
                'status'  => true,
                'message' => 'Issue marked as current successfully.',
                'data'    => $issue,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Issue not found.',
            ], 404);

        } catch (\Exception $e) {
            Log::error('Failed to set current issue', [
                'issue_id' => $id,
                'error'    => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to update current issue.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}