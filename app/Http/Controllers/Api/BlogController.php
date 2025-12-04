<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogCategory;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    // List published blogs (public)
    public function index(Request $request)
    {
        $blogs = Blog::published()
            ->with(['user', 'category'])
            ->withCount('approvedComments')
            ->when($request->category_id, function ($query, $categoryId) {
                return $query->where('category_id', $categoryId);
            })
            ->when($request->search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('excerpt', 'like', "%{$search}%")
                      ->orWhere('content', 'like', "%{$search}%");
                });
            })
            ->orderBy('published_at', 'desc')
            ->paginate(12);

        return response()->json([
            'success' => true,
            'data' => $blogs
        ]);
    }

    // Get single blog (public)
    public function show($slug)
    {
        $blog = Blog::where('slug', $slug)
            ->published()
            ->with(['user', 'category', 'approvedComments.user'])
            ->firstOrFail();

        // Increment view count
        $blog->increment('views_count');

        return response()->json([
            'success' => true,
            'data' => $blog
        ]);
    }

    // Get featured blogs
    public function featured()
    {
        $blogs = Blog::published()
            ->with(['user', 'category'])
            ->withCount('approvedComments')
            ->orderBy('views_count', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $blogs
        ]);
    }

    // Get latest blogs
    public function latest()
    {
        $blogs = Blog::published()
            ->with(['user', 'category'])
            ->withCount('approvedComments')
            ->orderBy('published_at', 'desc')
            ->take(6)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $blogs
        ]);
    }

    // Get related blogs
    public function related($slug)
    {
        $blog = Blog::where('slug', $slug)->published()->firstOrFail();

        $relatedBlogs = Blog::published()
            ->where('id', '!=', $blog->id)
            ->where(function ($query) use ($blog) {
                $query->where('category_id', $blog->category_id)
                      ->orWhereHas('user', function ($q) use ($blog) {
                          $q->where('id', $blog->user_id);
                      });
            })
            ->with(['user', 'category'])
            ->withCount('approvedComments')
            ->orderBy('published_at', 'desc')
            ->take(3)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $relatedBlogs
        ]);
    }

    // Get blogs by category
    public function byCategory($categorySlug)
    {
        $category = BlogCategory::where('slug', $categorySlug)
            ->where('is_active', true)
            ->firstOrFail();

        $blogs = Blog::published()
            ->where('category_id', $category->id)
            ->with(['user', 'category'])
            ->withCount('approvedComments')
            ->orderBy('published_at', 'desc')
            ->paginate(12);

        return response()->json([
            'success' => true,
            'category' => $category,
            'data' => $blogs
        ]);
    }

    // Get blogs by author
    public function byAuthor($userId)
    {
        $blogs = Blog::published()
            ->where('user_id', $userId)
            ->with(['user', 'category'])
            ->withCount('approvedComments')
            ->orderBy('published_at', 'desc')
            ->paginate(12);

        return response()->json([
            'success' => true,
            'data' => $blogs
        ]);
    }

    // Get all active categories
    public function categories()
    {
        $categories = BlogCategory::where('is_active', true)
            ->withCount(['blogs' => function ($query) {
                $query->published();
            }])
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    // Search blogs
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2',
        ]);

        $query = $request->q;

        $blogs = Blog::published()
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('excerpt', 'like', "%{$query}%")
                  ->orWhere('content', 'like', "%{$query}%");
            })
            ->with(['user', 'category'])
            ->withCount('approvedComments')
            ->orderBy('published_at', 'desc')
            ->paginate(12);

        return response()->json([
            'success' => true,
            'query' => $query,
            'data' => $blogs
        ]);
    }

    // Get popular blogs (by views)
    public function popular()
    {
        $blogs = Blog::published()
            ->with(['user', 'category'])
            ->withCount('approvedComments')
            ->orderBy('views_count', 'desc')
            ->take(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $blogs
        ]);
    }

    // Get blog statistics (public)
    public function statistics()
    {
        $stats = [
            'total_blogs' => Blog::published()->count(),
            'total_categories' => BlogCategory::where('is_active', true)->count(),
            'total_views' => Blog::published()->sum('views_count'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}