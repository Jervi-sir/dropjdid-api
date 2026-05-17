<?php

namespace App\Models;

use App\Models\Concerns\FormatsModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use FormatsModel, HasFactory, SoftDeletes;

    public const STATUS_DRAFT = 0;

    public const STATUS_PUBLISHED = 1;

    public const STATUS_ARCHIVED = 2;

    public const STATUS_REJECTED = 3;

    public const STATUSES = [
        self::STATUS_DRAFT => 'draft',
        self::STATUS_PUBLISHED => 'published',
        self::STATUS_ARCHIVED => 'archived',
        self::STATUS_REJECTED => 'rejected',
    ];

    protected $fillable = ['store_id', 'category_id', 'quality_id', 'name', 'description', 'payment_method_id', 'original_price', 'show_price', 'store_price', 'gender_id', 'status', 'refreshed_at', 'rejection_reason'];

    protected function casts(): array
    {
        return [
            'original_price' => 'decimal:2',
            'show_price' => 'decimal:2',
            'store_price' => 'decimal:2',
            'status' => 'integer',
            'deleted_at' => 'datetime',
            'refreshed_at' => 'datetime',
            'rejection_reason' => 'array',
        ];
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

    public function getStatusTextAttribute(): string
    {
        return self::STATUSES[$this->status] ?? 'unknown';
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function quality(): BelongsTo
    {
        return $this->belongsTo(Quality::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function gender(): BelongsTo
    {
        return $this->belongsTo(Gender::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function productKeywords(): HasMany
    {
        return $this->hasMany(ProductKeyword::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function keywords(): BelongsToMany
    {
        return $this->belongsToMany(Keyword::class, 'product_keywords')->withTimestamps();
    }

    public function drops(): BelongsToMany
    {
        return $this->belongsToMany(Drop::class, 'drop_product')
            ->withPivot('drop_price')
            ->withTimestamps();
    }

    public function savedProducts(): HasMany
    {
        return $this->hasMany(SavedProduct::class);
    }

    public function likedProducts(): HasMany
    {
        return $this->hasMany(LikedProduct::class);
    }

    /**
     * --------------------------------------------------------------------------
     * Formatter
     * --------------------------------------------------------------------------
     */
    public function formatProduct(Product $product, ?User $user): array
    {
        return [
            'type' => 'product',
            'id' => $product->id,
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
            'rejection_reason' => collect($this->rejection_reason)->first(),
            'status' => self::STATUSES[$this->status] ?? 'unknown',

        ];
    }
}
