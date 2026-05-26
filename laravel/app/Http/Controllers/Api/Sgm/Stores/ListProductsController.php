<?php

namespace App\Http\Controllers\Api\Sgm\Stores;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListProductsController extends Controller
{
    public function __invoke(Request $request, int $store_id): JsonResponse
    {
        $user = $request->user();
        $store = Store::find($store_id);
        abort_if($user === null, 401);
        abort_unless($store->user_id === $user->id, 404);

        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'status' => ['nullable', 'string', 'max:255'],
            'exclude_status' => ['nullable', 'string', 'max:255'],
        ]);

        $perPage = $validated['per_page'] ?? 10;

        $productsQuery = Product::query()
            ->where('store_id', $store->id)
            ->with([
                'images',
                'store.user',
                'savedProducts' => fn($query) => $query->where('user_id', $user->id),
            ])
            ->latest('id');

        if (! empty($validated['status'])) {
            $statusInt = array_search($validated['status'], Product::STATUSES);
            if ($statusInt !== false) {
                $productsQuery->where('status', $statusInt);
            }
        }

        if (! empty($validated['exclude_status'])) {
            $statusInt = array_search($validated['exclude_status'], Product::STATUSES);
            if ($statusInt !== false) {
                $productsQuery->where('status', '!=', $statusInt);
            }
        }

        $products = $productsQuery->simplePaginate($perPage);

        return response()->json([
            'data' => collect($products->items())->map(fn(Product $item): array => [
                'id' => $item->id,
                'price' => (float) ($item->show_price ?? $item->store_price ?? $item->original_price ?? 0),
                'image' => $item->images->sortBy('sort_order')->first()?->image,
                'status' => $item->status_details,
                'user' => [
                    'id' => $item->store?->user?->id,
                    'name' => $item->store?->user?->username,
                ],
                'is_draft' => $item->status === Product::STATUS_DRAFT,
            ])->values(),
            'next_page' => $products->hasMorePages() ? $products->currentPage() + 1 : null,
        ]);
    }
}
