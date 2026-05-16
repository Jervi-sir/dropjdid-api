<?php

namespace App\Http\Controllers\Api\Creators\Drops;

use App\Http\Controllers\Controller;
use App\Models\Drop;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MyDropsController extends Controller
{
    public function listDrops(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $perPage = $validated['per_page'] ?? 10;
        $user = $request->user();
        $userId = $user->id;

        $drops = Drop::query()
            ->where('creator_id', $userId)
            ->withCount('likedDrops')
            ->withCount('savedDrops')
            ->with([
                'creator',
                'images',
                'products.store.user',
                'products.images',
                'products' => function ($query) use ($userId): void {
                    $query->withSum('orderItems', 'quantity')->with([
                        'savedProducts' => fn ($saveQuery) => $saveQuery->where('user_id', $userId),
                    ]);
                },
                'likedDrops' => function ($query) use ($userId) {
                    return $query->where('user_id', $userId);
                },
                'savedDrops' => function ($query) use ($userId) {
                    return $query->where('user_id', $userId);
                },
            ])
            ->latest()
            ->paginate($perPage);

        $formattedDrops = collect($drops->items())
            ->map(fn (Drop $drop): array => $drop->formatDrop($user));

        $pendingBanner = Drop::query()
            ->where('creator_id', $userId)
            ->where('status', Drop::STATUS_DRAFT)
            ->get(['id', 'title'])
            ->map(fn (Drop $drop): array => [
                'drop_id' => $drop->id,
                'title' => $drop->title,
            ]);

        $rejectionBanner = Drop::query()
            ->where('creator_id', $userId)
            ->where('status', Drop::STATUS_REJECTED)
            ->get(['id', 'title'])
            ->map(fn (Drop $drop): array => [
                'drop_id' => $drop->id,
                'title' => $drop->title,
            ]);

        return response()->json([
            'data' => $formattedDrops,
            'total' => $drops->total(),
            'next_page' => $drops->hasMorePages() ? $drops->currentPage() + 1 : null,
            'pending_banner' => $pendingBanner,
            'rejection_banner' => $rejectionBanner,
        ]);
    }

    public function products(Request $request, int $drop_id): JsonResponse
    {
        $user = $request->user();
        $drop = Drop::where('creator_id', $user->id)->findOrFail($drop_id);

        $products = $drop->products()
            ->withSum('orderItems', 'quantity')
            ->with([
                'images',
                'store.user',
                'savedProducts' => fn ($query) => $query->where('user_id', $user->id),
            ])
            ->paginate($request->get('per_page', 10));

        return response()->json([
            'data' => collect($products->items())->map(fn (Product $product) => $drop->formatProduct($product, $user)),
            'total' => $products->total(),
            'next_page' => $products->hasMorePages() ? $products->currentPage() + 1 : null,
        ]);
    }

    public function show(Request $request, int $drop_id): JsonResponse
    {
        $user = $request->user();
        $userId = $user->id;

        $drop = Drop::query()
            ->where('creator_id', $userId)
            ->withCount('likedDrops')
            ->withCount('savedDrops')
            ->with([
                'creator',
                'images',
                'products.store.user',
                'products.images',
                'products' => function ($query) use ($userId): void {
                    $query->withSum('orderItems', 'quantity')->with([
                        'savedProducts' => fn ($saveQuery) => $saveQuery->where('user_id', $userId),
                    ]);
                },
                'likedDrops' => function ($query) use ($userId) {
                    return $query->where('user_id', $userId);
                },
                'savedDrops' => function ($query) use ($userId) {
                    return $query->where('user_id', $userId);
                },
            ])
            ->findOrFail($drop_id);

        return response()->json($drop->formatDrop($user));
    }

    public function delete(Request $request, int $drop_id): JsonResponse
    {
        $user = $request->user();
        $drop = Drop::where('creator_id', $user->id)->findOrFail($drop_id);

        $drop->images()->delete();
        $drop->products()->detach();
        $drop->delete();

        return response()->json([
            'message' => 'Drop deleted successfully.',
        ]);
    }
}
