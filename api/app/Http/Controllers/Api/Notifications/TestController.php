<?php

namespace App\Http\Controllers\Api\Notifications;

use App\Events\MessageSent;
use App\Events\NotificationSent;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Notification;
use App\Models\NotificationType;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TestController extends Controller
{
    /**
     * Test endpoint to broadcast real-time notifications or conversation messages without authentication.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        // 1. If conversation_id is provided, send & broadcast a conversation message
        if ($request->filled('conversation_id') || $request->filled('message')) {
            $conversationId = (int) ($request->input('conversation_id') ?? $request->query('conversation_id') ?? 1);
            $conversation = Conversation::find($conversationId) ?? Conversation::first();

            if (! $conversation) {
                return response()->json(['message' => 'No conversation found.'], 404);
            }

            $senderId = (int) ($request->input('sender_id') ?? $request->query('sender_id') ?? $conversation->first_user_id);
            $text = $request->input('message') ?? $request->query('message') ?? 'Test message sent at ' . now()->toTimeString();

            $msg = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $senderId,
                'type' => 'text',
                'body' => $text,
            ]);

            $conversation->touch();

            // Broadcast to presence-conversation.{id}
            broadcast(new MessageSent($msg, $conversation));

            return response()->json([
                'message' => 'Test conversation message sent and broadcasted successfully.',
                'channel' => 'presence-conversation.' . $conversation->id,
                'data' => $msg->toMessageType(),
            ], 201);
        }

        // 2. Otherwise, create & broadcast a real-time notification
        $targetUserId = (int) ($request->input('user_id') ?? $request->query('user_id') ?? $request->user('sanctum')?->id ?? 6);
        $targetUser = User::find($targetUserId) ?? User::first();

        if (! $targetUser) {
            return response()->json(['message' => 'No user found in database.'], 404);
        }

        // Get or create notification type
        $type = NotificationType::firstOrCreate(
            ['code' => 'sale'],
            ['en' => 'Sale', 'fr' => 'Vente', 'ar' => 'مبيعات']
        );

        $amount = rand(1500, 9500);
        $notificationData = [
            'target' => 'product',
            'price' => number_format($amount, 2, '.', '') . ' DZD',
            'direction' => 'up',
            'text1' => 'New Order Received #' . rand(100, 999),
            'text2' => 'Someone just purchased your product!',
            'image_url' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=200',
        ];

        $notification = Notification::create([
            'notification_type_id' => $type->id,
            'user_id' => $targetUser->id,
            'notifiable_type' => User::class,
            'notifiable_id' => $targetUser->id,
            'data' => $notificationData,
            'read_at' => null,
        ]);

        $unreadCount = Notification::query()
            ->where('user_id', $targetUser->id)
            ->whereNull('read_at')
            ->count();

        $formatted = [
            'id' => (int) $notification->id,
            'type' => 'sale',
            'created_at' => $notification->created_at ? $notification->created_at->toISOString() : now()->toISOString(),
            'image_url' => $notificationData['image_url'],
            'sale_meta' => [
                'target' => $notificationData['target'],
                'text1' => (string) $notificationData['text1'],
                'price' => (string) $notificationData['price'],
                'direction' => $notificationData['direction'],
            ],
            'withdraw_meta' => null,
            'order_meta' => null,
            'friend_request_meta' => null,
            'follower_meta' => null,
        ];

        // Broadcast to user's private channel in real-time (channel: private-user.{id})
        broadcast(new NotificationSent($targetUser->id, $formatted, $unreadCount));

        return response()->json([
            'message' => 'Test notification created and broadcasted successfully.',
            'channel' => 'private-user.' . $targetUser->id,
            'unread_count' => $unreadCount,
            'has_unread' => true,
            'notification' => $formatted,
        ], 201);
    }
}
