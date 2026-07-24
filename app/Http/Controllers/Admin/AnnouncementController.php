<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
    /**
     * Validation rules shared by store() and update().
     */


    private function rules(): array
    {
        return [
            'name'       => 'required|string|max:255',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png,webp|max:5120',
            'link'       => 'nullable|url|max:255',
            'sequence'   => 'nullable|integer',
            'meta'       => 'nullable|array',
        ];
    }

    // ─── List All Announcements (Admin) ────────────────────────────────────────
    // Protected by 'jwt' middleware on the route.
    public function adminIndex()
    {
        try {
            $announcements = Announcement::ordered()->paginate(10);

            return response()->json([
                'status'  => true,
                'message' => 'Announcements fetched successfully.',
                'data'    => $announcements,
            ]);

        } catch (\Exception $e) {

            Log::error('Failed to fetch announcements', ['error' => $e->getMessage()]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to fetch announcements.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─── Create Announcement ───────────────────────────────────────────────────
    // Protected by 'jwt' middleware on the route.
    public function store(Request $request)
    {
        $attachmentPath = null;

        try {
            $validated = $request->validate($this->rules());

            if ($request->hasFile('attachment')) {
                $attachmentPath = $request->file('attachment')
                    ->store('announcements/attachments', 'public');

                Log::info('Announcement attachment uploaded', [
                    'path'          => $attachmentPath,
                    'original_name' => $request->file('attachment')->getClientOriginalName(),
                    'size'          => $request->file('attachment')->getSize(),
                ]);
            }

            $announcement = Announcement::create([
                'name'       => $validated['name'],
                'attachment' => $attachmentPath,
                'link'       => $validated['link'] ?? null,
                'sequence'   => $validated['sequence'] ?? 0,
                'meta'       => $validated['meta'] ?? [],
            ]);

            Log::info('Announcement created successfully', [
                'announcement_id' => $announcement->id,
                'name'            => $announcement->name,
                'has_attachment'  => !is_null($attachmentPath),
            ]);

            return response()->json([
                'status'  => true,
                'message' => "Announcement \"{$announcement->name}\" created successfully.",
                'data'    => $announcement,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Exception $e) {

            Log::error('Failed to create announcement', [
                'input' => $request->except('attachment'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Rollback uploaded file if DB insert failed
            if (!empty($attachmentPath)) {
                Storage::disk('public')->delete($attachmentPath);
                Log::warning('Rolled back attachment upload after failed insert', [
                    'path' => $attachmentPath,
                ]);
            }

            return response()->json([
                'status'  => false,
                'message' => 'Failed to create announcement. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─── View Single Announcement (Public, no auth) ────────────────────────────
    public function show($id)
    {
        try {
            $announcement = Announcement::findOrFail($id);

            Log::info('Announcement fetched', ['announcement_id' => $id]);

            return response()->json([
                'status' => true,
                'data'   => $announcement,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            Log::warning('Announcement not found', ['announcement_id' => $id]);

            return response()->json([
                'status'  => false,
                'message' => 'Announcement not found.',
            ], 404);

        } catch (\Exception $e) {

            Log::error('Failed to fetch announcement', [
                'announcement_id' => $id,
                'error'           => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong.',
            ], 500);
        }
    }

    // ─── Update Announcement ────────────────────────────────────────

    public function update(Request $request, $id)
    {
        try {
            $announcement = Announcement::findOrFail($id);

            $validated = $request->validate($this->rules());

            $oldData = $announcement->toArray();

            if ($request->hasFile('attachment')) {

                // Delete old attachment
                if ($announcement->attachment) {
                    Storage::disk('public')->delete($announcement->attachment);

                    Log::info('Old announcement attachment deleted', [
                        'announcement_id' => $id,
                        'old_path'        => $announcement->attachment,
                    ]);
                }

                $announcement->attachment = $request->file('attachment')
                    ->store('announcements/attachments', 'public');

                Log::info('New announcement attachment uploaded', [
                    'announcement_id' => $id,
                    'new_path'        => $announcement->attachment,
                    'original_name'   => $request->file('attachment')->getClientOriginalName(),
                ]);
            }

            $announcement->name     = $validated['name'];
            $announcement->link     = $validated['link'] ?? null;
            $announcement->sequence = $validated['sequence'] ?? 0;
            $announcement->meta     = $validated['meta'] ?? [];
            $announcement->save();

            Log::info('Announcement updated successfully', [
                'announcement_id' => $id,
                'before'          => $oldData,
                'after'           => $announcement->toArray(),
            ]);

            return response()->json([
                'status'  => true,
                'message' => "Announcement \"{$announcement->name}\" updated successfully.",
                'data'    => $announcement,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            Log::warning('Announcement not found for update', ['announcement_id' => $id]);

            return response()->json([
                'status'  => false,
                'message' => 'Announcement not found.',
            ], 404);

        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Exception $e) {

            Log::error('Failed to update announcement', [
                'announcement_id' => $id,
                'input'           => $request->except('attachment'),
                'error'           => $e->getMessage(),
                'trace'           => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to update announcement. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─── Delete Announcement ───────────────────────────────────────────────────
    
    public function destroy($id)
    {
        try {
            $announcement = Announcement::findOrFail($id);
            $name         = $announcement->name;

            if ($announcement->attachment) {
                Storage::disk('public')->delete($announcement->attachment);

                Log::info('Announcement attachment deleted with record', [
                    'announcement_id' => $id,
                    'path'            => $announcement->attachment,
                ]);
            }

            $announcement->delete();

            Log::info('Announcement deleted successfully', [
                'announcement_id' => $id,
                'name'            => $name,
            ]);

            return response()->json([
                'status'  => true,
                'message' => "Announcement \"{$name}\" deleted successfully.",
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            Log::warning('Announcement not found for deletion', ['announcement_id' => $id]);

            return response()->json([
                'status'  => false,
                'message' => 'Announcement not found.',
            ], 404);

        } catch (\Exception $e) {

            Log::error('Failed to delete announcement', [
                'announcement_id' => $id,
                'error'           => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to delete announcement. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


}