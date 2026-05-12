<?php

namespace App\Http\Controllers\Api\Conversations;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeleteMessageController extends Controller
{
    public function __invoke(Request $request, int $conversation_id, int $message_id): JsonResponse
    {
        $user = $request->user();
        $conversation = Conversation::find($conversation_id);
        $message = Message::find($message_id);

        abort_if($user === null, 401);
        abort_unless($conversation->first_user_id === $user->id || $conversation->second_user_id === $user->id, 404);
        abort_unless($message->conversation_id === $conversation->id, 404);
        abort_unless($message->sender_id === $user->id, 403);

        $message->delete();
        $conversation->touch();

        return response()->json([
            'message' => 'Message deleted successfully.',
        ]);
    }
}
