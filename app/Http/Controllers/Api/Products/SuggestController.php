<?php

namespace App\Http\Controllers\Api\Products;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SuggestController extends Controller
{
    public function __invoke(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'label_id' => ['nullable', 'integer', 'exists:labels,id'],
        ]);

        $perPage = $validated['per_page'] ?? 10;
        $userId = $request->user()?->getAuthIdentifier();

        $suggestions = Product::query()
            ->where('status', 'published')
            ->whereKeyNot($product->id)
            ->when(isset($validated['label_id']), function (Builder $query) use ($validated): void {
                $query->whereHas('keywords.label', function (Builder $labelQuery) use ($validated): void {
                    $labelQuery->where('labels.id', $validated['label_id']);
                });
            }, function (Builder $query): void {
                $query->inRandomOrder();
            })
            ->with([
                'images',
                'store.user',
                'savedProducts' => function ($query) use ($userId) {
                    return $userId === null
                        ? $query->whereRaw('1 = 0')
                        : $query->where('user_id', $userId);
                },
            ])
            ->simplePaginate($perPage);

        return response()->json([
            'data' => $suggestions->getCollection()->map(fn (Product $item): array => [
                'id' => $item->id,
                'price' => (float) ($item->show_price ?? $item->store_price ?? $item->original_price ?? 0),
                'image' => $item->images->sortBy('sort_order')->first()?->image,
                'user' => [
                    'id' => $item->store?->user?->id,
                    'name' => $item->store?->user?->username,
                ],
                'is_saved' => $request->user() !== null && $item->savedProducts->isNotEmpty(),
            ])->values(),
            'next_page' => $suggestions->hasMorePages() ? $suggestions->currentPage() + 1 : null,
        ]);
    }
}
