<?php

namespace App\Http\Controllers\Admin\Friendships;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Friendship;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ActionFriendshipController extends Controller
{
    /**
     * Show friendship details, including sender/receiver profile details and conversation stats.
     */
    public function show(Friendship $friendship): JsonResponse
    {
        $friendship->load(['sender', 'receiver']);

        // Check if there is an existing conversation between the two users
        $conversation = Conversation::where(function ($q) use ($friendship) {
            $q->where('first_user_id', $friendship->sender_id)
                ->where('second_user_id', $friendship->receiver_id);
        })->orWhere(function ($q) use ($friendship) {
            $q->where('first_user_id', $friendship->receiver_id)
                ->where('second_user_id', $friendship->sender_id);
        })->first();

        $conversationData = null;
        if ($conversation) {
            $conversationData = [
                'id' => $conversation->id,
                'type' => Conversation::TYPE[$conversation->type] ?? 'private',
                'type_raw' => $conversation->type,
                'messages_count' => $conversation->messages()->count(),
                'created_at' => $conversation->created_at?->toIso8601String(),
                'updated_at' => $conversation->updated_at?->toIso8601String(),
            ];
        }

        return response()->json([
            'friendship' => [
                'id' => $friendship->id,
                'sender' => $friendship->sender ? [
                    'id' => $friendship->sender->id,
                    'full_name' => $friendship->sender->full_name,
                    'username' => $friendship->sender->username,
                    'email' => $friendship->sender->email,
                    'image' => $friendship->sender->image,
                ] : null,
                'receiver' => $friendship->receiver ? [
                    'id' => $friendship->receiver->id,
                    'full_name' => $friendship->receiver->full_name,
                    'username' => $friendship->receiver->username,
                    'email' => $friendship->receiver->email,
                    'image' => $friendship->receiver->image,
                ] : null,
                'status' => Friendship::STATUSES[$friendship->status] ?? 'unknown',
                'status_raw' => $friendship->status,
                'accepted_at' => $friendship->accepted_at?->toIso8601String(),
                'rejected_at' => $friendship->rejected_at?->toIso8601String(),
                'blocked_at' => $friendship->blocked_at?->toIso8601String(),
                'created_at' => $friendship->created_at?->toIso8601String(),
            ],
            'conversation' => $conversationData,
        ]);
    }

    /**
     * Update the friendship status and corresponding action timestamps.
     */
    public function update(Request $request, Friendship $friendship): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|integer|in:'.implode(',', array_keys(Friendship::STATUSES)),
        ]);

        $newStatus = (int) $validated['status'];
        $updateData = [
            'status' => $newStatus,
        ];

        // Apply state-based action timestamps
        if ($newStatus === Friendship::STATUS_ACCEPTED) {
            $updateData['accepted_at'] = now();
        } elseif ($newStatus === Friendship::STATUS_REJECTED) {
            $updateData['rejected_at'] = now();
        } elseif ($newStatus === Friendship::STATUS_BLOCKED) {
            $updateData['blocked_at'] = now();
        }

        $friendship->update($updateData);

        return back()->with('success', 'Friendship status updated successfully.');
    }

    /**
     * Delete/terminate the friendship relation completely.
     */
    public function destroy(Friendship $friendship): RedirectResponse
    {
        $friendship->delete();

        return back()->with('success', 'Friendship deleted successfully.');
    }
}
