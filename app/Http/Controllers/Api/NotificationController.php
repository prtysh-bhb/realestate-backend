<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get all notifications (paginated)
     */
    public function index(Request $request)
    {
        $notifications = auth()->user()
            ->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => [
                'notifications' => $notifications->items(),
                'pagination' => [
                    'total' => $notifications->total(),
                    'per_page' => $notifications->perPage(),
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                ],
            ],
        ]);
    }

    /**
     * Get unread notifications only
     */
    public function unread()
    {
        $notifications = auth()->user()
            ->unreadNotifications()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'notifications' => $notifications,
                'count' => $notifications->count(),
            ],
        ]);
    }

    /**
     * Get unread notification count
     */
    public function count()
    {
        $count = auth()->user()->unreadNotifications()->count();

        return response()->json([
            'success' => true,
            'data' => [
                'count' => $count,
            ],
        ]);
    }

    /**
     * Mark single notification as read
     */
    public function markAsRead($id)
    {
        try {
            $notification = auth()->user()
                ->notifications()
                ->findOrFail($id);

            $notification->markAsRead();

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read',
                'data' => [
                    'notification' => $notification,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found',
            ], 404);
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
        ]);
    }

    /**
     * Delete single notification
     */
    public function destroy($id)
    {
        try {
            $notification = auth()->user()
                ->notifications()
                ->findOrFail($id);

            $notification->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notification deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found',
            ], 404);
        }
    }

    /**
     * Delete all read notifications
     */
    public function deleteAllRead()
    {
        $count = auth()->user()
            ->notifications()
            ->whereNotNull('read_at')
            ->delete();

        return response()->json([
            'success' => true,
            'message' => "{$count} notifications deleted successfully",
            'data' => [
                'deleted_count' => $count,
            ],
        ]);
    }
}