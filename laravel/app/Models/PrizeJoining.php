<?php

namespace App\Models;

use App\Models\Concerns\FormatsModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrizeJoining extends Model
{
    use FormatsModel, HasFactory;

    public const STATUS_PENDING = 0;

    public const STATUS_JOINED = 1;

    public const STATUS_CANCELLED = 2;

    public const STATUS_REFUNDED = 3;

    public const STATUS_WINNER = 4;

    public const STATUS_LOST = 5;

    public const STATUS = [
        self::STATUS_PENDING => 'pending',
        self::STATUS_JOINED => 'joined',
        self::STATUS_CANCELLED => 'cancelled',
        self::STATUS_REFUNDED => 'refunded',
        self::STATUS_WINNER => 'winner',
        self::STATUS_LOST => 'lost',
    ];

    protected $fillable = ['prize_id', 'user_id', 'status'];

    public function prize(): BelongsTo
    {
        return $this->belongsTo(Prize::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function formatterRelations(): array
    {
        return ['prize', 'user'];
    }
}
