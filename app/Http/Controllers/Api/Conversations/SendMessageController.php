<?php

namespace App\Http\Controllers\Api\Conversations;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SendMessageController extends Controller
{
    public function __invoke(Request $request, int $conversation_id): JsonResponse
    {
        $user = $request->user();

        $conversation = Conversation::find($conversation_id);

        abort_if($user === null, 401);
        abort_unless($conversation->first_user_id === $user->id || $conversation->second_user_id === $user->id, 404);

        $validated = $request->validate([
            'type' => ['required', 'in:text,image'],
            'body' => ['required', 'string'],
        ]);

        $message = Message::query()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'type' => $validated['type'],
            'body' => trim($validated['body']),
        ]);

        $conversation->touch();
        $message->load('sender');

        return response()->json([
            'message' => 'Message sent successfully.',
            'data' => $message->formatForConversation($user),
        ], 201);
    }
}
