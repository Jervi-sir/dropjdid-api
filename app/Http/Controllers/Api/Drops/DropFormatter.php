<?php

namespace App\Http\Controllers\Api\Drops;

use App\Models\Drop;
use App\Models\Product;
use App\Models\User;

class DropFormatter
{
    public function formatDrop(Drop $drop, ?User $user): array
    {
        return [
            'type' => 'drop',
            'id' => $drop->id,
            'title' => $drop->title,
            'images' => $drop->images->pluck('image')->values()->all(),
            'creator' => [
                'id' => $drop->creator?->id,
                'name' => $drop->creator?->username,
            ],
            'nb_likes' => $drop->liked_drops_count,
            'is_liked' => $user !== null && $drop->likedDrops->isNotEmpty(),
            'is_saved' => $user !== null && $drop->savedDrops->isNotEmpty(),
            'products' => $drop->products
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
            ],
            'is_saved' => $user !== null && $product->relationLoaded('savedProducts') && $product->savedProducts->isNotEmpty(),
        ];
    }
}
