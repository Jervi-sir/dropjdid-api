<?php

namespace App\Http\Resources;

use App\Models\UserInteraction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Disable the default data wrapper on the resource.
     *
     * @var string|null
     */
    public static $wrap = null;

    protected ?float $overridePrice = null;
    protected ?int $userId = null;

    /**
     * Pass an optional price override (e.g. drop pivot price).
     */
    public function withPriceOverride(?float $price): static
    {
        $this->overridePrice = $price;
        return $this;
    }

    /**
     * Pass an explicit user ID.
     */
    public function withUserId(?int $userId): static
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * Transform the resource into an array matching frontend ProductType.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $userId = $this->userId ?? $request->user('sanctum')?->id ?? $request->user()?->id ?? $request->input('user_id');

        $imageUrl = $this->mainImage?->image_url
            ?? $this->images?->first()?->image_url
            ?? '';

        if ($imageUrl && ! str_starts_with($imageUrl, 'http://') && ! str_starts_with($imageUrl, 'https://')) {
            $imageUrl = url($imageUrl);
        }

        $currentPrice = $this->overridePrice ?? $this->price_shown ?? $this->price_original ?? 0;
        $originalPrice = $this->price_original ?? $currentPrice;

        $promoPercentage = '';
        if ($originalPrice > 0 && $currentPrice < $originalPrice) {
            $discount = round((($originalPrice - $currentPrice) / $originalPrice) * 100);
            $promoPercentage = "-{$discount}%";
        }

        $isSaved = false;
        if ($userId) {
            if ($this->relationLoaded('savedUsers')) {
                $isSaved = $this->savedUsers->contains('id', $userId);
            } else {
                $isSaved = UserInteraction::where('user_id', $userId)
                    ->where('target_type', UserInteraction::TARGET_PRODUCT)
                    ->where('target_id', $this->id)
                    ->where('type', UserInteraction::TYPE_SAVE)
                    ->exists();
            }
        }

        return [
            'id' => (int) $this->id,
            'image_url' => (string) $imageUrl,
            'prices' => [
                'price1' => $currentPrice !== null ? number_format((float) $currentPrice, 0, '.', ' ') . ' DZD' : '',
                'price2' => ($originalPrice > $currentPrice) ? number_format((float) $originalPrice, 0, '.', ' ') : '',
                'promo_percentage' => (string) $promoPercentage,
            ],
            'text' => (string) ($this->classification?->en
                ?: $this->description
                ?: ($this->name ?? 'Product #' . $this->id)),
            'save' => [
                'is_saved' => (bool) $isSaved,
                'nb_save' => (int) ($this->saved_users_count ?? 0),
            ],
            'is_saved' => (bool) $isSaved,
            'nb_saved' => (int) ($this->saved_users_count ?? 0),
        ];
    }
}
