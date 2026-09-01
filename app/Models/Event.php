<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'image_url',
        'url',
        'status',
        'sort_order',
        'starts_at',
        'ends_at',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'sort_order' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    /**
     * User who created or owns the event.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope query to active events currently within their active date range.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            });
    }

    /**
     * Scope query to upcoming events starting in the future.
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->whereNotNull('starts_at')
            ->where('starts_at', '>', now());
    }

    /**
     * Scope query to past/completed events.
     */
    public function scopePast(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('status', 'completed')
                ->orWhere(function ($sub) {
                    $sub->whereNotNull('ends_at')
                        ->where('ends_at', '<', now());
                });
        });
    }

    /**
     * Helper to get full absolute image url.
     */
    public function getFormattedImageUrlAttribute(): string
    {
        $img = $this->image_url;

        if (! $img) {
            return '';
        }

        if (! str_starts_with($img, 'http://') && ! str_starts_with($img, 'https://')) {
            return url($img);
        }

        return (string) $img;
    }

    /**
     * Format event into standard API schema with image_url, text1, text2.
     *
     * @param int|null $userId
     * @return array<string, mixed>
     */
    public function toEventType(?int $userId = null): array
    {
        return [
            'id' => (int) $this->id,
            'image_url' => $this->formatted_image_url,
            'text1' => (string) ($this->title ?? 'Event #' . $this->id),
            'text2' => (string) ($this->description ?? ''),
            'url' => (string) ($this->url ?? ''),
            'user_id' => $this->user_id ? (int) $this->user_id : null,
            'status' => (string) ($this->status ?? 'active'),
            'starts_at' => $this->starts_at?->toISOString(),
            'ends_at' => $this->ends_at?->toISOString(),
            'meta' => $this->meta ?? [],
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    /**
     * Format event specifically for feed cards (image_url, text1, text2, url).
     *
     * @return array<string, mixed>
     */
    public function toFeedEvent(): array
    {
        return [
            'id' => (int) $this->id,
            'image_url' => $this->formatted_image_url,
            'text1' => (string) ($this->title ?? 'Special Event'),
            'text2' => (string) ($this->description ?? ''),
            'url' => (string) ($this->url ?? ''),
        ];
    }
}
