<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AboutBasicContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AboutBasicContentController extends Controller
{
    private function rules(bool $isUpdate = false): array
    {
        $imgRule = $isUpdate ? 'nullable' : 'nullable';

        return [
            'about_badge'         => 'nullable|string|max:255',
            'about_heading'       => 'required|string|max:255',
            'about_description_1' => 'required|string',
            'about_description_2' => 'nullable|string',
            'about_section_img1'  => "{$imgRule}|file|mimes:jpg,jpeg,png,webp|max:2048",
            'about_section_img2'  => "{$imgRule}|file|mimes:jpg,jpeg,png,webp|max:2048",
            'why_badge'           => 'nullable|string|max:255',
            'why_heading'         => 'required|string|max:255',
            'why_description_1'   => 'required|string',
            'why_description_2'   => 'nullable|string',
            'why_section_image'   => "{$imgRule}|file|mimes:jpg,jpeg,png,webp|max:2048",
        ];
    }

    public function adminIndex()
    {
        try {
            $record = AboutBasicContent::latest()->first();
            return response()->json(['status' => true, 'data' => $record]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch about content', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Failed to fetch content.'], 500);
        }
    }


// ─── Show single record ────────────────────────────────────────────
public function show($id)
{
    try {
        $record = AboutBasicContent::findOrFail($id);
        return response()->json(['status' => true, 'data' => $record]);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json(['status' => false, 'message' => 'Record not found.'], 404);
    } catch (\Exception $e) {
        return response()->json(['status' => false, 'message' => 'Failed to fetch content.'], 500);
    }
}
    

    public function store(Request $request)
    {
        $uploaded = [];

        try {
            $validated = $request->validate($this->rules(false));

            foreach (['about_section_img1', 'about_section_img2', 'why_section_image'] as $field) {
                if ($request->hasFile($field)) {
                    $uploaded[$field] = $request->file($field)->store("about/{$field}", 'public');
                }
            }

            $record = AboutBasicContent::create(array_merge(
                collect($validated)->except(['about_section_img1', 'about_section_img2', 'why_section_image'])->toArray(),
                $uploaded
            ));

            return response()->json([
                'status'  => true,
                'message' => 'About content created successfully.',
                'data'    => $record,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Rollback any uploads already stored
            foreach ($uploaded as $path) {
                Storage::disk('public')->delete($path);
            }
            return response()->json(['status' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);

        } catch (\Exception $e) {
            foreach ($uploaded as $path) {
                Storage::disk('public')->delete($path);
            }
            Log::error('Failed to create about content', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Failed to create content.'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $record    = AboutBasicContent::findOrFail($id);
            $validated = $request->validate($this->rules(true));

            foreach (['about_section_img1', 'about_section_img2', 'why_section_image'] as $field) {
                if ($request->hasFile($field)) {
                    if ($record->$field) {
                        Storage::disk('public')->delete($record->$field);
                    }
                    $record->$field = $request->file($field)->store("about/{$field}", 'public');
                }
            }

            $record->fill(collect($validated)->except(['about_section_img1', 'about_section_img2', 'why_section_image'])->toArray());
            $record->save();

            return response()->json([
                'status'  => true,
                'message' => 'About content updated successfully.',
                'data'    => $record->fresh(),
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['status' => false, 'message' => 'Record not found.'], 404);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['status' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);

        } catch (\Exception $e) {
            Log::error('Failed to update about content', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Failed to update content.'], 500);
        }
    }
}