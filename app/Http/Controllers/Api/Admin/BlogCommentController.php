<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogComment;
use Illuminate\Http\Request;

class BlogCommentController extends Controller
{
    /**
     * GET /admin/blog-comments
     * List all comments with filters
     */
    public function index(Request $request)
    {
        $comments = BlogComment::with(['blog:id,title,slug', 'user:id,name,email,avatar'])
            ->when($request->blog_id, function ($query, $blogId) {
                $query->where('blog_id', $blogId);
            })
            ->when($request->user_id, function ($query, $userId) {
                $query->where('user_id', $userId);
            })
            ->when($request->search, function ($query, $search) {
                $query->where('comment', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $comments,
        ]);
    }

    /**
     * GET /admin/blog-comments/{id}
     * Get single comment
     */
    public function show($id)
    {
        $comment = BlogComment::with(['blog:id,title,slug', 'user:id,name,email,avatar'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $comment,
        ]);
    }

    /**
     * DELETE /admin/blog-comments/{id}
     * Delete comment
     */
    public function destroy($id)
    {
        $comment = BlogComment::findOrFail($id);
        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Comment deleted successfully',
        ]);
    }

    /**
     * GET /admin/blog-comments/statistics
     * Comment statistics
     */
    public function statistics()
    {
        $stats = [
            'total_comments' => BlogComment::count(),
            'today_comments' => BlogComment::whereDate('created_at', today())->count(),
            'this_week_comments' => BlogComment::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month_comments' => BlogComment::whereMonth('created_at', now()->month)->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}