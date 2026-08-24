<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PageController extends Controller
{
    public function adminIndex(Request $request)
    {
        $perPage = (int) $request->get('per_page', 10);
        $query = Page::query();

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $pages = $query->orderByDesc('id')->paginate($perPage);

        return response()->json([
            'status'  => true,
            'message' => 'Pages fetched successfully.',
            'data'    => $pages,
        ]);
    }

    public function show($id)
    {
        $page = Page::find($id);

        if (!$page) {
            return response()->json([
                'status'  => false,
                'message' => 'Page not found.',
            ], 404);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Page fetched successfully.',
            'data'    => $page,
        ]);
    }

    public function store(Request $request)
    {
        return $this->save($request);
    }

    public function update(Request $request, $id)
    {
        $page = Page::find($id);

        if (!$page) {
            return response()->json([
                'status'  => false,
                'message' => 'Page not found.',
            ], 404);
        }

        return $this->save($request, $page);
    }

    protected function save(Request $request, ?Page $page = null)
    {
        $validator = Validator::make($request->all(), [
            'title'            => 'required|string|max:255',
            'slug'             => 'nullable|string|max:255',
            'content'          => 'required|string',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:1000',
            'meta_image'       => 'nullable|image|max:2048',
            'status'           => 'nullable|in:draft,published',
            'is_homepage'      => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        $data['slug'] = Page::generateUniqueSlug(
            $data['slug'] ?: $data['title'],
            $page?->id
        );

        $data['status']      = $data['status'] ?? 'draft';
        $data['is_homepage'] = $request->boolean('is_homepage');

        if ($request->hasFile('meta_image')) {
            if ($page && $page->meta_image) {
                Storage::disk('public')->delete($page->meta_image);
            }
            $data['meta_image'] = $request->file('meta_image')->store('pages', 'public');
        }

        // Only one page can be the homepage at a time
        if ($data['is_homepage']) {
            Page::where('is_homepage', true)
                ->when($page, fn ($q) => $q->where('id', '!=', $page->id))
                ->update(['is_homepage' => false]);
        }

        if ($page) {
            $page->update($data);
            $message = 'Page updated successfully.';
        } else {
            $page = Page::create($data);
            $message = 'Page created successfully.';
        }

        return response()->json([
            'status'  => true,
            'message' => $message,
            'data'    => $page,
        ]);
    }

    public function destroy($id)
    {
        $page = Page::find($id);

        if (!$page) {
            return response()->json([
                'status'  => false,
                'message' => 'Page not found.',
            ], 404);
        }

        if ($page->meta_image) {
            Storage::disk('public')->delete($page->meta_image);
        }

        $page->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Page deleted successfully.',
        ]);
    }

public function toggleStatus($id)
{
    $page = Page::find($id);

    if (!$page) {
        return response()->json([
            'status'  => false,
            'message' => 'Page not found.',
        ], 404);
    }

    $page->status = $page->status === 'published' ? 'draft' : 'published';
    $page->save();

    return response()->json([
        'status'  => true,
        'message' => 'Status updated successfully.',
        'data'    => $page,
    ]);
}
}