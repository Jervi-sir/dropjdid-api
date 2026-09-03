<?php

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShowController extends Controller
{
    /**
     * Get single product details formatted matching ProductType with stats.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $userId = $request->user('sanctum')?->id ?? $request->user()?->id ?? $request->input('user_id');

        $product = Product::query()
            ->where('id', $id)
            ->with(['mainImage', 'images', 'savedUsers', 'likedUsers', 'store', 'category', 'labels'])
            ->withCount(['savedUsers', 'likedUsers', 'drops'])
            ->first();

        if (! $product) {
            return response()->json([
                'message' => 'Product not found.',
            ], 404);
        }

        $imageUrl = $product->mainImage?->image_url
            ?? $product->images->first()?->image_url
            ?? '';

        if ($imageUrl && ! str_starts_with($imageUrl, 'http://') && ! str_starts_with($imageUrl, 'https://')) {
            $imageUrl = url($imageUrl);
        }

        $priceShown = $product->price_shown ?? $product->price_original;
        $priceOriginal = $product->price_original;

        $promoPercentage = '';
        if ($priceOriginal && $priceShown && (float) $priceOriginal > (float) $priceShown) {
            $discount = round(((float) $priceOriginal - (float) $priceShown) / (float) $priceOriginal * 100);
            $promoPercentage = "-{$discount}%";
        }

        $isSaved = false;
        $isLiked = false;
        $isReposted = false;

        if ($userId) {
            if ($product->relationLoaded('savedUsers')) {
                $isSaved = $product->savedUsers->contains('id', $userId);
            }
            if ($product->relationLoaded('likedUsers')) {
                $isLiked = $product->likedUsers->contains('id', $userId);
            }
            $isReposted = \App\Models\UserInteraction::query()
                ->where('user_id', $userId)
                ->where('type', \App\Models\UserInteraction::TYPE_REPOST)
                ->where('target_type', \App\Models\UserInteraction::TARGET_PRODUCT)
                ->where('target_id', $product->id)
                ->exists();
        }

        $nbSaved = (int) ($product->saved_users_count ?? 0);
        $nbLiked = (int) ($product->liked_users_count ?? 0);
        $nbDrops = (int) ($product->drops_count ?? 0);
        $nbShares = \App\Models\UserInteraction::query()
            ->where('type', \App\Models\UserInteraction::TYPE_SHARE)
            ->where('target_type', \App\Models\UserInteraction::TARGET_PRODUCT)
            ->where('target_id', $product->id)
            ->count();
        $nbReposts = \App\Models\UserInteraction::query()
            ->where('type', \App\Models\UserInteraction::TYPE_REPOST)
            ->where('target_type', \App\Models\UserInteraction::TARGET_PRODUCT)
            ->where('target_id', $product->id)
            ->count();

        $stats = [
            'nb_interested' => $nbLiked,
            'nb_saved' => $nbSaved,
            'nb_shares' => $nbShares,
            'nb_drops' => $nbDrops,
            'nb_reposted' => $nbReposts,
            'nb_reposts' => $nbReposts,
        ];

        $data = [
            'id' => (int) $product->id,
            'image_url' => (string) $imageUrl,
            'prices' => [
                'price1' => $priceShown !== null ? number_format((float) $priceShown, 0, '.', ' ') . ' DZD' : '',
                'price2' => $priceOriginal !== null ? number_format((float) $priceOriginal, 0, '.', ' ') . ' DZD' : '',
                'promo_percentage' => (string) $promoPercentage,
            ],
            'text' => (string) ($product->name ?? 'Product #' . $product->id),
            'save' => [
                'is_saved' => (bool) $isSaved,
                'nb_save' => $nbSaved,
            ],
            'is_saved' => (bool) $isSaved,
            'nb_saved' => $nbSaved,
            'is_liked' => (bool) $isLiked,
            'nb_liked' => $nbLiked,
            'is_reposted' => (bool) $isReposted,
            'nb_reposted' => $nbReposts,
            'nb_reposts' => $nbReposts,
            'nb_shares' => $nbShares,
            'nb_drops' => $nbDrops,
            'stats' => $stats,
            'like' => [
                'is_liked' => (bool) $isLiked,
                'nb_liked' => $nbLiked,
            ],
        ];

        return response()->json([
            'data' => $data,
        ], 200);
    }
}
