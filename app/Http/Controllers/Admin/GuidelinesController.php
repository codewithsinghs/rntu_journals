<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guideline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class GuidelinesController extends Controller
{
    private function rules(?int $id = null): array
    {
        return [
            'journal_id' => [
                'required',
                'integer',
                'exists:journals,id',
                Rule::unique('guidelines', 'journal_id')->ignore($id),
            ],

            'author_badge'                => 'nullable|string|max:255',
            'author_heading'               => 'required|string|max:255',
            'author_description'           => 'required|string',

            'process_badge'                => 'nullable|string|max:255',
            'process_heading'              => 'required|string|max:255',
            'process_description'          => 'required|string',

            'manuscript_badge'             => 'nullable|string|max:255',
            'manuscript_heading'           => 'required|string|max:255',
            'manuscript_description'       => 'required|string',

            'formatting_badge1'            => 'nullable|string|max:255',
            'formatting_badge2'            => 'nullable|string|max:255',
            'formatting_heading'           => 'required|string|max:255',
            'formatting_description'       => 'required|string',

            'layout_badge1'                => 'nullable|string|max:255',
            'layout_heading'                => 'required|string|max:255',
            'layout_description'           => 'required|string',

            'acknowlegdement_badge1'       => 'nullable|string|max:255',
            'acknowlegdement_heading'      => 'required|string|max:255',
            'acknowlegdement_description'  => 'required|string',
        ];
    }

    private function messages(): array
    {
        return [
            'journal_id.required' => 'Please select a journal.',
            'journal_id.exists'   => 'Selected journal is invalid.',
            'journal_id.unique'   => 'This journal already has a guidelines page.',
        ];
    }

    // ─── Admin List (datatable — like Journal List) ─────────────────
    public function adminIndex(Request $request)
    {
        try {
            $query = Guideline::with('journal:id,title');

            if ($request->filled('q')) {
                $term = $request->string('q');
                $query->where(function ($q) use ($term) {
                    $q->where('author_heading', 'like', "%{$term}%")
                        ->orWhereHas('journal', function ($jq) use ($term) {
                            $jq->where('title', 'like', "%{$term}%");
                        });
                });
            }

            $records = $query->orderByDesc('created_at')
                ->paginate($request->integer('per_page', 10));

            return response()->json(['status' => true, 'data' => $records]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch guidelines list', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Failed to fetch guidelines.'], 500);
        }
    }

    // ─── Show Single Record ────────────────────────────────────────
    public function show($id)
    {
        try {
            $record = Guideline::with('journal:id,title')->findOrFail($id);
            return response()->json(['status' => true, 'data' => $record]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['status' => false, 'message' => 'Record not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Failed to fetch content.'], 500);
        }
    }

    // ─── Store ─────────────────────────────────────────────────────
    public function store(Request $request)
    {
        try {
            $validated = $request->validate($this->rules(), $this->messages());

            $record = Guideline::create($validated);
            $record->load('journal:id,title');

            return response()->json([
                'status'  => true,
                'message' => 'Guidelines created successfully.',
                'data'    => $record,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['status' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);

        } catch (\Exception $e) {
            Log::error('Failed to create guidelines content', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Failed to create content.'], 500);
        }
    }

    // ─── Update ────────────────────────────────────────────────────
    public function update(Request $request, $id)
    {
        try {
            $record    = Guideline::findOrFail($id);
            $validated = $request->validate($this->rules($record->id), $this->messages());

            $record->fill($validated);
            $record->save();
            $record->load('journal:id,title');

            return response()->json([
                'status'  => true,
                'message' => 'Guidelines updated successfully.',
                'data'    => $record,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['status' => false, 'message' => 'Record not found.'], 404);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['status' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);

        } catch (\Exception $e) {
            Log::error('Failed to update guidelines content', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Failed to update content.'], 500);
        }
    }

    // ─── Destroy ───────────────────────────────────────────────────
    public function destroy($id)
    {
        try {
            $record = Guideline::findOrFail($id);
            $record->delete();

            return response()->json([
                'status'  => true,
                'message' => 'Guidelines deleted successfully.',
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['status' => false, 'message' => 'Record not found.'], 404);

        } catch (\Exception $e) {
            Log::error('Failed to delete guidelines content', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Failed to delete content.'], 500);
        }
    }
}