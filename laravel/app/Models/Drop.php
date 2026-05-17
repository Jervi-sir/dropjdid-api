<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Drop extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 0;

    public const STATUS_PUBLISHED = 1;

    public const STATUS_ENDED = 2;

    public const STATUS_CANCELLED = 3;

    public const STATUS_REJECTED = 4;

    public const STATUSES = [
        self::STATUS_DRAFT => 'draft',
        self::STATUS_PUBLISHED => 'published',
        self::STATUS_ENDED => 'ended',
        self::STATUS_CANCELLED => 'cancelled',
        self::STATUS_REJECTED => 'rejected',
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
                ->map(fn (Product $product): array => $this->formatProduct($product, $user))
                ->values()
                ->all(),
            'rejection_reason' => collect($this->rejection_reason)->first(),
            'status' => self::STATUSES[$this->status] ?? 'unknown',
            'next_page' => ($this->products_count ?? $this->products->count()) > 10 ? 2 : null,
        ];
    }

    public function formatProduct(Product $product, ?User $user): array
    {
        return [
            'type' => 'product',
            'id' => $product->id,
            'title' => $product->name,
            'price' => (float) ($product->pivot->drop_price ?? $product->show_price ?? $product->store_price ?? $product->original_price ?? 0),
            'image' => $product->images->sortBy('sort_order')->first()?->image,
            'user' => [
                'id' => $product->store?->user?->id,
                'name' => $product->store?->user?->username,
                'username' => $product->store?->user?->username,
            ],
            'nb_sales' => (int) ($product->order_items_sum_quantity ?? 0),
            'is_saved' => $user !== null && $product->relationLoaded('savedProducts') && $product->savedProducts->isNotEmpty(),
            'payment_method' => $product->relationLoaded('paymentMethod') && $product->paymentMethod ? [
                'id' => $product->paymentMethod->id,
                'code' => $product->paymentMethod->code,
                'en' => $product->paymentMethod->en,
                'fr' => $product->paymentMethod->fr,
                'ar' => $product->paymentMethod->ar,
            ] : null,
        ];
    }
}
