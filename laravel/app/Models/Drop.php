<?php

namespace App\Models;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Drop extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 0;

    public const STATUS_PUBLISHED = 1;

    public const STATUS_ENDED = 2;

    public const STATUS_CANCELLED = 3;

    public const STATUS_REJECTED = 4;

    public const STATUS_PENDING = 5;

    public const STATUSES = [
        self::STATUS_DRAFT => 'draft',
        self::STATUS_PUBLISHED => 'published',
        self::STATUS_ENDED => 'ended',
        self::STATUS_CANCELLED => 'cancelled',
        self::STATUS_REJECTED => 'rejected',
        self::STATUS_PENDING => 'pending',
    ];

    protected $fillable = ['creator_id', 'title', 'description', 'starts_at', 'ends_at', 'status', 'rejection_reason'];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'rejection_reason' => 'array',
        ];
    }

    protected function status(): Attribute
    {
        return Attribute::make(
            set: function ($value) {
                if (is_string($value) && ! is_numeric($value)) {
                    $statusMap = array_flip(self::STATUSES);

                    return $statusMap[$value] ?? self::STATUS_DRAFT;
                }

                return (int) $value;
            }
        );
    }

    public function addRejectionReason(string $en, string $fr, string $ar): void
    {
        $reasons = $this->rejection_reason ?? [];

        array_unshift($reasons, [
            'id' => count($reasons) + 1,
            'en' => $en,
            'fr' => $fr,
            'ar' => $ar,
        ]);

        $this->rejection_reason = $reasons;
        $this->save();
    }

    public function orderItem(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'drop_product')
            ->withPivot('drop_price')
            ->withTimestamps();
    }

    public function images(): HasMany
    {
        return $this->hasMany(DropImage::class)->orderBy('sort_order');
    }

    public function savedDrops(): HasMany
    {
        return $this->hasMany(SavedDrop::class);
    }

    public function likedDrops(): HasMany
    {
        return $this->hasMany(LikedDrop::class);
    }

    /**
     * --------------------------------------------------------------------------
     * Formatters
     * --------------------------------------------------------------------------
     */
    public function formatDrop(?User $user): array
    {
        return [
            'type' => 'drop',
            'id' => $this->id,
            'title' => $this->title,
            'images' => $this->images->pluck('image')->values()->all(),
            'creator' => [
                'id' => $this->creator?->id,
                'name' => $this->creator?->username,
                'username' => $this->creator?->username,
            ],
            'nb_likes' => $this->liked_drops_count,
            'nb_saves' => $this->saved_drops_count,
            'is_liked' => $user !== null && $this->likedDrops->isNotEmpty(),
            'is_saved' => $user !== null && $this->savedDrops->isNotEmpty(),
            'products_count' => $this->products_count ?? $this->products->count(),
            'products' => $this->products
                ->take(10)
                ->map(fn (Product $product): array => [
                    'type' => 'product',
                    'id' => $product->id,
                    'title' => '@'.$this->creator?->username,
                    'price' => (float) ($product->pivot->drop_price ?? $product->show_price ?? $product->store_price ?? $product->original_price ?? 0),
                    'image' => $product->images->sortBy('sort_order')->first()?->image,
                    'is_saved' => $user !== null && $product->relationLoaded('savedProducts') && $product->savedProducts->isNotEmpty(),
                ])
                ->values()
                ->all(),
            'rejection_reason' => collect($this->rejection_reason)->first(),
            'status' => self::STATUSES[$this->status] ?? 'unknown',
            'next_page' => ($this->products_count ?? $this->products->count()) > 10 ? 2 : null,
        ];
    }

    /**
     * Eager load standard feed relations for a collection or paginator of drops.
     * Consolidates user loading to prevent duplicate queries.
     */
    public static function loadFeedRelations(mixed $drops, ?int $userId, ?callable $productCallback = null): void
    {
        $items = match (true) {
            $drops instanceof Paginator => \Illuminate\Database\Eloquent\Collection::make($drops->items()),
            $drops instanceof \Illuminate\Database\Eloquent\Collection => $drops,
            $drops instanceof Collection => \Illuminate\Database\Eloquent\Collection::make($drops->all()),
            $drops instanceof Drop => \Illuminate\Database\Eloquent\Collection::make([$drops]),
            default => \Illuminate\Database\Eloquent\Collection::make($drops),
        };

        if ($items->isEmpty()) {
            return;
        }

        $items->load([
            'images',
            'products.store',
            'products.images',
            'products' => function ($query) use ($userId, $productCallback): void {
                if ($userId !== null) {
                    $query->with([
                        'savedProducts' => fn ($saveQuery) => $saveQuery->where('user_id', $userId),
                    ]);
                }
                if ($productCallback !== null) {
                    $productCallback($query);
                }
            },
            'likedDrops' => function ($query) use ($userId): void {
                if ($userId === null) {
                    $query->whereRaw('1 = 0');
                } else {
                    $query->where('user_id', $userId);
                }
            },
            'savedDrops' => function ($query) use ($userId): void {
                if ($userId === null) {
                    $query->whereRaw('1 = 0');
                } else {
                    $query->where('user_id', $userId);
                }
            },
        ]);

        $userIds = [];
        foreach ($items as $drop) {
            if ($drop->creator_id) {
                $userIds[] = $drop->creator_id;
            }
            foreach ($drop->products as $product) {
                if ($product->store && $product->store->user_id) {
                    $userIds[] = $product->store->user_id;
                }
            }
        }

        $uniqueUserIds = array_unique(array_filter($userIds));

        if (! empty($uniqueUserIds)) {
            $users = User::query()->whereIn('id', $uniqueUserIds)->get()->keyBy('id');

            foreach ($items as $drop) {
                $drop->setRelation('creator', $users->get($drop->creator_id));
                foreach ($drop->products as $product) {
                    if ($product->store) {
                        $product->store->setRelation('user', $users->get($product->store->user_id));
                    }
                }
            }
        } else {
            foreach ($items as $drop) {
                $drop->setRelation('creator', null);
                foreach ($drop->products as $product) {
                    if ($product->store) {
                        $product->store->setRelation('user', null);
                    }
                }
            }
        }
    }
}
