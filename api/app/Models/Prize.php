<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prize extends Model
{
    use HasFactory;

    protected $fillable = [
        'creator_id',
        'title',
        'image',
        'description',
        'starts_at',
        'ends_at',
        'prize_status',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function joinings(): HasMany
    {
        return $this->hasMany(PrizeJoining::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'prize_joinings', 'prize_id', 'user_id')->withTimestamps();
    }
}
