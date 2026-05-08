<?php

namespace App\Http\Controllers\Api\Products;

use App\Http\Controllers\Controller;
use App\Models\Drop;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DropsController extends Controller
{
    public function index(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $page = $validated['page'] ?? 1;
        $perPage = $validated['per_page'] ?? 10;
        $user = $request->user();
        $userId = $user?->getAuthIdentifier();

        return $this->paginatedDropsResponse($product, $userId, $user, $page, $perPage);
    }

    private function paginatedDropsResponse(Product $product, ?int $userId, $user, int $page, int $perPage): JsonResponse
    {
        $baseQuery = $this->baseDropsQuery($product);

        $drops = (clone $baseQuery)
            ->withCount('likedDrops')
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
            ->simplePaginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => collect($drops->items())
                ->map(fn (Drop $drop): array => $drop->formatDrop($user))
                ->values()
                ->all(),
            'count' => (clone $baseQuery)->count(),
            'next_page' => $drops->hasMorePages() ? $drops->currentPage() + 1 : null,
        ]);
    }

    private function baseDropsQuery(Product $product)
    {
        return Drop::query()
            ->where('status', 'published')
            ->whereHas('creator')
            ->whereHas('products', fn ($query) => $query->where('products.id', $product->id));
    }
}
