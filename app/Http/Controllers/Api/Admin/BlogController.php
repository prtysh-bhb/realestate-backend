<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Services\ContentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    protected $service;

    public function __construct(ContentService $service)
    {
        $this->service = $service;
    }

    // Original list method - keep for backward compatibility
    public function index(Request $request)
    {
        // If using ContentService listing
        if (!$request->has('status') && !$request->has('search')) {
            $data = $this->service->list(Blog::class);
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        }

        // Enhanced listing with filters
        $blogs = Blog::with(['user', 'category', 'reviewer'])
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->search, function ($query, $search) {
                return $query->where('title', 'like', "%{$search}%");
            })
            ->when($request->category_id, function ($query, $categoryId) {
                return $query->where('category_id', $categoryId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $blogs
        ]);
    }

    // Original store method - keep for backward compatibility
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:191',
            'description' => 'nullable|string|max:2000',
            'content' => 'nullable|string',
            'excerpt' => 'nullable|string|max:500',
            'category_id' => 'nullable|exists:blog_categories,id',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'featured_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'status' => 'nullable|in:draft,pending,approved,rejected',
            'meta_tags' => 'nullable|array',
        ]);

        $slug = Str::slug($request->title);
        $originalSlug = $slug;
        $count = 1;
        
        while (Blog::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        $validated['slug'] = $slug;
        $validated['user_id'] = auth()->id();

        // Handle image upload
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('blogs', $filename, 'public');
            $validated['image'] = $path;
        }

        // Handle featured_image upload
        if ($request->hasFile('featured_image')) {
            $file = $request->file('featured_image');
            $filename = time() . '_featured_' . $file->getClientOriginalName();
            $path = $file->storeAs('blogs', $filename, 'public');
            $validated['featured_image'] = $path;
        }

        if (!isset($validated['status'])) {
            $validated['status'] = 'draft';
        }

        if ($validated['status'] === 'approved') {
            $validated['reviewed_by'] = auth()->id();
            $validated['reviewed_at'] = now();
            $validated['published_at'] = now();
        }

        $blog = Blog::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Blog created successfully',
            'data' => $blog->load('category', 'user'),
        ], 201);
    }

    // Original show method
    public function show($id)
    {
        $blog = Blog::with(['user', 'category', 'reviewer', 'comments.user'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $blog
        ]);
    }

    // Original update method
    public function update(Request $request, $id)
    {
        $blog = Blog::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:191',
            'description' => 'nullable|string|max:2000',
            'content' => 'nullable|string',
            'excerpt' => 'nullable|string|max:500',
            'category_id' => 'nullable|exists:blog_categories,id',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'featured_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'status' => 'nullable|in:draft,pending,approved,rejected',
            'meta_tags' => 'nullable|array',
        ]);

        if ($request->has('title') && $request->title !== $blog->title) {
            $slug = Str::slug($request->title);
            $originalSlug = $slug;
            $count = 1;
            
            while (Blog::where('slug', $slug)->where('id', '!=', $blog->id)->exists()) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }
            $validated['slug'] = $slug;
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            if ($blog->image && Storage::disk('public')->exists($blog->image)) {
                Storage::disk('public')->delete($blog->image);
            }
            
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('blogs', $filename, 'public');
            $validated['image'] = $path;
        }

        // Handle featured_image upload
        if ($request->hasFile('featured_image')) {
            if ($blog->featured_image && Storage::disk('public')->exists($blog->featured_image)) {
                Storage::disk('public')->delete($blog->featured_image);
            }
            
            $file = $request->file('featured_image');
            $filename = time() . '_featured_' . $file->getClientOriginalName();
            $path = $file->storeAs('blogs', $filename, 'public');
            $validated['featured_image'] = $path;
        }

        if (isset($validated['status']) && $validated['status'] === 'approved' && $blog->status !== 'approved') {
            $validated['reviewed_by'] = auth()->id();
            $validated['reviewed_at'] = now();
            $validated['published_at'] = now();
        }

        $blog->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Blog updated successfully',
            'data' => $blog->load('category', 'user'),
        ]);
    }

    // Original destroy method
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

    // Original updateStatus method
    public function updateStatus($id)
    {
        $result = $this->service->updateStatus(Blog::class, $id);
        return response()->json($result, $result['success'] ? 200 : 404);
    }

    // ========== NEW MODERATION METHODS ==========

    // Approve blog (moderation)
    public function approve(Request $request, $id)
    {
        $blog = Blog::findOrFail($id);

        if ($blog->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending blogs can be approved',
            ], 400);
        }

        $blog->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'published_at' => $request->publish_now ? now() : ($request->published_at ?? now()),
            'rejection_reason' => null,
        ]);

        // TODO: Send notification to agent

        return response()->json([
            'success' => true,
            'message' => 'Blog approved successfully',
            'data' => $blog->load(['user', 'category', 'reviewer']),
        ]);
    }

    // Reject blog (moderation)
    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $blog = Blog::findOrFail($id);

        if ($blog->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending blogs can be rejected',
            ], 400);
        }

        $blog->update([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'rejection_reason' => $request->rejection_reason,
            'published_at' => null,
        ]);

        // TODO: Send notification to agent

        return response()->json([
            'success' => true,
            'message' => 'Blog rejected',
            'data' => $blog->load(['user', 'category', 'reviewer']),
        ]);
    }

    // Blog statistics
    public function statistics()
    {
        $stats = [
            'total' => Blog::count(),
            'pending' => Blog::where('status', 'pending')->count(),
            'approved' => Blog::where('status', 'approved')->count(),
            'rejected' => Blog::where('status', 'rejected')->count(),
            'draft' => Blog::where('status', 'draft')->count(),
            'total_views' => Blog::sum('views_count'),
            'published' => Blog::published()->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    // Get pending blogs (for moderation queue)
    public function pending()
    {
        $blogs = Blog::pending()
            ->with(['user', 'category'])
            ->orderBy('created_at', 'asc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $blogs
        ]);
    }

    // ========== CATEGORY MANAGEMENT ==========

    public function indexCategories()
    {
        $categories = BlogCategory::withCount('blogs')->orderBy('name')->get();
        
        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:blog_categories,name',
            'description' => 'nullable|string',
        ]);

        $category = BlogCategory::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'data' => $category,
        ], 201);
    }

    public function updateCategory(Request $request, $id)
    {
        $category = BlogCategory::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:blog_categories,name,' . $id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'is_active' => $request->is_active ?? $category->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'data' => $category,
        ]);
    }

    public function updateCategoryStatus(Request $request, $id)
    {
        $category = BlogCategory::findOrFail($id);
        $category->is_active = !$category->is_active;
        $category->save();

        return response()->json([
            'success' => true,
            'message' => 'Category status updated successfully',
            'data' => $category,
        ]);
    }

    public function destroyCategory($id)
    {
        $category = BlogCategory::findOrFail($id);
        
        if ($category->blogs()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with existing blogs',
            ], 400);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully',
        ]);
    }
}