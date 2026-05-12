<?php

namespace App\Http\Controllers\Api\Conversations;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShowConversationController extends Controller
{
    public function __invoke(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        abort_if($user === null, 401);
        abort_unless($this->isParticipant($conversation, $user->id), 404);

        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $perPage = $validated['per_page'] ?? 20;

        $messages = $conversation->messages()
            ->with(['sender', 'attachable'])
            ->latest('id')
            ->simplePaginate($perPage);

        if ($conversation->first_user_id === $user->id) {
            $conversation->update(['first_user_last_read_at' => now()]);
        } else {
            $conversation->update(['second_user_last_read_at' => now()]);
        }

        $otherUser = $conversation->first_user_id === $user->id ? $conversation->secondUser : $conversation->firstUser;

        return response()->json([
            'conversation' => [
                'id' => $conversation->id,
                'type' => $conversation->type,
                'user' => [
                    'id' => $otherUser?->id,
                    'name' => $otherUser?->username,
                    'username' => $otherUser?->username,
                    'image' => $otherUser?->image,
                ],
            ],
            'data' => $messages->getCollection()->map(fn ($message): array => $message->formatForConversation($user))->values(),
            'next_page' => $messages->hasMorePages() ? $messages->currentPage() + 1 : null,
        ]);
    }

    private function isParticipant(Conversation $conversation, int $userId): bool
    {
        return $conversation->first_user_id === $userId || $conversation->second_user_id === $userId;
    }
}
