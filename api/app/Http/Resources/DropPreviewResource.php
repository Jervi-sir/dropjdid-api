<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DropPreviewResource extends JsonResource
{
    /**
     * Disable the default data wrapper on the resource.
     *
     * @var string|null
     */
    public static $wrap = null;

    /**
     * Transform the resource into a lightweight direct search preview array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $imageUrl = $this->mainImage?->image
            ?? $this->images?->first()?->image
            ?? '';

        if ($imageUrl && ! str_starts_with($imageUrl, 'http://') && ! str_starts_with($imageUrl, 'https://')) {
            $imageUrl = url($imageUrl);
        }

        $creatorUsername = $this->creator ? '@' . ltrim($this->creator->username, '@') : '';

        return [
            'id' => (int) $this->id,
            'title' => (string) ($this->title ?? 'Drop #' . $this->id),
            'creator' => (string) $creatorUsername,
            'image_url' => (string) $imageUrl,
        ];
    }
}
