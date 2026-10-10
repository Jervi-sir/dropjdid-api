<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DropFeedResource extends JsonResource
{
    /**
     * Disable the default data wrapper on the resource.
     *
     * @var string|null
     */
    public static $wrap = null;

    /**
     * Transform the resource into an array matching frontend DropFeedType / Drop1Card.
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

        $dropName = $this->title ?: ('#' . $this->id);
        $text1 = 'drop:' . $dropName;
        $text2 = $this->creator ? '@' . ltrim($this->creator->username, '@') : ($this->description ?? '');

        return [
            'id' => (int) $this->id,
            'image_url' => (string) $imageUrl,
            'text1' => (string) $text1,
            'text2' => (string) $text2,
        ];
    }
}
