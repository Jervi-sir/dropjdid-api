<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Models\Drop;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CreatorDropsController extends Controller
{
    public function __invoke(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $perPage = $validated['per_page'] ?? 10;
        $authUser = $request->user();
        $userId = $authUser?->getAuthIdentifier();

        $drops = Drop::query()
            ->where('creator_id', $user->id)
            ->where('status', 'published')
            ->withCount('likedDrops')
            ->with([
                'creator',
                'images',
                'likedDrops' => function ($query) use ($userId) {
                    return $userId === null
                        ? $query->whereRaw('1 = 0')
                        : $query->where('user_id', $userId);
                },
                'savedDrops' => function ($query) use ($userId) {
                    return $userId === null
                        ? $query->whereRaw('1 = 0')
                        : $query->where('user_id', $userId);
                },
                'products.store.user',
                'products.images',
                'products' => function ($query) use ($userId): void {
                    if ($userId !== null) {
                        $query->with([
                            'savedProducts' => fn ($savedProductsQuery) => $savedProductsQuery->where('user_id', $userId),
                        ]);
                    }
                },
            ])
            ->latest()
            ->simplePaginate($perPage);

        return response()->json([
            'data' => $drops->getCollection()->map(fn (Drop $drop): array => $this->formatDrop($drop, $authUser))->values(),
            'next_page' => $drops->hasMorePages() ? $drops->currentPage() + 1 : null,
        ]);
    }

    private function formatDrop(Drop $drop, ?User $user): array
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
            'products' => $drop->products->map(fn (Product $product): array => [
                'id' => $product->id,
                'price' => (float) ($product->pivot->drop_price ?? $product->show_price ?? $product->store_price ?? $product->original_price ?? 0),
                'image' => $product->images->sortBy('sort_order')->first()?->image,
                'user' => [
                    'id' => $product->store?->user?->id,
                    'name' => $product->store?->user?->username,
                ],
                'is_saved' => $user !== null && $product->relationLoaded('savedProducts') && $product->savedProducts->isNotEmpty(),
            ])->values()->all(),
        ];
    }
}
