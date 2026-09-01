<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrizeJoining extends Model
{
    use HasFactory;

    protected $fillable = [
        'prize_id',
        'user_id',
        'phone_number',
        'status',
    ];

    public function prize(): BelongsTo
    {
        return $this->belongsTo(Prize::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
