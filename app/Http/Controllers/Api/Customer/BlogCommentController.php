<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogComment;
use Illuminate\Http\Request;

class BlogCommentController extends Controller
{
    /**
     * POST /customer/blogs/{blogId}/comments
     * Add comment (auto-approved)
     */
    public function store(Request $request, $blogId)
    {
        $blog = Blog::published()->findOrFail($blogId);

        $validated = $request->validate([
            'comment' => 'required|string|min:3|max:1000',
        ]);

        $comment = BlogComment::create([
            'blog_id' => $blog->id,
            'user_id' => auth()->id(),
            'comment' => $validated['comment'],
            'is_approved' => true, // Auto-approve
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Comment added successfully',
            'data' => $comment->load('user:id,name,avatar'),
        ], 201);
    }

    /**
     * GET /customer/my-comments
     * Get customer's own comments
     */
    public function myComments()
    {
        $comments = BlogComment::where('user_id', auth()->id())
            ->with(['blog:id,title,slug', 'user:id,name,avatar'])
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $comments,
        ]);
    }

    /**
     * PUT /customer/comments/{id}
     * Update own comment
     */
    public function update(Request $request, $id)
    {
        $comment = BlogComment::where('user_id', auth()->id())->findOrFail($id);

        $validated = $request->validate([
            'comment' => 'required|string|min:3|max:1000',
        ]);

        $comment->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Comment updated successfully',
            'data' => $comment,
        ]);
    }

    /**
     * DELETE /customer/comments/{id}
     * Delete own comment
     */
    public function destroy($id)
    {
        $comment = BlogComment::where('user_id', auth()->id())->findOrFail($id);
        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Comment deleted successfully',
        ]);
    }
}