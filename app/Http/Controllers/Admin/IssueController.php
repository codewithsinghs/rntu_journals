<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Issue;
use App\Models\Volume;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class IssueController extends Controller
{
    /**
     * Validation rules shared by store() and update().
     * "year" is not accepted from the client: it is always taken from the
     * selected volume (see resolveVolumeData()).
     */
    private function rules(): array
    {
        return [
            'journal_id'     => 'required|exists:journals,id',
            'volume_id'      => 'required|exists:volumes,id',
            'issue'          => 'required|string|max:100',
            'published_date' => 'nullable|date|before_or_equal:today',
            'status'         => 'required|in:draft,published,archived',
            'is_current'     => 'nullable|boolean',
        ];
    }

    /**
     * Loads the volume and enforces:
     *  - the volume belongs to the selected journal
     *  - the volume has a year
     *  - the published date (if any) falls in the volume's year
     *
     * Returns the year to store, always copied from the volume.
     *
     * @throws ValidationException
     */
    private function resolveVolumeData(array $validated): string
    {
        $volume = Volume::findOrFail($validated['volume_id']);

        if ((int) $volume->journal_id !== (int) $validated['journal_id']) {
            throw ValidationException::withMessages([
                'volume_id' => ['The selected volume does not belong to the selected journal.'],
            ]);
        }

        if ($volume->year === null || $volume->year === '') {
            throw ValidationException::withMessages([
                'volume_id' => ['The selected volume has no year. Please set the year on the volume first.'],
            ]);
        }

        $year = (string) $volume->year;

        if (!empty($validated['published_date'])
            && substr((string) $validated['published_date'], 0, 4) !== $year) {
            throw ValidationException::withMessages([
                'published_date' => ["The published date must be in {$year}, the same year as the selected volume."],
            ]);
        }

        return $year;
    }

    /**
     * Brings every existing issue's year in line with its volume's year.
     * Runs automatically when the issues list loads, so old rows get fixed
     * without a migration. Cheap: one UPDATE that only touches mismatches.
     */
    private function syncYearsFromVolumes(): void
    {
        try {
            Issue::query()
                ->join('volumes', 'volumes.id', '=', 'issues.volume_id')
                ->whereNotNull('volumes.year')
                ->where(function ($q) {
                    $q->whereNull('issues.year')
                        ->orWhereColumn('issues.year', '!=', 'volumes.year');
                })
                ->update(['issues.year' => DB::raw('volumes.year')]);
        } catch (\Throwable $e) {
            Log::warning('Could not sync issue years from volumes', ['error' => $e->getMessage()]);
        }
    }

    // ─── List All Issues (Admin) ──────────────────────────────────
    public function adminIndex()
    {
        try {
            $this->syncYearsFromVolumes();

            $issues = Issue::with('journal:id,title', 'volume:id,volume,year')
                ->orderBy('id', 'desc')
                ->paginate(10);

            // Distinct volumes (for filter dropdowns etc.)
            $volumes = Volume::select('id', 'volume', 'year', 'journal_id')
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

            // Year always comes from the volume
            $year = $this->resolveVolumeData($validated);

            // Scoped by journal_id (not volume_id) so that "current" means
            // one current issue per journal overall — matching how
            // Journal::latestIssue() is scoped by journal_id, and matching
            // VolumeController's journal_id-scoped "current volume" behavior.
            if (!empty($validated['is_current'])) {
                Issue::where('journal_id', $validated['journal_id'])
                    ->update(['is_current' => false]);
            }

            // forceFill so the year is saved even if $fillable on the model
            // doesn't list it.
            $issue = new Issue();
            $issue->forceFill([
                'journal_id'     => $validated['journal_id'],
                'volume_id'      => $validated['volume_id'],
                'issue'          => $validated['issue'],
                'year'           => $year,
                'published_date' => $validated['published_date'] ?? null,
                'status'         => $validated['status'],
                'is_current'     => $request->boolean('is_current', false),
            ])->save();

            Log::info('Issue created successfully', [
                'issue_id'  => $issue->id,
                'volume_id' => $issue->volume_id,
                'year'      => $issue->year,
            ]);

            $issue->load('journal:id,title', 'volume:id,volume,year');

            return response()->json([
                'status'  => true,
                'message' => "Issue \"{$issue->issue}\" created successfully.",
                'data'    => $issue,
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->validator ? $e->validator->errors()->first() : 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Selected volume not found.',
            ], 404);

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
            $issue = Issue::with('journal:id,title', 'volume:id,volume,year')->findOrFail($id);

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

            // Year always comes from the volume
            $year = $this->resolveVolumeData($validated);

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
            $issue->year            = $year;
            $issue->published_date  = $validated['published_date'] ?? null;
            $issue->status          = $validated['status'];
            $issue->is_current      = $request->boolean('is_current', false);
            $issue->save();

            Log::info('Issue updated successfully', [
                'issue_id' => $id,
                'before'   => $oldData,
                'after'    => $issue->toArray(),
            ]);

            $issue->load('journal:id,title', 'volume:id,volume,year');

            return response()->json([
                'status'  => true,
                'message' => "Issue \"{$issue->issue}\" updated successfully.",
                'data'    => $issue,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Issue or volume not found for update', ['issue_id' => $id]);

            return response()->json([
                'status'  => false,
                'message' => 'Issue not found.',
            ], 404);

        } catch (ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->validator ? $e->validator->errors()->first() : 'Validation failed.',
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