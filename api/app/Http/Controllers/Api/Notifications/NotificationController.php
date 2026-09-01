<?php

namespace App\Http\Controllers\Api\Notifications;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get unread notifications count for the authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $userId = $request->user('sanctum')?->id ?? $request->user()?->id ?? $request->query('user_id');

        if (! $userId) {
            return response()->json(['unread_count' => 0, 'has_unread' => false], 200);
        }

        $unreadCount = Notification::query()
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'unread_count' => $unreadCount,
            'has_unread' => $unreadCount > 0,
        ], 200);
    }

    /**
     * Mark all notifications as read for the authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $userId = $request->user('sanctum')?->id ?? $request->user()?->id ?? $request->input('user_id');

        if (! $userId) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        Notification::query()
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'message' => 'All notifications marked as read.',
            'unread_count' => 0,
            'has_unread' => false,
        ], 200);
    }
}
