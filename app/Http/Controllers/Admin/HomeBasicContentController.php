<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeBasicContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HomeBasicContentController extends Controller
{
    private const IMAGE_DIR = 'images/home-content';

    private function rules(bool $isUpdate = false): array
    {
        $imageRule = $isUpdate
            ? 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048'
            : 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048';

        return [
            // Aim & Scope
            'aim_and_scope_title_1'          => 'nullable|string|max:255',   // badge / eyebrow
            'aim_and_scope_title_2'          => 'required|string|max:255',   // H1 heading
            'aim_and_scope_title_3'          => 'required|string|max:255',   // H2 sub-heading
            'aim_and_scope_description'      => 'required|string',
            'scope_of_publication_description' => 'required|string',
            'university_highlight_quote'     => 'nullable|string',
            'aim_section_image'              => $imageRule,

            // Why RNTU Stats
            'why_rntu_title_1'               => 'nullable|string|max:255',   // section heading
            'why_rntu_title_2'               => 'nullable|string|max:255',   // sub-heading
            'why_rntu_years'                 => 'required|string|max:50',
            'why_rntu_years_label'           => 'required|string|max:100',
            'why_rntu_articles'              => 'required|string|max:50',
            'why_rntu_articles_label'        => 'required|string|max:100',
            'why_rntu_journals'              => 'required|string|max:50',
            'why_rntu_journals_label'        => 'required|string|max:100',
            'why_rntu_readers'               => 'required|string|max:50',
            'why_rntu_readers_label'         => 'required|string|max:100',
            'why_rntu_access'                => 'required|string|max:50',
            'why_rntu_access_label'          => 'required|string|max:100',

            // Support Section
            'support_section_heading'        => 'required|string|max:255',
            'support_articles_count'         => 'required|string|max:50',
            'support_short_heading'          => 'required|string|max:255',
            'support_section_description'    => 'required|string',

            // Latest Journal Section
            'latest_journal_title'           => 'nullable|string|max:255',   // eyebrow/badge
            'latest_journal_heading'         => 'required|string|max:255',
            'latest_journal_description'     => 'required|string',

            // Footer
            'footer_about_description'       => 'required|string',
        ];
    }


    private function storeImage($file): string
    {
        $filename    = uniqid('home_') . '.' . $file->getClientOriginalExtension();
        $destination = public_path(self::IMAGE_DIR);

        if (!file_exists($destination)) {
            mkdir($destination, 0775, true);
        }

        $file->move($destination, $filename);

        return 'home-content/' . $filename;
    }

    private function deleteImage(?string $relativePath): void
    {
        if (!$relativePath) {
            return;
        }

        $fullPath = public_path('images/' . $relativePath);

        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }


    private function imageUrl(?string $relativePath): ?string
    {
        return $relativePath ? asset('images/' . $relativePath) : null;
    }

    public function adminIndex()
    {
        try {
            $record = HomeBasicContent::first();

            if (!$record) {
                return response()->json([
                    'status'  => true,
                    'message' => 'No record found.',
                    'data'    => null,
                ]);
            }

            $record->aim_section_image_url = $this->imageUrl($record->aim_section_image);

            return response()->json([
                'status'  => true,
                'message' => 'Home content fetched successfully.',
                'data'    => $record->toArray(),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch home content', ['error' => $e->getMessage()]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to fetch home content.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        // Enforce single-record constraint
        if (HomeBasicContent::exists()) {
            return response()->json([
                'status'  => false,
                'message' => 'A home content record already exists. Please edit the existing record.',
            ], 422);
        }

        $imagePath = null;

        try {
            $validated = $request->validate($this->rules(false));

            if ($request->hasFile('aim_section_image')) {
                $imagePath = $this->storeImage($request->file('aim_section_image'));

                Log::info('Home content image uploaded', ['path' => $imagePath]);
            }

            $record = HomeBasicContent::create(array_merge(
                collect($validated)->except('aim_section_image')->toArray(),
                ['aim_section_image' => $imagePath]
            ));

            $record->aim_section_image_url = $this->imageUrl($imagePath);

            Log::info('Home content created', ['id' => $record->id]);

            return response()->json([
                'status'  => true,
                'message' => 'Home content created successfully.',
                'data'    => $record->toArray(),
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            // Rollback uploaded file if DB insert failed
            if ($imagePath) {
                $this->deleteImage($imagePath);
                Log::warning('Rolled back image after failed insert', ['path' => $imagePath]);
            }

            Log::error('Failed to create home content', ['error' => $e->getMessage()]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to create home content. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $record = HomeBasicContent::findOrFail($id);

            $record->aim_section_image_url = $this->imageUrl($record->aim_section_image);

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
            Log::error('Failed to fetch home content record', [
                'id'    => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong.',
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $record = HomeBasicContent::findOrFail($id);

            $isPatch = strtoupper($request->input('_method', $request->method())) === 'PATCH';

            if ($isPatch) {
                $field = array_key_first($request->except('_method'));
                $allRules = $this->rules(true);
                $rules = isset($allRules[$field]) ? [$field => $allRules[$field]] : [];

                $validated = $request->validate($rules);
                $record->fill($validated);
                $record->save();

                Log::info('Home content field patched', ['id' => $id, 'field' => $field]);

                return response()->json([
                    'status'  => true,
                    'message' => 'Field updated successfully.',
                    'data'    => $record->toArray(),
                ]);
            }
            
            $validated = $request->validate($this->rules(true));

            if ($request->hasFile('aim_section_image')) {
                if ($record->aim_section_image) {
                    $this->deleteImage($record->aim_section_image);
                    Log::info('Old home content image deleted', ['path' => $record->aim_section_image]);
                }

                $record->aim_section_image = $this->storeImage($request->file('aim_section_image'));

                Log::info('New home content image uploaded', ['path' => $record->aim_section_image]);
            }

            $record->fill(collect($validated)->except('aim_section_image')->toArray());
            $record->save();

            $record->aim_section_image_url = $this->imageUrl($record->aim_section_image);

            Log::info('Home content updated', ['id' => $id]);

            return response()->json([
                'status'  => true,
                'message' => 'Home content updated successfully.',
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
            Log::error('Failed to update home content', [
                'id'    => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to update home content. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $record = HomeBasicContent::findOrFail($id);

            if ($record->aim_section_image) {
                $this->deleteImage($record->aim_section_image);
                Log::info('Home content image deleted', ['path' => $record->aim_section_image]);
            }

            $record->delete();

            Log::info('Home content record deleted', ['id' => $id]);

            return response()->json([
                'status'  => true,
                'message' => 'Home content record deleted successfully.',
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Record not found.',
            ], 404);

        } catch (\Exception $e) {
            Log::error('Failed to delete home content', [
                'id'    => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to delete home content. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}