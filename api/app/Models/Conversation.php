<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conversation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type',
        'first_user_id',
        'second_user_id',
        'first_user_last_read_at',
        'second_user_last_read_at',
        'first_user_deleted_at',
        'second_user_deleted_at',
    ];

    protected function casts(): array
    {
        return [
            'first_user_last_read_at' => 'datetime',
            'second_user_last_read_at' => 'datetime',
            'first_user_deleted_at' => 'datetime',
            'second_user_deleted_at' => 'datetime',
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
        return $this->hasMany(Message::class)->latest();
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function otherUser(int $currentUserId): ?User
    {
        return $this->first_user_id === $currentUserId
            ? $this->secondUser
            : $this->firstUser;
    }

    public function toConversationType(int $currentUserId): array
    {
        $other = $this->otherUser($currentUserId);
        $lastMsg = $this->latestMessage ?? $this->messages()->first();

        $imageUrl = $other?->image_url ?? '';
        if ($imageUrl && ! str_starts_with($imageUrl, 'http://') && ! str_starts_with($imageUrl, 'https://')) {
            $imageUrl = url($imageUrl);
        }

        $text1 = $other
            ? ($other->full_name ?? $other->name ?? $other->username ?? 'User #'.$other->id)
            : 'Support';

        $text2 = '';
        if ($lastMsg) {
            if ($lastMsg->type === 'text') {
                $text2 = (string) $lastMsg->body;
            } elseif ($lastMsg->type === 'image') {
                $text2 = 'Sent an image';
            } elseif ($lastMsg->type === 'product') {
                $text2 = 'Shared a product';
            } elseif ($lastMsg->type === 'drop') {
                $text2 = 'Shared a drop';
            } elseif ($lastMsg->type === 'ad') {
                $text2 = 'Shared an ad';
            } elseif ($lastMsg->type === 'profile') {
                $text2 = 'Shared a profile';
            }
        }

        $hasUnread = false;
        $userLastReadAt = $this->first_user_id === $currentUserId
            ? $this->first_user_last_read_at
            : $this->second_user_last_read_at;

        if ($lastMsg && $lastMsg->sender_id !== $currentUserId) {
            $hasUnread = $userLastReadAt ? $lastMsg->created_at->gt($userLastReadAt) : true;
        }

        return [
            'id' => (int) $this->id,
            'user_id' => $other?->id ? (int) $other->id : null,
            'image_url' => (string) $imageUrl,
            'text1' => (string) $text1,
            'text2' => (string) $text2,
            'has_unread_messages' => (bool) $hasUnread,
            'was_reset' => (bool) ($this->was_reset ?? false),
        ];
    }
}
