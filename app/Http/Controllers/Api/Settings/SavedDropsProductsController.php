<?php

namespace App\Http\Controllers\Api\Settings;

use App\Http\Controllers\Controller;
use App\Models\Drop;
use App\Models\SavedDrop;
use App\Models\SavedProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SavedDropsProductsController extends Controller
{
    public function products(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_if($user === null, 401);

        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $perPage = $validated['per_page'] ?? 10;

        $savedProducts = SavedProduct::query()
            ->where('user_id', $user->id)
            ->with([
                'product.images',
                'product.store.user',
                'product.savedProducts' => fn ($query) => $query->where('user_id', $user->id),
            ])
            ->latest('id')
            ->simplePaginate($perPage);

        return response()->json([
            'data' => collect($savedProducts->items())
                ->map(fn (SavedProduct $savedProduct): ?array => $savedProduct->product?->formatProduct($savedProduct->product, $user))
                ->filter()
                ->values()
                ->all(),
            'next_page' => $savedProducts->hasMorePages() ? $savedProducts->currentPage() + 1 : null,
        ]);
    }

    public function drops(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_if($user === null, 401);

        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $perPage = $validated['per_page'] ?? 10;

        $savedDrops = SavedDrop::query()
            ->where('user_id', $user->id)
            ->with([
                'drop' => function ($query) use ($user): void {
                    $userId = $user->id;

                    $query
                        ->withCount('likedDrops')
                        ->with([
                            'creator',
                            'images',
                            'products.store.user',
                            'products.images',
                            'products.savedProducts' => fn ($savedProductsQuery) => $savedProductsQuery->where('user_id', $userId),
                            'likedDrops' => fn ($likedDropsQuery) => $likedDropsQuery->where('user_id', $userId),
                            'savedDrops' => fn ($savedDropsQuery) => $savedDropsQuery->where('user_id', $userId),
                        ]);
                },
            ])
            ->latest('id')
            ->simplePaginate($perPage);

        $drops = collect($savedDrops->items())->pluck('drop')->filter();

        return response()->json([
            'data' => $drops
                ->map(fn (Drop $drop): array => $drop->formatDrop($user))
                ->values()
                ->all(),
            'next_page' => $savedDrops->hasMorePages() ? $savedDrops->currentPage() + 1 : null,
        ]);
    }
}
