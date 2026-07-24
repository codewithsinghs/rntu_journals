<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    // ── Validation rules ──────────────────────────────────────────────────────────
    private function rules(): array
    {
        return [
            // Contact Block 1
            'contact_badge'    => 'required|string|max:255',
            'contact_heading1' => 'required|string|max:255',
            'contact_detail1'  => 'required|string',

            // Contact Block 2
            'contact_heading2' => 'required|string|max:255',
            'contact_detail2'  => 'required|string',

            // Contact Block 3
            'contact_heading3' => 'required|string|max:255',
            'contact_detail3'  => 'required|string',
        ];
    }

    // ── GET /api/admin/contact ────────────────────────────────────────────────────
    public function adminIndex()
    {
        try {
            $record = Contact::first();

            if (!$record) {
                return response()->json([
                    'status'  => true,
                    'message' => 'No record found.',
                    'data'    => null,
                ]);
            }

            return response()->json([
                'status'  => true,
                'message' => 'Contact content fetched successfully.',
                'data'    => $record->toArray(),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch contact content', ['error' => $e->getMessage()]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to fetch contact content.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ── POST /api/admin/contact ───────────────────────────────────────────────────
    public function store(Request $request)
    {
        // Enforce single-record constraint
        if (Contact::exists()) {
            return response()->json([
                'status'  => false,
                'message' => 'A contact record already exists. Please edit the existing record.',
            ], 422);
        }

        try {
            $validated = $request->validate($this->rules());

            $record = Contact::create($validated);

            Log::info('Contact content created', ['id' => $record->id]);

            return response()->json([
                'status'  => true,
                'message' => 'Contact content created successfully.',
                'data'    => $record->toArray(),
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('Failed to create contact content', ['error' => $e->getMessage()]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to create contact content. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ── GET /api/admin/contact/{id} ───────────────────────────────────────────────
    public function show($id)
    {
        try {
            $record = Contact::findOrFail($id);

            return response()->json([
                'status' => true,
                'data'   => $record,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Record not found.',
            ], 404);

        } catch (\Exception $e) {
            Log::error('Failed to fetch contact record', [
                'id'    => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong.',
            ], 500);
        }
    }

    // ── POST /api/admin/contact/{id}  (_method=PUT / PATCH) ──────────────────────
    public function update(Request $request, $id)
    {
        try {
            $record = Contact::findOrFail($id);

            // Support both full update (PUT) and single-field patch (PATCH)
            $isPatch = strtoupper($request->input('_method', $request->method())) === 'PATCH';

            if ($isPatch) {
                // Inline single-field edit — only validate the one field sent
                $field    = array_key_first($request->except('_method'));
                $allRules = $this->rules();
                $rules    = isset($allRules[$field]) ? [$field => $allRules[$field]] : [];

                $validated = $request->validate($rules);
                $record->fill($validated);
                $record->save();

                Log::info('Contact field patched', ['id' => $id, 'field' => $field]);

                return response()->json([
                    'status'  => true,
                    'message' => 'Field updated successfully.',
                    'data'    => $record->toArray(),
                ]);
            }

            // Full PUT update
            $validated = $request->validate($this->rules());

            $record->fill($validated);
            $record->save();

            Log::info('Contact content updated', ['id' => $id]);

            return response()->json([
                'status'  => true,
                'message' => 'Contact content updated successfully.',
                'data'    => $record->toArray(),
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Record not found.',
            ], 404);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('Failed to update contact content', [
                'id'    => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to update contact content. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ── DELETE /api/admin/contact/{id} ────────────────────────────────────────────
    public function destroy($id)
    {
        try {
            $record = Contact::findOrFail($id);

            $record->delete();

            Log::info('Contact record deleted', ['id' => $id]);

            return response()->json([
                'status'  => true,
                'message' => 'Contact record deleted successfully.',
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Record not found.',
            ], 404);

        } catch (\Exception $e) {
            Log::error('Failed to delete contact content', [
                'id'    => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to delete contact content. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}