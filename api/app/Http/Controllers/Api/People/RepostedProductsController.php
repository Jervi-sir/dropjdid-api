<?php

namespace App\Http\Controllers\Api\People;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use App\Models\UserInteraction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RepostedProductsController extends Controller
{
    /**
     * Get products reposted by this user/profile.
     *
     * @param Request $request
     * @param int|string $id Target user ID
     * @return JsonResponse
     */
    public function index(Request $request, int|string $id): JsonResponse
    {
        $currentUserId = $request->user('sanctum')?->id ?? $request->user()?->id ?? $request->query('user_id');

        $targetUser = User::query()
            ->where('id', $id)
            ->first();

        if (! $targetUser) {
            return response()->json([
                'message' => 'User not found.',
            ], 404);
        }

        $perPage = max(1, min(100, (int) $request->query('per_page', 20)));
        $page = max(1, (int) $request->query('page', 1));

        $query = UserInteraction::query()
            ->where('user_id', $targetUser->id)
            ->where('type', UserInteraction::TYPE_REPOST)
            ->where('target_type', UserInteraction::TARGET_PRODUCT)
            ->latest('id');

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);
        $interactions = collect($paginator->items());

        $productIds = $interactions->pluck('target_id')->filter()->unique()->values()->all();

        $products = ! empty($productIds)
            ? Product::query()
                ->whereIn('id', $productIds)
                ->with(['mainImage', 'images', 'store', 'savedUsers', 'likedUsers'])
                ->withCount(['savedUsers', 'likedUsers'])
                ->get()
                ->keyBy('id')
            : collect();

        $data = $interactions->map(function (UserInteraction $interaction) use ($products, $currentUserId) {
            /** @var Product|null $product */
            $product = $products->get($interaction->target_id);

            if (! $product) {
                return null;
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

            if ($currentUserId) {
                $isSaved = $product->savedUsers ? $product->savedUsers->contains('id', $currentUserId) : false;
                $isLiked = $product->likedUsers ? $product->likedUsers->contains('id', $currentUserId) : false;
                $isReposted = UserInteraction::query()
                    ->where('user_id', $currentUserId)
                    ->where('type', UserInteraction::TYPE_REPOST)
                    ->where('target_type', UserInteraction::TARGET_PRODUCT)
                    ->where('target_id', $product->id)
                    ->exists();
            }

            return [
                'id' => (int) $product->id,
                'interaction_id' => (int) $interaction->id,
                'image_url' => (string) $imageUrl,
                'text' => (string) ($product->name ?? 'Product #' . $product->id),
                'text1' => (string) ($product->name ?? 'Product #' . $product->id),
                'text2' => (string) ($product->store?->name ?? ''),
                'prices' => [
                    'price1' => $priceShown !== null ? number_format((float) $priceShown, 0, '.', ' ') . ' DZD' : '',
                    'price2' => $priceOriginal !== null ? number_format((float) $priceOriginal, 0, '.', ' ') . ' DZD' : '',
                    'promo_percentage' => (string) $promoPercentage,
                ],
                'save' => [
                    'is_saved' => (bool) $isSaved,
                    'nb_save' => (int) ($product->saved_users_count ?? 0),
                ],
                'quote' => $interaction->meta['quote'] ?? null,
                'reposted_at' => $interaction->created_at,
                'created_at' => $product->created_at,
                'is_saved' => (bool) $isSaved,
                'is_liked' => (bool) $isLiked,
                'is_reposted' => (bool) $isReposted,
            ];
        })->filter()->values()->all();

        $nextPage = $paginator->hasMorePages() ? ($page + 1) : null;

        return response()->json([
            'data' => $data,
            'current_page' => $paginator->currentPage(),
            'next_page' => $nextPage,
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
        ], 200);
    }
}
