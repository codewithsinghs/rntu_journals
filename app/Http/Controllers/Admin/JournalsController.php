<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class JournalsController extends Controller
{
    /**
     * Validation rules shared by store() and update().
     */
    private function rules(): array
    {
        return [
            // ===== Common =====
            'title'                        => 'required|string|max:255',
            'description'                  => 'nullable|string',
            'cover_image'                  => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_active'                    => 'nullable|boolean',

            // ===== From old journals table =====
            'heading_1'                    => 'nullable|string|max:255', // frontend field name -> title_2
            'view_all_issues_label'        => 'nullable|string|max:255',
            'view_all_issues_link'         => 'nullable|string|max:255',
            'explore_journals_label'       => 'nullable|string|max:255',
            'explore_journals_link'        => 'nullable|string|max:255',
            'fields_covered'               => 'nullable|array',
            'fields_covered.*'             => 'string|max:255',
            'sequence'                     => 'nullable|integer',

            // ===== From old journal table =====
            'abbreviation'                 => 'nullable|string|max:255',
            'e_issn'                       => 'nullable|string|max:50',
            'p_issn'                       => 'nullable|string|max:50',
            'issn_online'                  => 'nullable|string|max:50',
            'volume'                       => 'nullable|string|max:100',
            'issue'                        => 'nullable|string|max:100',
            'latest_volume'                => 'nullable|string|max:100',
            'publication_language'         => 'nullable|string|max:100',
            'publishing_frequency'         => 'nullable|string|max:100',
            'publishing_months'            => 'nullable|string|max:255',
            'indexing_impact_factor'       => 'nullable|string|max:255',
            'time_to_first_decision'       => 'nullable|string|max:100',
            'time_to_review'               => 'nullable|string|max:100',
            'acceptance_to_publication'    => 'nullable|string|max:100',
            'aim_and_scope_title'          => 'nullable|string',
            'aim_and_scope'                => 'nullable|string',
            'badge'                        => 'nullable|string|max:255',
            'article_template_url'         => 'nullable|string|max:255',
        ];
    }

    /**
     * Generate a unique slug from the explore_journals_link URL.
     * Falls back to the title if the link is empty or has no usable segment.
     */
    private function generateUniqueSlug(?string $exploreLink, string $title, ?int $ignoreId = null): string
    {
        $baseSlug = $this->extractSlugSource($exploreLink, $title);
        $slug = $baseSlug;
        $counter = 2;

        while (
            Journal::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    /**
     * Pulls a usable slug string out of the explore_journals_link URL.
     * Falls back to the title if the link is missing or has no meaningful path.
     */
    private function extractSlugSource(?string $exploreLink, string $title): string
    {
        if ($exploreLink) {
            $path = parse_url(trim($exploreLink), PHP_URL_PATH);

            if ($path) {
                $segments = array_values(array_filter(explode('/', trim($path, '/'))));
                $lastSegment = end($segments);

                if ($lastSegment) {
                    return Str::slug($lastSegment);
                }
            }
        }

        // Fallback: no link, no path, or path had no segments
        return Str::slug($title);
    }

    // ─── List All Journals (Admin) ────────────────────────────────
    public function adminIndex()
    {
        try {
            $journals = Journal::orderBy('sequence')->paginate(10);

            // Map title_2 → heading_1 for frontend
            $journals->getCollection()->transform(function ($journal) {
                $journal->heading_1 = $journal->title_2;
                return $journal;
            });

            return response()->json([
                'status'  => true,
                'message' => 'Journals fetched successfully.',
                'data'    => $journals,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch journals', ['error' => $e->getMessage()]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to fetch journals.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─── Create Journal ───────────────────────────────────────────
    public function store(Request $request)
    {
        $coverImagePath = null;

        try {
            $validated = $request->validate($this->rules());

            if ($request->hasFile('cover_image')) {
                $coverImagePath = $request->file('cover_image')
                    ->store('journals/covers', 'public');

                Log::info('Journal cover image uploaded', [
                    'path'          => $coverImagePath,
                    'original_name' => $request->file('cover_image')->getClientOriginalName(),
                    'size'          => $request->file('cover_image')->getSize(),
                ]);
            }

            $journal = Journal::create([
                // Common
                'title'                     => $validated['title'],
                'slug'                      => $this->generateUniqueSlug(
                    $validated['explore_journals_link'] ?? null,
                    $validated['title']
                ),
                'description'               => $validated['description'] ?? null,
                'cover_image'               => $coverImagePath,
                'is_active'                 => $request->boolean('is_active', true),

                // Old journals table fields
                'title_2'                   => $validated['heading_1'] ?? null, // map heading_1 → title_2
                'view_all_issues_label'     => $validated['view_all_issues_label'] ?? null,
                'view_all_issues_link'      => $validated['view_all_issues_link'] ?? null,
                'explore_journals_label'    => $validated['explore_journals_label'] ?? null,
                'explore_journals_link'     => $validated['explore_journals_link'] ?? null,
                'fields_covered'            => $validated['fields_covered'] ?? [],
                'sequence'                  => $validated['sequence'] ?? 0,

                // Old journal table fields
                'abbreviation'              => $validated['abbreviation'] ?? null,
                'e_issn'                    => $validated['e_issn'] ?? null,
                'p_issn'                    => $validated['p_issn'] ?? null,
                'issn_online'               => $validated['issn_online'] ?? null,
                'volume'                    => $validated['volume'] ?? null,
                'issue'                     => $validated['issue'] ?? null,
                'latest_volume'             => $validated['latest_volume'] ?? null,
                'publication_language'      => $validated['publication_language'] ?? null,
                'publishing_frequency'      => $validated['publishing_frequency'] ?? null,
                'publishing_months'         => $validated['publishing_months'] ?? null,
                'indexing_impact_factor'    => $validated['indexing_impact_factor'] ?? null,
                'time_to_first_decision'    => $validated['time_to_first_decision'] ?? null,
                'time_to_review'            => $validated['time_to_review'] ?? null,
                'acceptance_to_publication' => $validated['acceptance_to_publication'] ?? null,
                'aim_and_scope_title'       => $validated['aim_and_scope_title'] ?? null,
                'aim_and_scope'             => $validated['aim_and_scope'] ?? null,
                'badge'                     => $validated['badge'] ?? null,
                'article_template_url'      => $validated['article_template_url'] ?? null,
            ]);

            Log::info('Journal created successfully', [
                'journal_id'      => $journal->id,
                'title'           => $journal->title,
                'slug'            => $journal->slug,
                'has_cover_image' => !is_null($coverImagePath),
            ]);

            $journal->heading_1 = $journal->title_2;

            return response()->json([
                'status'  => true,
                'message' => "Journal \"{$journal->title}\" created successfully.",
                'data'    => $journal,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('Failed to create journal', [
                'input' => $request->except('cover_image'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if (!empty($coverImagePath)) {
                Storage::disk('public')->delete($coverImagePath);
                Log::warning('Rolled back cover image upload after failed insert', [
                    'path' => $coverImagePath,
                ]);
            }

            return response()->json([
                'status'  => false,
                'message' => 'Failed to create journal. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─── View Single Journal ──────────────────────────────────────
    public function show($id)
    {
        try {
            $journal = Journal::with('volumes', 'issues')->findOrFail($id);

            // Map title_2 → heading_1 for frontend
            $journal->heading_1 = $journal->title_2;

            Log::info('Journal fetched', ['journal_id' => $id]);

            return response()->json([
                'status' => true,
                'data'   => $journal,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Journal not found', ['journal_id' => $id]);

            return response()->json([
                'status'  => false,
                'message' => 'Journal not found.',
            ], 404);

        } catch (\Exception $e) {
            Log::error('Failed to fetch journal', [
                'journal_id' => $id,
                'error'      => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong.',
            ], 500);
        }
    }

    // ─── Update Journal ───────────────────────────────────────────
    public function update(Request $request, $id)
    {
        try {
            $journal = Journal::findOrFail($id);

            $validated = $request->validate($this->rules());

            $oldData = $journal->toArray();

            if ($request->hasFile('cover_image')) {
                if ($journal->cover_image) {
                    Storage::disk('public')->delete($journal->cover_image);

                    Log::info('Old journal cover image deleted', [
                        'journal_id' => $id,
                        'old_path'   => $journal->cover_image,
                    ]);
                }

                $journal->cover_image = $request->file('cover_image')
                    ->store('journals/covers', 'public');

                Log::info('New journal cover image uploaded', [
                    'journal_id'    => $id,
                    'new_path'      => $journal->cover_image,
                    'original_name' => $request->file('cover_image')->getClientOriginalName(),
                ]);
            }

            // Common
            $journal->title = $validated['title'];

            // Always regenerate slug from the current explore_journals_link on every update
            $journal->slug = $this->generateUniqueSlug(
                $validated['explore_journals_link'] ?? null,
                $validated['title'],
                $journal->id
            );

            $journal->description = $validated['description'] ?? null;
            $journal->is_active   = $request->boolean('is_active', true);

            // Old journals table fields
            $journal->title_2                = $validated['heading_1'] ?? null; // map heading_1 → title_2
            $journal->view_all_issues_label  = $validated['view_all_issues_label'] ?? null;
            $journal->view_all_issues_link   = $validated['view_all_issues_link'] ?? null;
            $journal->explore_journals_label = $validated['explore_journals_label'] ?? null;
            $journal->explore_journals_link  = $validated['explore_journals_link'] ?? null;
            $journal->fields_covered         = $validated['fields_covered'] ?? [];
            $journal->sequence               = $validated['sequence'] ?? 0;

            // Old journal table fields
            $journal->abbreviation              = $validated['abbreviation'] ?? null;
            $journal->e_issn                    = $validated['e_issn'] ?? null;
            $journal->p_issn                    = $validated['p_issn'] ?? null;
            $journal->issn_online               = $validated['issn_online'] ?? null;
            $journal->volume                    = $validated['volume'] ?? null;
            $journal->issue                     = $validated['issue'] ?? null;
            $journal->latest_volume             = $validated['latest_volume'] ?? null;
            $journal->publication_language       = $validated['publication_language'] ?? null;
            $journal->publishing_frequency       = $validated['publishing_frequency'] ?? null;
            $journal->publishing_months          = $validated['publishing_months'] ?? null;
            $journal->indexing_impact_factor     = $validated['indexing_impact_factor'] ?? null;
            $journal->time_to_first_decision     = $validated['time_to_first_decision'] ?? null;
            $journal->time_to_review             = $validated['time_to_review'] ?? null;
            $journal->acceptance_to_publication  = $validated['acceptance_to_publication'] ?? null;
            $journal->aim_and_scope_title         = $validated['aim_and_scope_title'] ?? null;
            $journal->aim_and_scope               = $validated['aim_and_scope'] ?? null;
            $journal->badge                       = $validated['badge'] ?? null;
            $journal->article_template_url        = $validated['article_template_url'] ?? null;

            $journal->save();

            Log::info('Journal updated successfully', [
                'journal_id' => $id,
                'before'     => $oldData,
                'after'      => $journal->toArray(),
            ]);

            $journal->heading_1 = $journal->title_2;

            return response()->json([
                'status'  => true,
                'message' => "Journal \"{$journal->title}\" updated successfully.",
                'data'    => $journal,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Journal not found for update', ['journal_id' => $id]);

            return response()->json([
                'status'  => false,
                'message' => 'Journal not found.',
            ], 404);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('Failed to update journal', [
                'journal_id' => $id,
                'input'      => $request->except('cover_image'),
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to update journal. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─── Delete Journal ───────────────────────────────────────────
    public function destroy($id)
    {
        try {
            $journal = Journal::findOrFail($id);
            $title   = $journal->title;

            if ($journal->cover_image) {
                Storage::disk('public')->delete($journal->cover_image);

                Log::info('Journal cover image deleted with record', [
                    'journal_id' => $id,
                    'path'       => $journal->cover_image,
                ]);
            }

            $journal->delete();

            Log::info('Journal deleted successfully', [
                'journal_id' => $id,
                'title'      => $title,
            ]);

            return response()->json([
                'status'  => true,
                'message' => "Journal \"{$title}\" deleted successfully.",
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Journal not found for deletion', ['journal_id' => $id]);

            return response()->json([
                'status'  => false,
                'message' => 'Journal not found.',
            ], 404);

        } catch (\Exception $e) {
            Log::error('Failed to delete journal', [
                'journal_id' => $id,
                'error'      => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to delete journal. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─── Toggle Active Status ─────────────────────────────────────
    public function toggleStatus($id)
    {
        try {
            $journal            = Journal::findOrFail($id);
            $journal->is_active = !$journal->is_active;
            $journal->save();

            Log::info('Journal status toggled', [
                'journal_id' => $id,
                'is_active'  => $journal->is_active,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Journal status updated successfully.',
                'data'    => $journal,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Journal not found for status toggle', ['journal_id' => $id]);

            return response()->json([
                'status'  => false,
                'message' => 'Journal not found.',
            ], 404);

        } catch (\Exception $e) {
            Log::error('Failed to toggle journal status', [
                'journal_id' => $id,
                'error'      => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to update status. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}