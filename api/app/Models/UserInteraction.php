<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserInteraction extends Model
{
    use HasFactory;

    public const TYPE_LIKE = 'like';
    public const TYPE_SAVE = 'save';
    public const TYPE_SHARE = 'share';
    public const TYPE_REPOST = 'repost';

    public const TARGET_ADVERTISEMENT = 'advertisement';
    public const TARGET_DROP = 'drop';
    public const TARGET_PRODUCT = 'product';
    public const TARGET_PROFILE = 'profile';
    public const TARGET_USER = 'user';

    protected $fillable = [
        'user_id',
        'type',
        'target_type',
        'target_id',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
