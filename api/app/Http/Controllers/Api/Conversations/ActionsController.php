<?php

namespace App\Http\Controllers\Api\Conversations;

use App\Http\Controllers\Controller;
use App\Services\ConversationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ActionsController extends Controller
{
    public function __construct(
        protected ConversationService $conversationService
    ) {}

    /**
     * Get single conversation details and its paginated messages.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $conversation = $this->conversationService->getConversationForUser($id, $user->id);

        if (! $conversation) {
            return response()->json(['message' => 'Conversation not found.'], 404);
        }

        $perPage = (int) $request->query('per_page', 30);
        $page = (int) $request->query('page', 1);

        $messagesData = $this->conversationService->getPaginatedMessages($conversation->id, $page, $perPage);

        $convData = $conversation->toConversationType($user->id);
        $wasReset = (bool) ($conversation->was_reset ?? false);

        return response()->json([
            'conversation' => $convData,
            'messages' => $messagesData['messages'],
            'next_page' => $messagesData['next_page'],
            'was_reset' => $wasReset,
        ], 200);
    }

    /**
     * Start a new conversation with a user or retrieve existing one.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function startConversation(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => ['required', 'integer', 'exists:users,id', 'different:current_user_id'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $targetUserId = (int) $request->input('user_id');

        if ($targetUserId === $user->id) {
            return response()->json(['message' => 'Cannot start a conversation with yourself.'], 422);
        }

        $conversation = $this->conversationService->getOrCreatePrivateConversation($user->id, $targetUserId);
        $convData = $conversation->toConversationType($user->id);
        $wasReset = (bool) ($conversation->was_reset ?? false);

        return response()->json([
            'data' => $convData,
            'conversation' => $convData,
            'was_reset' => $wasReset,
        ], 200);
    }

    /**
     * Send a message in an existing conversation.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function sendMessage(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $conversation = $this->conversationService->getConversationForUser($id, $user->id);

        if (! $conversation) {
            return response()->json(['message' => 'Conversation not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'type' => ['required', 'string', 'in:text,image,product,drop,ad,profile'],
            'message' => ['nullable', 'string'],
            'image_url' => ['nullable', 'string'],
            'image' => ['nullable', 'file', 'image', 'max:10240'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'drop_id' => ['nullable', 'integer', 'exists:drops,id'],
            'ad_id' => ['nullable', 'integer', 'exists:advertisements,id'],
            'profile_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $message = $this->conversationService->createMessage(
            conversation: $conversation,
            senderId: $user->id,
            data: $request->all(),
            imageFile: $request->file('image')
        );

        return response()->json([
            'message' => 'Message sent successfully.',
            'data' => $message->toMessageType(),
        ], 201);
    }

    /**
     * Share an item directly to a user (creating the conversation if it does not exist)
     * and automatically recording the share interaction.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function shareToUser(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'type' => ['required', 'string', 'in:text,image,product,drop,ad,profile'],
            'message' => ['nullable', 'string'],
            'image_url' => ['nullable', 'string'],
            'image' => ['nullable', 'file', 'image', 'max:10240'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'drop_id' => ['nullable', 'integer', 'exists:drops,id'],
            'ad_id' => ['nullable', 'integer', 'exists:advertisements,id'],
            'profile_id' => ['nullable', 'integer', 'exists:users,id'],
            'channel' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $targetUserId = (int) $request->input('user_id');

        if ($targetUserId === $user->id) {
            return response()->json(['message' => 'Cannot share with yourself.'], 422);
        }

        $result = $this->conversationService->shareToUser(
            senderId: $user->id,
            targetUserId: $targetUserId,
            data: $request->all(),
            imageFile: $request->file('image'),
            channel: $request->input('channel', 'app')
        );

        return response()->json([
            'message' => 'Shared successfully.',
            'conversation_id' => $result['conversation']->id,
            'data' => $result['message']->toMessageType(),
            'conversation' => $result['conversation']->toConversationType($user->id),
        ], 201);
    }

    /**
     * Delete a message within a conversation.
     *
     * @param Request $request
     * @param int $id Conversation ID
     * @param int $messageId Message ID
     * @return JsonResponse
     */
    public function deleteMessage(Request $request, int $id, int $messageId): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $conversation = $this->conversationService->getConversationForUser($id, $user->id);

        if (! $conversation) {
            return response()->json(['message' => 'Conversation not found.'], 404);
        }

        $deleted = $this->conversationService->deleteMessage($conversation, $messageId, $user->id);

        if (! $deleted) {
            return response()->json(['message' => 'Message not found or not authorized to delete.'], 404);
        }

        return response()->json([
            'message' => 'Message deleted successfully.',
            'message_id' => $messageId,
        ], 200);
    }

    /**
     * Mark a conversation as seen/read.
     *
     * @param Request $request
     * @param int $id Conversation ID
     * @return JsonResponse
     */
    public function markSeen(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $conversation = $this->conversationService->getConversationForUser($id, $user->id);

        if (! $conversation) {
            return response()->json(['message' => 'Conversation not found.'], 404);
        }

        $this->conversationService->markConversationAsSeen($conversation, $user->id);

        return response()->json([
            'message' => 'Conversation marked as read.',
        ], 200);
    }
}
