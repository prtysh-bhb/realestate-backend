<?php

namespace App\Http\Controllers\Api\Agent;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    // List agent's blogs
    public function index(Request $request)
    {
        $blogs = Blog::where('user_id', auth()->id())
            ->with(['category', 'reviewer'])
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($blogs);
    }

    // Get single blog
    public function show($id)
    {
        $blog = Blog::where('user_id', auth()->id())
            ->with(['category', 'reviewer'])
            ->findOrFail($id);

        return response()->json($blog);
    }

    // Create blog
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'nullable|exists:blog_categories,id',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'status' => 'required|in:draft,pending',
            'meta_tags' => 'nullable|array',
        ]);

        $slug = Str::slug($request->title);
        $originalSlug = $slug;
        $count = 1;
        
        while (Blog::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        $data = $request->except('image');
        $data['user_id'] = auth()->id();
        $data['slug'] = $slug;

        // Handle image upload properly
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('blogs', $filename, 'public');
            $data['image'] = $path;
        }

        $blog = Blog::create($data);

        return response()->json([
            'message' => 'Blog created successfully',
            'blog' => $blog->load('category'),
        ], 201);
    }

    // Update blog
    public function update(Request $request, $id)
    {
        $blog = Blog::where('user_id', auth()->id())->findOrFail($id);

        // Can only edit if draft or rejected
        if (!in_array($blog->status, ['draft', 'rejected'])) {
            return response()->json([
                'message' => 'Cannot edit blog in ' . $blog->status . ' status',
            ], 403);
        }

        $request->validate([
            'title' => 'sometimes|required|string|max:255',         
            'category_id' => 'nullable|exists:blog_categories,id',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'sometimes|required|string',                
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'status' => 'sometimes|required|in:draft,pending',      
            'meta_tags' => 'nullable|array',
        ]);

        $data = $request->except('image');

        // Update slug if title changed
        if ($request->has('title') && $request->title !== $blog->title) {
            $slug = Str::slug($request->title);
            $originalSlug = $slug;
            $count = 1;
            
            while (Blog::where('slug', $slug)->where('id', '!=', $blog->id)->exists()) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }
            $data['slug'] = $slug;
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            if ($blog->image && Storage::disk('public')->exists($blog->image)) {
                Storage::disk('public')->delete($blog->image);
            }
            
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('blogs', $filename, 'public');
            $data['image'] = $path;
        }

        // Reset review status if resubmitting
        if ($request->has('status') && $request->status === 'pending' && $blog->status === 'rejected') {
            $data['rejection_reason'] = null;
            $data['reviewed_by'] = null;
            $data['reviewed_at'] = null;
        }

        $blog->update($data);

        return response()->json([
            'message' => 'Blog updated successfully',
            'blog' => $blog->load('category'),
        ]);
    }

    // Delete blog
    public function destroy($id)
    {
        $blog = Blog::where('user_id', auth()->id())->findOrFail($id);

        // Delete image if exists
        if ($blog->image && Storage::disk('public')->exists($blog->image)) {
            Storage::disk('public')->delete($blog->image);
        }

        $blog->delete();

        return response()->json([
            'message' => 'Blog deleted successfully',
        ]);
    }

    // Get categories
    public function categories()
    {
        $categories = BlogCategory::where('is_active', true)->get();
        return response()->json($categories);
    }

    // Blog statistics
    public function statistics()
    {
        $userId = auth()->id();

        $stats = [
            'total' => Blog::where('user_id', $userId)->count(),
            'draft' => Blog::where('user_id', $userId)->where('status', 'draft')->count(),
            'pending' => Blog::where('user_id', $userId)->where('status', 'pending')->count(),
            'approved' => Blog::where('user_id', $userId)->where('status', 'approved')->count(),
            'rejected' => Blog::where('user_id', $userId)->where('status', 'rejected')->count(),
            'total_views' => Blog::where('user_id', $userId)->sum('views_count'),
        ];

        return response()->json($stats);
    }
}