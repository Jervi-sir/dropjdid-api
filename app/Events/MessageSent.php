<?php

namespace App\Events;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Message $message;
    public int $conversationId;
    public ?int $recipientId;

    /**
     * Create a new event instance.
     */
    public function __construct(Message $message, ?Conversation $conversation = null)
    {
        $this->message = $message;
        $this->conversationId = (int) $message->conversation_id;

        $conv = $conversation ?? $message->conversation;
        $this->recipientId = $conv ? ($conv->otherUser((int) $message->sender_id)?->id) : null;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PresenceChannel('conversation.' . $this->conversationId),
        ];

        if ($this->recipientId) {
            $channels[] = new PrivateChannel('user.' . $this->recipientId);
        }

        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    /**
     * Data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->conversationId,
            'message' => $this->message->toMessageType(),
        ];
    }
}
