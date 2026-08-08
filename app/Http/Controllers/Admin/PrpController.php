<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PRP;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PrpController extends Controller
{
    private function rules(bool $isUpdate = false): array
    {
        return [
            // ── PRPs Author ──────────────────────────────
            'author_badge'               => 'nullable|string|max:255',
            'author_heading'             => 'required|string|max:255',
            'author_description'         => 'required|string',

            // ── Process Submission ──────────────────────────────
            'process_badge'              => 'nullable|string|max:255',
            'process_heading'            => 'required|string|max:255',
            'process_description'        => 'required|string',

            // ── Manuscript ──────────────────────────────────────
            'manuscript_badge'           => 'nullable|string|max:255',
            'manuscript_heading'         => 'required|string|max:255',
            'manuscript_description'     => 'required|string',

            // ── Document Formatting ──────────────────────────────
            'formatting_badge1'          => 'nullable|string|max:255',
            'formatting_badge2'          => 'nullable|string|max:255',
            'formatting_heading'         => 'required|string|max:255',
            'formatting_description'     => 'required|string',

            // ── Page Layout ──────────────────────────────────────
            'layout_badge1'              => 'nullable|string|max:255',
            'layout_heading'             => 'required|string|max:255',
            'layout_description'         => 'required|string',

            // ── Acknowledgement ───────────────────────────────────
            'acknowlegdement_badge1'     => 'nullable|string|max:255',
            'acknowlegdement_heading'    => 'required|string|max:255',
            'acknowlegdement_description'=> 'required|string',
        ];
    }

    // ─── Admin Index ───────────────────────────────────────────────
    public function adminIndex()
    {
        try {
            $record = PRP::latest()->first();
            return response()->json(['status' => true, 'data' => $record]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch PRPs content', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Failed to fetch content.'], 500);
        }
    }


    // ─── Show Single Record ────────────────────────────────────────
    public function show($id)
    {
        try {
            $record = PRP::findOrFail($id);
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
            $validated = $request->validate($this->rules(false));

            $record = PRP::create($validated);

            return response()->json([
                'status'  => true,
                'message' => 'PRPs content created successfully.',
                'data'    => $record,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['status' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);

        } catch (\Exception $e) {
            Log::error('Failed to create PRPs content', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Failed to create content.'], 500);
        }
    }

    // ─── Update ────────────────────────────────────────────────────
    public function update(Request $request, $id)
    {
        try {
            $record    = PRP::findOrFail($id);
            $validated = $request->validate($this->rules(true));

            $record->fill($validated);
            $record->save();

            return response()->json([
                'status'  => true,
                'message' => 'PRPs content updated successfully.',
                'data'    => $record->fresh(),
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['status' => false, 'message' => 'Record not found.'], 404);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['status' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);

        } catch (\Exception $e) {
            Log::error('Failed to update PRPs content', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Failed to update content.'], 500);
        }
    }

    // ─── Destroy ───────────────────────────────────────────────────
    public function destroy($id)
    {
        try {
            $record = PRP::findOrFail($id);
            $record->delete();

            return response()->json([
                'status'  => true,
                'message' => 'PRPs content deleted successfully.',
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['status' => false, 'message' => 'Record not found.'], 404);

        } catch (\Exception $e) {
            Log::error('Failed to delete PRPs content', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Failed to delete content.'], 500);
        }
    }
}