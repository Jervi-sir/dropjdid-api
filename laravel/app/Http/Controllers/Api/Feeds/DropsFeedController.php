<?php

namespace App\Http\Controllers\Api\Feeds;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\Drop;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DropsFeedController extends Controller
{
    public function list(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'ads_count' => ['nullable', 'integer', 'min:1', 'max:20'],
            'filter' => ['nullable', 'string', 'in:for_you,creators_i_follow,trending'],
        ]);

        $perPage = $validated['per_page'] ?? 4;
        $adsCount = $validated['ads_count'] ?? 4;
        $user = $request->user();
        $userId = $user?->getAuthIdentifier();

        $drops = Drop::query()
            ->where('status', Drop::STATUS_PUBLISHED)
            ->whereHas('creator')
            ->withCount(['likedDrops', 'products', 'savedDrops'])
            ->with([
                'creator',
                'images',
                'products.store.user',
                'products.images',
                'products' => function ($query) use ($userId): void {
                    if ($userId !== null) {
                        $query->with([
                            'savedProducts' => fn ($saveQuery) => $saveQuery->where('user_id', $userId),
                        ]);
                    }
                },
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
            ])
            ->latest()
            ->simplePaginate($perPage);

        $formattedDrops = collect($drops->items())
            ->map(fn (Drop $drop): array => $drop->formatDrop($user));

        $data = Advertisement::injectIntoFeed($formattedDrops, adsCount: $adsCount)->values();

        return response()->json([
            'data' => $data,
            'next_page' => $drops->hasMorePages() ? $drops->currentPage() + 1 : null,
        ]);
    }

    public function products(Request $request, Drop $drop): JsonResponse
    {
        $validated = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'ads_count' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $perPage = $validated['per_page'] ?? 10;
        $adsCount = $validated['ads_count'] ?? 4;
        $user = $request->user();
        $userId = $user?->getAuthIdentifier();

        $products = $drop->products()
            ->with([
                'store.user',
                'images',
            ])
            ->when($userId, function ($query) use ($userId) {
                $query->with([
                    'savedProducts' => fn ($saveQuery) => $saveQuery->where('user_id', $userId),
                ]);
            })
            ->simplePaginate($perPage);

        $formattedProducts = collect($products->items())
            ->map(fn (Product $product): array => $drop->formatProduct($product, $user));

        $data = Advertisement::injectIntoFeed($formattedProducts, adsCount: $adsCount)->values();

        return response()->json([
            'data' => $data,
            'next_page' => $products->hasMorePages() ? $products->currentPage() + 1 : null,
        ]);
    }
}
