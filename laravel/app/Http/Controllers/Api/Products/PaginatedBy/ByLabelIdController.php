<?php

namespace App\Http\Controllers\Api\Products\PaginatedBy;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ByLabelIdController extends Controller
{
    public function __invoke(Request $request, int $label_id): JsonResponse
    {
        $user = $request->user();
        $userId = $user?->id;
        $query = $request->query('query');

        $products = Product::query()
            ->where('status', Product::STATUS_PUBLISHED)
            // ->whereHas('paymentMethod', fn ($query) => $query->where('code', PaymentMethod::ONLINE))
            ->where(function ($q) use ($label_id) {
                $q->whereHas('productKeywords', fn(Builder $qk) => $qk->where('product_keywords.label_id', $label_id))
                    ->orWhereHas('keywords', fn($k) => $k->where('keywords.label_id', $label_id));
            })
            ->when($query, function (Builder $q) use ($query) {
                $q->where('name', 'ilike', "%$query%");
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
            ->latest('id')
            ->simplePaginate($request->integer('per_page', 10));

        return response()->json([
            'data' => collect($products->items())->map(fn(Product $product) => $product->formatProduct($product, $user)),
            'next_page' => $products->hasMorePages() ? $products->currentPage() + 1 : null,
        ]);
    }
}
