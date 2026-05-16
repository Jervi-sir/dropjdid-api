<?php

namespace App\Models;

use App\Models\Concerns\FormatsModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Friendship extends Model
{
    use FormatsModel, HasFactory;

    public const STATUS_PENDING = 0;

    public const STATUS_ACCEPTED = 1;

    public const STATUS_REJECTED = 2;

    public const STATUS_BLOCKED = 3;

    public const STATUSES = [
        self::STATUS_PENDING => 'pending',
        self::STATUS_ACCEPTED => 'accepted',
        self::STATUS_REJECTED => 'rejected',
        self::STATUS_BLOCKED => 'blacked',
    ];

    protected $fillable = ['sender_id', 'receiver_id', 'status', 'accepted_at', 'rejected_at', 'blocked_at'];

    protected function casts(): array
    {
        return [
            'status' => 'integer',
            'accepted_at' => 'datetime',
            'rejected_at' => 'datetime',
            'blocked_at' => 'datetime',
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    protected function formatterRelations(): array
    {
        return ['sender', 'receiver'];
    }
}
