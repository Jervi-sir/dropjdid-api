<?php

namespace App\Models;

use App\Models\Concerns\FormatsModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prize extends Model
{
    use FormatsModel, HasFactory;

    public const STATUS_DRAFT = 0;

    public const STATUS_ACTIVE = 1;

    public const STATUS_ENDED = 2;

    public const STATUS_CANCELLED = 3;

    public const STATUS = [
        self::STATUS_DRAFT => 'draft',
        self::STATUS_ACTIVE => 'active',
        self::STATUS_ENDED => 'ended',
        self::STATUS_CANCELLED => 'cancelled',
    ];

    protected $fillable = ['creator_id', 'title', 'image', 'description', 'starts_at', 'ends_at', 'status'];

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

    protected function formatterRelations(): array
    {
        return ['creator', 'joinings', 'saves'];
    }

    public function formatForApi(?User $user = null): array
    {
        $joining = $user === null
            ? null
            : $this->joinings->firstWhere('user_id', $user->id);

        $isJoined = $joining !== null || (bool) ($this->is_joined ?? false);

        return [
            'id' => $this->id,
            'title' => $this->title,
            'image' => $this->image,
            'description' => $this->description,
            'starts_at' => $this->starts_at?->toISOString(),
            'ends_at' => $this->ends_at?->toISOString(),
            'date_range' => $this->formatDateRange(),
            'status' => $this->status,
            'joinings_count' => $this->joinings_count ?? $this->joinings->count(),

            'is_joined' => $isJoined,

            'current_user_joining' => $joining === null ? null : [
                'id' => $joining->id,
                'status' => $joining->status,
                'amount_paid' => (float) $joining->amount_paid,
            ],

            'creator' => [
                'id' => $this->creator?->id,
                'name' => $this->creator?->username,
                'image' => $this->creator?->image,
            ],

            'time_left_seconds' => $this->ends_at === null
                ? null
                : max(0, now()->diffInSeconds($this->ends_at, false)),
        ];
    }

    private function formatDateRange(): ?string
    {
        if ($this->starts_at === null && $this->ends_at === null) {
            return null;
        }

        if ($this->starts_at !== null && $this->ends_at !== null) {
            return $this->starts_at->format('M j').' - '.$this->ends_at->format('M j');
        }

        return $this->starts_at?->format('M j') ?? $this->ends_at?->format('M j');
    }
}
