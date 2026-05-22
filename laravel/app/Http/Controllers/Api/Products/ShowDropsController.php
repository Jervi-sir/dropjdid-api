<?php

namespace App\Http\Controllers\Api\Products;

use App\Http\Controllers\Controller;
use App\Models\Drop;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShowDropsController extends Controller
{
    public function __invoke(Request $request, $product_id): JsonResponse
    {
        $product = Product::findOrFail((int) $product_id);
        $user = $request->user();
        $userId = $user?->id;

        $drops = $product->drops()
            ->where('status', Drop::STATUS_PUBLISHED)
            ->withCount(['likedDrops', 'products', 'savedDrops'])
            ->latest()
            ->paginate($request->get('per_page', 10));

        Drop::loadFeedRelations($drops, $userId);

        $formattedDrops = collect($drops->items())
            ->map(fn (Drop $drop): array => $drop->formatDrop($user));

        return response()->json([
            'data' => $formattedDrops,
            'count' => $drops->total(),
            'next_page' => $drops->hasMorePages() ? $drops->currentPage() + 1 : null,
        ]);
    }
}
