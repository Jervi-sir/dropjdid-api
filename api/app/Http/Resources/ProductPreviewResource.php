<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductPreviewResource extends JsonResource
{
    /**
     * Disable the default data wrapper on the resource.
     *
     * @var string|null
     */
    public static $wrap = null;

    protected ?float $overridePrice = null;

    /**
     * Pass an optional price override.
     */
    public function withPriceOverride(?float $price): static
    {
        $this->overridePrice = $price;
        return $this;
    }

    /**
     * Transform the resource into a lightweight direct preview array.
     * Used in Search suggestions & direct entity results.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $imageUrl = $this->mainImage?->image_url
            ?? $this->images?->first()?->image_url
            ?? '';

        if ($imageUrl && ! str_starts_with($imageUrl, 'http://') && ! str_starts_with($imageUrl, 'https://')) {
            $imageUrl = url($imageUrl);
        }

        $price = $this->overridePrice ?? $this->price_shown ?? $this->price_original ?? 0;
        $formattedPrice = number_format((float) $price, 0, '.', ' ') . ' DZD';

        return [
            'id' => (int) $this->id,
            'title' => (string) ($this->name ?? 'Product #' . $this->id),
            'price' => (string) $formattedPrice,
            'store_name' => (string) ($this->store?->name ?? 'Store'),
            'image_url' => (string) $imageUrl,
        ];
    }
}
