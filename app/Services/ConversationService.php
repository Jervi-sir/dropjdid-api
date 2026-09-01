<?php

namespace App\Services;

use App\Events\MessageDeleted;
use App\Events\MessageSent;
use App\Events\MessagesSeen;
use App\Models\Advertisement;
use App\Models\Conversation;
use App\Models\Drop;
use App\Models\Message;
use App\Models\Product;
use App\Models\User;
use App\Models\UserInteraction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ConversationService
{
    /**
     * Get or create a private 1-on-1 conversation between two users.
     * Restores the conversation for User A if it was previously hidden/deleted by them.
     */
    public function getOrCreatePrivateConversation(int $userAId, int $userBId): Conversation
    {
        $firstId = min($userAId, $userBId);
        $secondId = max($userAId, $userBId);

        $conversation = Conversation::where('first_user_id', $firstId)
            ->where('second_user_id', $secondId)
            ->first();

        if ($conversation) {
            $isFirst = $conversation->first_user_id === $userAId;
            $wasDeleted = $isFirst
                ? ! is_null($conversation->first_user_deleted_at)
                : ! is_null($conversation->second_user_deleted_at);

            if ($wasDeleted) {
                $conversation->update([
                    $isFirst ? 'first_user_deleted_at' : 'second_user_deleted_at' => null,
                ]);
                $conversation->setAttribute('was_reset', true);
            }

            return $conversation;
        }

        return Conversation::create([
            'first_user_id' => $firstId,
            'second_user_id' => $secondId,
            'type' => 'private',
        ]);
    }

    /**
     * Retrieve a conversation for a user with authorization check and mark as read.
     * Restores the conversation for this user if it was previously hidden/deleted by them.
     */
    public function getConversationForUser(int $conversationId, int $userId): ?Conversation
    {
        $conversation = Conversation::query()
            ->where('id', $conversationId)
            ->where(function ($q) use ($userId) {
                $q->where('first_user_id', $userId)
                  ->orWhere('second_user_id', $userId);
            })
            ->with(['firstUser', 'secondUser'])
            ->first();

        if (! $conversation) {
            return null;
        }

        $isFirst = $conversation->first_user_id === $userId;
        $wasDeleted = $isFirst
            ? ! is_null($conversation->first_user_deleted_at)
            : ! is_null($conversation->second_user_deleted_at);

        if ($wasDeleted) {
            $conversation->update([
                $isFirst ? 'first_user_deleted_at' : 'second_user_deleted_at' => null,
            ]);
            $conversation->setAttribute('was_reset', true);
        }

        // Update read receipt and broadcast seen event
        $this->markConversationAsSeen($conversation, $userId);

        return $conversation;
    }

    /**
     * Mark a conversation as seen for a specific user and broadcast to others.
     */
    public function markConversationAsSeen(Conversation $conversation, int $userId): void
    {
        $isFirst = $conversation->first_user_id === $userId;
        if ($isFirst) {
            $conversation->update(['first_user_last_read_at' => now()]);
        } else {
            $conversation->update(['second_user_last_read_at' => now()]);
        }

        // Broadcast to the other user that messages in this conversation were seen
        broadcast(new MessagesSeen($conversation->id, $userId, now()->toIso8601String()))->toOthers();
    }

    /**
     * Fetch paginated messages for a conversation formatted for mobile clients.
     */
    public function getPaginatedMessages(int $conversationId, int $page = 1, int $perPage = 30): array
    {
        $paginator = Message::query()
            ->where('conversation_id', $conversationId)
            ->latest('id')
            ->paginate($perPage, ['*'], 'page', $page);

        $messages = $paginator->getCollection()
            ->map(fn (Message $m) => $m->toMessageType())
            ->reverse()
            ->values();

        $nextPage = $paginator->hasMorePages() ? $paginator->currentPage() + 1 : null;

        return [
            'messages' => $messages,
            'next_page' => $nextPage,
        ];
    }

    /**
     * Create and attach a message to an existing conversation and broadcast in real time.
     */
    public function createMessage(
        Conversation $conversation,
        int $senderId,
        array $data,
        ?UploadedFile $imageFile = null
    ): Message {
        $type = $data['type'];
        $body = $data['message'] ?? null;
        $attachableType = null;
        $attachableId = null;

        if ($type === 'image') {
            if ($imageFile) {
                $filename = 'msg_' . time() . '_' . uniqid() . '.' . $imageFile->getClientOriginalExtension();
                $path = $imageFile->storeAs('uploads/messages', $filename, 'public');
                $body = url(Storage::url($path));
            } else {
                $body = $data['image_url'] ?? $body;
            }
        } elseif ($type === 'product') {
            $attachableType = Product::class;
            $attachableId = isset($data['product_id']) ? (int) $data['product_id'] : null;
        } elseif ($type === 'drop') {
            $attachableType = Drop::class;
            $attachableId = isset($data['drop_id']) ? (int) $data['drop_id'] : null;
        } elseif ($type === 'ad') {
            $attachableType = Advertisement::class;
            $attachableId = isset($data['ad_id']) ? (int) $data['ad_id'] : null;
        } elseif ($type === 'profile') {
            $attachableType = User::class;
            $attachableId = isset($data['profile_id']) ? (int) $data['profile_id'] : null;
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $senderId,
            'type' => $type,
            'body' => $body,
            'attachable_type' => $attachableType,
            'attachable_id' => $attachableId,
        ]);

        // When a new message is sent, un-hide the conversation for both participants
        $conversation->update([
            'first_user_deleted_at' => null,
            'second_user_deleted_at' => null,
        ]);
        $conversation->touch();

        // Broadcast to participants via Reverb WebSockets
        broadcast(new MessageSent($message, $conversation))->toOthers();

        return $message;
    }

    /**
     * Delete a single message from a conversation and broadcast the deletion.
     */
    public function deleteMessage(Conversation $conversation, int $messageId, int $userId): bool
    {
        $message = Message::query()
            ->where('id', $messageId)
            ->where('conversation_id', $conversation->id)
            ->where('sender_id', $userId)
            ->first();

        if (! $message) {
            return false;
        }

        $message->delete();

        // Broadcast deletion event
        broadcast(new MessageDeleted($conversation->id, $messageId))->toOthers();

        return true;
    }

    /**
     * Share an item or message to a user, creating the conversation and recording the share log.
     */
    public function shareToUser(
        int $senderId,
        int $targetUserId,
        array $data,
        ?UploadedFile $imageFile = null,
        string $channel = 'app'
    ): array {
        // 1. Get or create conversation
        $conversation = $this->getOrCreatePrivateConversation($senderId, $targetUserId);

        // 2. Create the message
        $message = $this->createMessage($conversation, $senderId, $data, $imageFile);

        // 3. Record UserInteraction share log if attachable
        $type = $data['type'];
        $targetInteractionType = null;
        $targetInteractionId = null;

        if ($type === 'product' && ! empty($data['product_id'])) {
            $targetInteractionType = UserInteraction::TARGET_PRODUCT;
            $targetInteractionId = (int) $data['product_id'];
        } elseif ($type === 'drop' && ! empty($data['drop_id'])) {
            $targetInteractionType = UserInteraction::TARGET_DROP;
            $targetInteractionId = (int) $data['drop_id'];
        } elseif ($type === 'ad' && ! empty($data['ad_id'])) {
            $targetInteractionType = UserInteraction::TARGET_ADVERTISEMENT;
            $targetInteractionId = (int) $data['ad_id'];
        } elseif ($type === 'profile' && ! empty($data['profile_id'])) {
            $targetInteractionType = UserInteraction::TARGET_PROFILE;
            $targetInteractionId = (int) $data['profile_id'];
        }

        if ($targetInteractionType && $targetInteractionId) {
            UserInteraction::create([
                'user_id' => $senderId,
                'type' => UserInteraction::TYPE_SHARE,
                'target_type' => $targetInteractionType,
                'target_id' => $targetInteractionId,
                'meta' => [
                    'shared_to_user_id' => $targetUserId,
                    'channel' => $channel,
                ],
            ]);
        }

        return [
            'conversation' => $conversation,
            'message' => $message,
        ];
    }
}
