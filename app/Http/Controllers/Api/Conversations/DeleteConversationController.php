<?php

namespace App\Http\Controllers\Api\Conversations;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeleteConversationController extends Controller
{
    public function __invoke(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        abort_if($user === null, 401);
        abort_unless($conversation->first_user_id === $user->id || $conversation->second_user_id === $user->id, 404);

        $conversation->delete();

        return response()->json([
            'message' => 'Conversation deleted successfully.',
        ]);
    }
}
