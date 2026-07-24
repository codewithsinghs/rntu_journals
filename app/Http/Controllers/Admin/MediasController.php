<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediasController extends Controller
{
    /**
     * Allowed upload extensions (images, video, documents, spreadsheets).
     * Keep in sync with the `mimes:` rule below.
     */
    private const ALLOWED_MIMES = 'jpg,jpeg,png,gif,webp,bmp,svg,mp4,mov,avi,webm,mkv,pdf,doc,docx,xls,xlsx,ppt,pptx,csv,txt,zip';

    /**
     * List Media (paginated)
     * Auth: handled by JwtMiddleware before this method runs.
     */
    public function index()
    {
        try {
            $media = Media::latest()->paginate(20);

            return response()->json([
                'status'  => true,
                'message' => 'Media fetched successfully.',
                'data'    => $media,
            ]);
        } catch (\Exception $e) {
            Log::error('Media index failed: ' . $e->getMessage());

            return response()->json([
                'status'  => false,
                'message' => 'Failed to fetch media.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload Media
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'file'        => 'required|file|max:10240|mimes:' . self::ALLOWED_MIMES,
                'custom_name' => 'nullable|string|max:255',
            ]);

            $file = $request->file('file');
            $userId = Auth::id();

            DB::beginTransaction();

            $ext      = strtolower($file->getClientOriginalExtension());
            $baseName = $request->filled('custom_name')
                ? Str::slug($request->input('custom_name'))
                : Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));

            if ($baseName === '') {
                $baseName = 'file';
            }

            $filename = $baseName . '-' . Str::random(8) . '.' . $ext;

            $path = $file->storeAs('media', $filename, 'public');

            $media = Media::create([
                'user_id'       => $userId,
                'filename'      => $filename,
                'original_name' => $request->filled('custom_name')
                    ? $request->input('custom_name') . '.' . $ext
                    : $file->getClientOriginalName(),
                'mime_type'     => $file->getMimeType(),
                'size'          => $file->getSize(),
                'disk'          => 'public',
                'path'          => $path,
                'url'           => Storage::disk('public')->url($path),
                'meta'          => null,
            ]);

            DB::commit();

            Log::info('Media uploaded', [
                'media_id' => $media->id,
                'user_id'  => $userId,
                'filename' => $filename,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Media uploaded successfully.',
                'data'    => $media,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($path) && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }

            Log::error('Media upload failed: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to upload media.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update Media (metadata and/or replace file)
     */
    public function update(Request $request, Media $media)
    {
        try {
            $validated = $request->validate([
                'original_name' => 'required|string|max:255',
                'new_file'      => 'nullable|file|max:10240|mimes:' . self::ALLOWED_MIMES,
            ]);

            $userId = Auth::id();

            DB::beginTransaction();

            $data = [
                'original_name' => $validated['original_name'],
            ];

            $oldPath = $media->path;
            $oldDisk = $media->disk;

            if ($request->hasFile('new_file')) {
                $file = $request->file('new_file');

                $ext      = strtolower($file->getClientOriginalExtension());
                $baseName = Str::slug(pathinfo($validated['original_name'], PATHINFO_FILENAME));

                if ($baseName === '') {
                    $baseName = 'file';
                }

                $filename = $baseName . '-' . Str::random(8) . '.' . $ext;

                $path = $file->storeAs('media', $filename, 'public');

                $data['filename']  = $filename;
                $data['mime_type'] = $file->getMimeType();
                $data['size']      = $file->getSize();
                $data['disk']      = 'public';
                $data['path']      = $path;
                $data['url']       = Storage::disk('public')->url($path);
            }

            $media->update($data);

            if ($request->hasFile('new_file') && $oldPath && Storage::disk($oldDisk)->exists($oldPath)) {
                Storage::disk($oldDisk)->delete($oldPath);
            }

            DB::commit();

            Log::info('Media updated', [
                'media_id' => $media->id,
                'user_id'  => $userId,
                'replaced_file' => $request->hasFile('new_file'),
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Media updated successfully.',
                'data'    => $media->fresh(),
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Media update failed: ' . $e->getMessage(), [
                'media_id' => $media->id ?? null,
                'user_id'  => Auth::id(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to update media.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete Media
     */
    public function destroy(Media $media)
    {
        try {
            $userId = Auth::id();
            $mediaId = $media->id;

            DB::beginTransaction();

            if ($media->path && Storage::disk($media->disk)->exists($media->path)) {
                Storage::disk($media->disk)->delete($media->path);
            }

            $media->delete();

            DB::commit();

            Log::info('Media deleted', [
                'media_id' => $mediaId,
                'user_id'  => $userId,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Media deleted successfully.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Media delete failed: ' . $e->getMessage(), [
                'media_id' => $media->id ?? null,
                'user_id'  => Auth::id(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to delete media.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    
}