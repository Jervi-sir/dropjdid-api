<?php

namespace App\Models;

use App\Models\Concerns\FormatsModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Message extends Model
{
    use FormatsModel, HasFactory;

    public const TYPE_TEXT = 0;

    public const TYPE_PRODUCT = 1;

    public const TYPE_PROFILE = 2;

    public const TYPE_IMAGE = 3;

    public const TYPE = [
        self::TYPE_TEXT => 'text',
        self::TYPE_PRODUCT => 'product',
        self::TYPE_PROFILE => 'profile',
        self::TYPE_IMAGE => 'image',
    ];

    protected $fillable = ['conversation_id', 'sender_id', 'type', 'body', 'attachable_type', 'attachable_id'];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    protected function formatterRelations(): array
    {
        return ['conversation', 'sender', 'attachable'];
    }

    public function formatForConversation(User $user): array
    {
        $type = $this->type;
        $body = $this->body;

        if ($type === 'text' && $body === null && $this->attachable instanceof Drop) {
            $body = 'Shared drop';
        }

        return [
            'id' => $this->id,
            'type' => $type,
            'message' => $body,
            'isMine' => $this->sender_id === $user->id,
            'created_at' => $this->created_at?->toISOString(),
            'sender' => [
                'id' => $this->sender?->id,
                'name' => $this->sender?->username,
                'username' => $this->sender?->username,
                'image' => $this->sender?->image,
            ],
            'image' => $type === 'image' ? [
                'url' => $this->body,
            ] : null,
            'product' => $type === 'product' && $this->attachable instanceof Product ? [
                'id' => $this->attachable->id,
                'name' => $this->attachable->name,
            ] : null,
            'profile' => $type === 'profile' && $this->attachable instanceof User ? [
                'id' => $this->attachable->id,
                'name' => $this->attachable->username,
                'username' => '@'.$this->attachable->username,
                'image' => $this->attachable->image,
            ] : null,
        ];
    }
}
