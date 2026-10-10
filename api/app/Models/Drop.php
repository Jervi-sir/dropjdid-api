<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Drop extends Model
{
    use HasFactory;

    // Available drop statuses
    public const STATUS_DRAFT = 'draft';
    public const STATUS_NEW = 'new';
    public const STATUS_UNDER_REVIEW = 'under-review';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_PAUSED = 'paused';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_NEW,
        self::STATUS_UNDER_REVIEW,
        self::STATUS_PUBLISHED,
        self::STATUS_REJECTED,
        self::STATUS_PAUSED,
    ];

    protected $fillable = [
        'creator_id',
        'title',
        'description',
        'drop_status',
        'rejection_reason',
    ];

    public function isDraft(): bool
    {
        return $this->drop_status === self::STATUS_DRAFT;
    }

    public function isNew(): bool
    {
        return $this->drop_status === self::STATUS_NEW;
    }

    public function isUnderReview(): bool
    {
        return $this->drop_status === self::STATUS_UNDER_REVIEW;
    }

    public function isPublished(): bool
    {
        return $this->drop_status === self::STATUS_PUBLISHED;
    }

    public function isRejected(): bool
    {
        return $this->drop_status === self::STATUS_REJECTED;
    }

    public function isPaused(): bool
    {
        return $this->drop_status === self::STATUS_PAUSED;
    }

    protected function casts(): array
    {
        return [
            'rejection_reason' => 'array',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(DropImage::class)->orderBy('sort_order', 'asc');
    }

    public function mainImage(): HasOne
    {
        return $this->hasOne(DropImage::class)->where('is_main', true);
    }

    public function interactions(): HasMany
    {
        return $this->hasMany(UserInteraction::class, 'target_id')->where('target_type', UserInteraction::TARGET_DROP);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(UserInteraction::class, 'target_id')
            ->where('target_type', UserInteraction::TARGET_DROP)
            ->where('type', UserInteraction::TYPE_LIKE);
    }

    public function likedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_interactions', 'target_id', 'user_id')
            ->wherePivot('target_type', '=', UserInteraction::TARGET_DROP)
            ->wherePivot('type', '=', UserInteraction::TYPE_LIKE)
            ->withPivot(['target_type', 'type'])
            ->withTimestamps();
    }

    public function saves(): HasMany
    {
        return $this->hasMany(UserInteraction::class, 'target_id')
            ->where('target_type', UserInteraction::TARGET_DROP)
            ->where('type', UserInteraction::TYPE_SAVE);
    }

    public function savedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_interactions', 'target_id', 'user_id')
            ->wherePivot('target_type', '=', UserInteraction::TARGET_DROP)
            ->wherePivot('type', '=', UserInteraction::TYPE_SAVE)
            ->withPivot(['target_type', 'type'])
            ->withTimestamps();
    }

    public function dropProducts(): HasMany
    {
        return $this->hasMany(DropProduct::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'drop_products', 'drop_id', 'product_id')
            ->withPivot('drop_price')
            ->withTimestamps();
    }

    public function histories(): HasMany
    {
        return $this->hasMany(DropHistory::class)->latest('id');
    }

    /**
     * Format drop into the standard drop feed array structure.
     *
     * @return array<string, mixed>
     */
    public function toFeedArray(): array
    {
        return (new \App\Http\Resources\DropFeedResource($this))->resolve();
    }

    /**
     * Format drop into direct search preview array structure.
     *
     * @return array<string, mixed>
     */
    public function toPreviewArray(): array
    {
        return (new \App\Http\Resources\DropPreviewResource($this))->resolve();
    }
}
