<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreatorRequest extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 0;

    public const STATUS_APPROVED = 1;

    public const STATUS_REJECTED = 2;

    public const STATUSES = [
        self::STATUS_PENDING => 'pending',
        self::STATUS_APPROVED => 'approved',
        self::STATUS_REJECTED => 'rejected',
    ];

    protected $fillable = ['user_id', 'phone_number', 'status', 'note', 'reviewed_at'];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
