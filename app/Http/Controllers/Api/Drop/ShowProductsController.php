<?php

namespace App\Http\Controllers\Api\Drop;

use App\Http\Controllers\Controller;
use App\Models\Drop;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShowProductsController extends Controller
{
    /**
     * Get list of products in a drop formatted as ProductType[].
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $userId = $request->user('sanctum')?->id ?? $request->user()?->id;

        $drop = Drop::find($id);

        if (! $drop) {
            return response()->json([
                'message' => 'Drop not found.',
            ], 404);
        }

        $query = $drop->products()
            ->with(['mainImage', 'images', 'savedUsers'])
            ->withCount('savedUsers');

        $page = $request->query('page');
        $perPage = $request->query('per_page');

        if ($page !== null || $perPage !== null) {
            $paginator = $query->paginate(max(1, min(100, (int) ($perPage ?? 20))));
            $collection = $paginator->getCollection();
            $nextPage = $paginator->hasMorePages() ? $paginator->currentPage() + 1 : null;
            $total = $paginator->total();
            $currentPage = $paginator->currentPage();
        } else {
            $collection = $query->get();
            $nextPage = null;
            $total = $collection->count();
            $currentPage = 1;
        }

        $data = $collection->map(function (Product $product) use ($userId) {
            $imageUrl = $product->mainImage?->image_url
                ?? $product->images->first()?->image_url
                ?? '';

            if ($imageUrl && ! str_starts_with($imageUrl, 'http://') && ! str_starts_with($imageUrl, 'https://')) {
                $imageUrl = url($imageUrl);
            }

            // Pivot drop_price overrides shown price if present
            $priceShown = $product->pivot->drop_price
                ?? $product->price_shown
                ?? $product->price_original;

            $priceOriginal = $product->price_original;

            $promoPercentage = '';
            if ($priceOriginal && $priceShown && (float) $priceOriginal > (float) $priceShown) {
                $discount = round(((float) $priceOriginal - (float) $priceShown) / (float) $priceOriginal * 100);
                $promoPercentage = "-{$discount}%";
            }

            $isSaved = false;
            if ($userId && $product->relationLoaded('savedUsers')) {
                $isSaved = $product->savedUsers->contains('id', $userId);
            }

            return [
                'id' => (int) $product->id,
                'image_url' => (string) $imageUrl,
                'prices' => [
                    'price1' => $priceShown !== null ? number_format((float) $priceShown, 0, '.', ' ') . ' DZD' : '',
                    'price2' => $priceOriginal !== null ? number_format((float) $priceOriginal, 0, '.', ' ') . ' DZD' : '',
                    'promo_percentage' => (string) $promoPercentage,
                ],
                'text' => (string) ($product->name ?? 'Product #'.$product->id),
                'save' => [
                    'is_saved' => (bool) $isSaved,
                    'nb_save' => (int) ($product->saved_users_count ?? 0),
                ],
            ];
        })->values();

        return response()->json([
            'data' => $data,
            'current_page' => $currentPage,
            'next_page' => $nextPage,
            'total' => $total,
        ], 200);
    }
}
