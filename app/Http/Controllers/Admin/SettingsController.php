<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Setting;
use App\Models\SettingMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    private const ALLOWED_MIMES = 'jpg,jpeg,png,gif,webp,bmp,svg,mp4,mov,avi,webm,mkv,pdf,doc,docx,xls,xlsx,ppt,pptx,csv,txt,zip';
    private const ALLOWED_MEDIA_KEYS = ['logo', 'favicon'];

    public function show()
    {
        try {
            $settings = Setting::with('mediaSlots.media')->first();

            if (!$settings) {
                $settings = Setting::create([]);
                $settings->load('mediaSlots.media');
            }

            return response()->json([
                'status'  => true,
                'message' => 'Settings fetched successfully.',
                'data'    => $this->transformSettings($settings),
            ]);
        } catch (\Exception $e) {
            Log::error('Settings fetch failed: ' . $e->getMessage());

            return response()->json([
                'status'  => false,
                'message' => 'Failed to fetch settings.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'address'       => 'nullable|string|max:500',
                'email'         => 'nullable|email|max:255',
                'phone'         => 'nullable|string|max:50',
                'website_name'  => 'nullable|string|max:255',
                'website_url'   => 'nullable|url|max:255',
                'facebook_url'  => 'nullable|url|max:255',
                'instagram_url' => 'nullable|url|max:255',
                'twitter_url'   => 'nullable|url|max:255',
                'youtube_url'   => 'nullable|url|max:255',
                'linkedin_url'  => 'nullable|url|max:255',
                'meta'          => 'nullable|array',
            ]);

            DB::beginTransaction();

            $settings = Setting::first();

            if (!$settings) {
                $settings = Setting::create($validated);
            } else {
                $settings->update($validated);
            }

            DB::commit();

            $settings->load('mediaSlots.media');

            Log::info('Settings updated', [
                'settings_id' => $settings->id,
                'user_id'     => Auth::id(),
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Settings updated successfully.',
                'data'    => $this->transformSettings($settings),
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Settings update failed: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to update settings.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function uploadMedia(Request $request, string $key)
    {
        try {
            if (!in_array($key, self::ALLOWED_MEDIA_KEYS, true)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Invalid media slot key.',
                    'errors'  => ['key' => ["'{$key}' is not an allowed media slot."]],
                ], 422);
            }

            $request->validate([
                'file' => 'required|file|max:10240|mimes:' . self::ALLOWED_MIMES,
            ]);

            $file   = $request->file('file');
            $userId = Auth::id();

            $settings = Setting::firstOrCreate([]);

            DB::beginTransaction();

            $ext      = strtolower($file->getClientOriginalExtension());
            $baseName = Str::slug($key . '-' . pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
            $baseName = $baseName ?: $key;
            $filename = $baseName . '-' . Str::random(8) . '.' . $ext;
            $path     = $file->storeAs('media/settings', $filename, 'public');

            $media = Media::create([
                'user_id'       => $userId,
                'filename'      => $filename,
                'original_name' => $file->getClientOriginalName(),
                'mime_type'     => $file->getMimeType(),
                'size'          => $file->getSize(),
                'disk'          => 'public',
                'path'          => $path,
                'url'           => Storage::disk('public')->url($path),
                'meta'          => ['settings_key' => $key],
            ]);

            $previousMedia = $settings->getMedia($key);

            $slot = $settings->setMedia($key, $media->id);

            if ($previousMedia && $previousMedia->id !== $media->id) {
                if ($previousMedia->path && Storage::disk($previousMedia->disk)->exists($previousMedia->path)) {
                    Storage::disk($previousMedia->disk)->delete($previousMedia->path);
                }
                $previousMedia->delete();
            }

            DB::commit();

            Log::info('Settings media uploaded', [
                'settings_id' => $settings->id,
                'key'         => $key,
                'media_id'    => $media->id,
                'user_id'     => $userId,
            ]);

            return response()->json([
                'status'  => true,
                'message' => ucfirst($key) . ' uploaded successfully.',
                'data'    => [
                    'key'   => $key,
                    'media' => $media,
                    'slot'  => $slot,
                ],
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

            Log::error('Settings media upload failed: ' . $e->getMessage(), [
                'key'     => $key,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to upload media.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function removeMedia(Request $request, string $key)
    {
        try {
            if (!in_array($key, self::ALLOWED_MEDIA_KEYS, true)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Invalid media slot key.',
                    'errors'  => ['key' => ["'{$key}' is not an allowed media slot."]],
                ], 422);
            }

            $settings = Setting::first();

            if (!$settings) {
                return response()->json(['status' => false, 'message' => 'Settings not found.'], 404);
            }

            $media = $settings->getMedia($key);

            if (!$media) {
                return response()->json(['status' => false, 'message' => "No media is set for '{$key}'."], 404);
            }

            $deleteFile = $request->boolean('delete_file', true);

            DB::beginTransaction();

            $settings->removeMedia($key);

            if ($deleteFile) {
                if ($media->path && Storage::disk($media->disk)->exists($media->path)) {
                    Storage::disk($media->disk)->delete($media->path);
                }
                $media->delete();
            }

            DB::commit();

            Log::info('Settings media removed', [
                'settings_id'  => $settings->id,
                'key'          => $key,
                'media_id'     => $media->id,
                'deleted_file' => $deleteFile,
                'user_id'      => Auth::id(),
            ]);

            return response()->json([
                'status'  => true,
                'message' => ucfirst($key) . ' removed successfully.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Settings media removal failed: ' . $e->getMessage(), [
                'key'     => $key,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to remove media.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    private function transformSettings(Setting $settings): array
    {
        $payload = $settings->toArray();

        // Build a flat key => media map from the eager-loaded slots
        $payload['media'] = $settings->mediaSlots
            ->mapWithKeys(fn (SettingMedia $slot) => [
                $slot->key => $slot->media   // media, not media
            ])
            ->toArray();

        unset($payload['media_slots']); // remove the raw relation array

        return $payload;
    }
}