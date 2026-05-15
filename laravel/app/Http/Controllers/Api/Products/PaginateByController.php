<?php

namespace App\Http\Controllers\Api\Products;

use App\Http\Controllers\Controller;
use App\Models\Label;
use App\Models\LikedProduct;
use App\Models\PaymentMethod;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaginateByController extends Controller
{
    public function byLabels(Request $request, int $product_id): JsonResponse
    {
        $userId = $request->user()?->id;

        $labelIds = Product::findOrFail($product_id)
            ->productKeywords()
            ->pluck('label_id')
            ->unique()
            ->values();

        $labels = Label::query()
            ->whereIn('id', $labelIds)
            ->simplePaginate(5);

        $data = collect($labels->items())->map(function (Label $label) use ($product_id, $userId) {
            $products = Product::query()
                ->where('status', 'published')
                ->where('id', '!=', $product_id)
                ->whereHas('productKeywords', fn (Builder $query) => $query->where('label_id', $label->id))
                ->with(['images', 'store.user', 'savedProducts' => function ($query) use ($userId) {
                    return $userId === null ? $query->whereRaw('1 = 0') : $query->where('user_id', $userId);
                }])
                ->inRandomOrder()
                ->limit(10)
                ->get();

            $likedProductsCount = LikedProduct::query()
                ->where('user_id', $userId)
                ->whereHas('product.productKeywords', fn (Builder $query) => $query->where('label_id', $label->id))
                ->count();

            return [
                'type' => 'label',
                'label' => [
                    'id' => $label->id,
                    'code' => $label->code,
                    'en' => $label->en,
                    'fr' => $label->fr,
                    'ar' => $label->ar,
                ],
                'products' => $products->map(fn (Product $p) => [
                    'id' => $p->id,
                    'image' => $p->images->sortBy('sort_order')->first()?->image,
                    'images' => $p->images->sortBy('sort_order')->pluck('image')->values()->all(),
                    'price' => (float) ($p->show_price ?? $p->store_price ?? $p->original_price ?? 0),
                    'user' => [
                        'id' => $p->store?->user?->id,
                        'username' => $p->store?->user?->username,
                    ],
                    'is_saved' => $userId !== null && $p->savedProducts->isNotEmpty(),
                ]),
                'nb_likes' => $likedProductsCount,
                'next_page' => null,
            ];
        });

        return response()->json([
            'data' => $data,
            'next_page' => $labels->hasMorePages() ? $labels->currentPage() + 1 : null,
        ]);
    }

    public function byProductId(Request $request, int $product_id): JsonResponse
    {
        $userId = $request->user()?->id;

        $products = Product::query()
            ->where('status', 'published')
            ->where('id', '!=', $product_id)
            ->with([
                'images',
                'store.user',
                'savedProducts' => function ($query) use ($userId) {
                    return $userId === null
                        ? $query->whereRaw('1 = 0')
                        : $query->where('user_id', $userId);
                },
            ])
            ->inRandomOrder()
            ->simplePaginate(10);

        return response()->json([
            'data' => collect($products->items())->map(fn (Product $product) => [
                'id' => $product->id,
                'image' => $product->images->sortBy('sort_order')->first()?->image,
                'images' => $product->images->sortBy('sort_order')->pluck('image')->values()->all(),
                'price' => (float) ($product->show_price ?? $product->store_price ?? $product->original_price ?? 0),
                'user' => [
                    'id' => $product->store?->id,
                    'username' => $product->store?->store_name ?? $product->store?->user?->username,
                ],
                'is_saved' => $userId !== null && $product->savedProducts->isNotEmpty(),
            ]),
            'next_page' => $products->hasMorePages() ? $products->currentPage() + 1 : null,
        ]);
    }

    public function byLabelId(Request $request, int $label_id): JsonResponse
    {
        $user = $request->user();
        $userId = $user?->id;
        $query = $request->query('query');

        $products = Product::query()
            ->where('status', 'published')
            ->whereHas('paymentMethod', fn ($query) => $query->where('code', PaymentMethod::ONLINE))
            ->where(function ($q) use ($label_id) {
                $q->whereHas('productKeywords', fn (Builder $qk) => $qk->where('product_keywords.label_id', $label_id))
                    ->orWhereHas('keywords', fn ($k) => $k->where('keywords.label_id', $label_id));
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
            ->simplePaginate(10);

        return response()->json([
            'data' => collect($products->items())->map(fn (Product $product) => $product->formatProduct($product, $user)),
            'next_page' => $products->hasMorePages() ? $products->currentPage() + 1 : null,
        ]);
    }

    public function byDropId(Request $request, int $drop_id): JsonResponse
    {
        $userId = $request->user()?->id;

        $products = Product::query()
            ->where('status', 'published')
            ->whereHas('drops', fn (Builder $query) => $query->where('drops.id', $drop_id))
            ->with([
                'images',
                'store.user',
                'savedProducts' => function ($query) use ($userId) {
                    return $userId === null
                        ? $query->whereRaw('1 = 0')
                        : $query->where('user_id', $userId);
                },
            ])
            ->inRandomOrder()
            ->simplePaginate(10);

        return response()->json([
            'data' => collect($products->items())->map(fn (Product $product) => [
                'id' => $product->id,
                'image' => $product->images->sortBy('sort_order')->first()?->image,
                'images' => $product->images->sortBy('sort_order')->pluck('image')->values()->all(),
                'price' => (float) ($product->show_price ?? $product->store_price ?? $product->original_price ?? 0),
                'user' => [
                    'id' => $product->user?->id,
                    'username' => $product->user?->username,
                ],
                'is_saved' => $userId !== null && $product->savedProducts->isNotEmpty(),
            ]),
            'next_page' => $products->hasMorePages() ? $products->currentPage() + 1 : null,
        ]);
    }
}
