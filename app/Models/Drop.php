<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Drop extends Model
{
    use HasFactory;

    protected $fillable = ['creator_id', 'title', 'description', 'starts_at', 'ends_at', 'status'];

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
            'is_liked' => $user !== null && $this->likedDrops->isNotEmpty(),
            'is_saved' => $user !== null && $this->savedDrops->isNotEmpty(),
            'products_count' => $this->products_count ?? $this->products->count(),
            'products' => $this->products
                ->take(10)
                ->map(fn (Product $product): array => $this->formatProduct($product, $user))
                ->values()
                ->all(),
        ];
    }

    public function formatProduct(Product $product, ?User $user): array
    {
        return [
            'id' => $product->id,
            'price' => (float) ($product->pivot->drop_price ?? $product->show_price ?? $product->store_price ?? $product->original_price ?? 0),
            'image' => $product->images->sortBy('sort_order')->first()?->image,
            'user' => [
                'id' => $product->store?->user?->id,
                'name' => $product->store?->user?->username,
                'username' => $product->store?->user?->username,
            ],
            'is_saved' => $user !== null && $product->relationLoaded('savedProducts') && $product->savedProducts->isNotEmpty(),
        ];
    }
}
