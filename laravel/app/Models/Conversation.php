<?php

namespace App\Models;

use App\Models\Concerns\FormatsModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conversation extends Model
{
    use FormatsModel, HasFactory, SoftDeletes;

    public const TYPE_PRIVATE = 0;

    public const TYPE_SUPPORT = 1;

    public const TYPE = [
        self::TYPE_PRIVATE => 'private',
        self::TYPE_SUPPORT => 'support',
    ];

    protected $fillable = ['type', 'first_user_id', 'second_user_id', 'first_user_last_read_at', 'second_user_last_read_at'];

    protected function casts(): array
    {
        return [
            'first_user_last_read_at' => 'datetime',
            'second_user_last_read_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function firstUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'first_user_id');
    }

    public function secondUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'second_user_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    protected function formatterRelations(): array
    {
        return ['firstUser', 'secondUser', 'messages'];
    }

    public function formatForList(User $user): array
    {
        $isFirstUser = $this->first_user_id === $user->id;
        $otherUser = $isFirstUser ? $this->secondUser : $this->firstUser;
        $latestMessage = $this->messages->sortByDesc('created_at')->first();
        $lastReadAt = $isFirstUser ? $this->first_user_last_read_at : $this->second_user_last_read_at;

        return [
            'id' => $this->id,
            'type' => $this->type,
            'user' => [
                'id' => $otherUser?->id,
                'name' => $otherUser?->username,
                'username' => $otherUser?->username,
                'image' => $otherUser?->image,
            ],
            'latest_message' => [
                'id' => $latestMessage?->id,
                'type' => $latestMessage?->type,
                'body' => $latestMessage?->body,
                'created_at' => $latestMessage?->created_at?->toISOString(),
            ],
            'has_unread' => $latestMessage !== null
                && $lastReadAt !== null
                ? $latestMessage->created_at?->gt($lastReadAt) ?? false
                : $latestMessage !== null,
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
