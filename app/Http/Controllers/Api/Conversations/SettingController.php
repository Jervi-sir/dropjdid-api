<?php

namespace App\Http\Controllers\Api\Conversations;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Clear all messages in a conversation.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function clear(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $conversation = Conversation::query()
            ->where('id', $id)
            ->where(function ($q) use ($user) {
                $q->where('first_user_id', $user->id)
                  ->orWhere('second_user_id', $user->id);
            })
            ->first();

        if (! $conversation) {
            return response()->json(['message' => 'Conversation not found.'], 404);
        }

        Message::where('conversation_id', $conversation->id)->delete();

        return response()->json([
            'message' => 'Conversation history cleared successfully.',
        ], 200);
    }

    /**
     * Delete / leave a conversation.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $conversation = Conversation::query()
            ->where('id', $id)
            ->where(function ($q) use ($user) {
                $q->where('first_user_id', $user->id)
                  ->orWhere('second_user_id', $user->id);
            })
            ->first();

        if (! $conversation) {
            return response()->json(['message' => 'Conversation not found.'], 404);
        }

        // Hide/delete conversation specifically for the requesting user
        if ($conversation->first_user_id === $user->id) {
            $conversation->update(['first_user_deleted_at' => now()]);
        } else {
            $conversation->update(['second_user_deleted_at' => now()]);
        }

        return response()->json([
            'message' => 'Conversation deleted successfully.',
        ], 200);
    }
}
