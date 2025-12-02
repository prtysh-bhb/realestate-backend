<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Services\ContentService;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    protected $service;

    public function __construct(ContentService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $data = $this->service->list(Blog::class);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status'      => 'nullable|boolean',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image');
        }

        $blog = $this->service->create(
            Blog::class,
            $data,
            'image',
            'blogs' // uploads/blogs/
        );

        return response()->json([
            'success' => true,
            'message' => 'Blog created successfully',
            'data' => $blog
        ]);
    }

    public function show($id)
    {
        $blog = Blog::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $blog
        ]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status'      => 'nullable|boolean',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image');
        }

        $blog = $this->service->update(
            Blog::class,
            $id,
            $data,
            'image',
            'blogs'
        );

        return response()->json([
            'success' => true,
            'message' => 'Blog updated successfully',
            'data' => $blog
        ]);
    }

    public function destroy($id)
    {
        $this->service->delete(
            Blog::class,
            $id,
            'image',
            'blogs'
        );

        return response()->json([
            'success' => true,
            'message' => 'Blog deleted successfully'
        ]);
    }

    public function updateStatus($id)
    {
        $result = $this->service->updateStatus(Blog::class, $id);

        return response()->json($result, $result['success'] ? 200 : 404);
    }
}
