<?php

namespace App\Http\Controllers\Api\Products;

use App\Http\Controllers\Controller;
use App\Models\Drop;
use App\Models\Product;
use App\Models\SavedLabel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShowProductController extends Controller
{
    public function __invoke(Request $request, $product_id): JsonResponse
    {
        $userId = $request->user()?->getAuthIdentifier();

        $product = Product::find((int) $product_id);

        $product->load([
            'images',
            'drops' => function ($query) {
                $query->where('status', Drop::STATUS_PUBLISHED);
            },
            'variants.size',
            'store.user',
            'paymentMethod',
            'likedProducts' => function ($query) use ($userId) {
                return $userId === null
                    ? $query->whereRaw('1 = 0')
                    : $query->where('user_id', $userId);
            },
            'savedProducts' => function ($query) use ($userId) {
                return $userId === null
                    ? $query->whereRaw('1 = 0')
                    : $query->where('user_id', $userId);
            },
            'keywords.label',
        ]);

        $labels = $product->keywords
            ->map(fn ($keyword) => $keyword->label)
            ->filter()
            ->unique('id')
            ->values()
            ->map(fn ($label) => [
                'id' => $label->id,
                'code' => $label->code,
                'en' => $label->en,
                'fr' => $label->fr,
                'ar' => $label->ar,
                'is_liked' => $userId !== null && SavedLabel::where('label_id', $label->id)->where('user_id', $userId)->exists(),
                'nb_likes' => SavedLabel::where('label_id', $label->id)->count(),
            ])
            ->all();

        return response()->json([
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'images' => $product->images->sortBy('sort_order')->pluck('image')->values()->all(),
                'price' => (float) ($product->show_price ?? $product->store_price ?? $product->original_price ?? 0),
                'nb_likes' => $product->likedProducts()->count(),
                'is_liked' => $request->user() !== null && $product->likedProducts->isNotEmpty(),
                'available_sizes' => $product->variants
                    ->map(fn ($variant) => $variant->size?->code)
                    ->filter()
                    ->unique()
                    ->values()
                    ->all(),
                'search_code' => strtolower('sam'.$product->id),
                'description' => $product->description,
                'is_saved' => $request->user() !== null && $product->savedProducts->isNotEmpty(),
                'nb_drops' => $product->drops->count(),
                'labels' => $labels,
                'payment_method' => $product->paymentMethod ? [
                    'id' => $product->paymentMethod->id,
                    'code' => $product->paymentMethod->code,
                    'en' => $product->paymentMethod->en,
                    'fr' => $product->paymentMethod->fr,
                    'ar' => $product->paymentMethod->ar,
                ] : null,
                'user' => [
                    'id' => $product->store?->user?->id,
                    'name' => $product->store?->user?->username,
                ],
            ],
        ]);
    }
}
