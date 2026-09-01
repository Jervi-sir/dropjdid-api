<?php

namespace App\Http\Controllers\Api\UserInteraction;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\Drop;
use App\Models\Product;
use App\Models\User;
use App\Models\UserInteraction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class RepostController extends Controller
{
    /**
     * Toggle repost on a Drop.
     * POST /api/interactions/drops/{id}/repost or /api/drops/{id}/repost
     */
    public function toggleDrop(Request $request, int|string $id): JsonResponse
    {
        return $this->toggle(
            request: $request,
            targetType: UserInteraction::TARGET_DROP,
            targetId: (int) $id,
            modelClass: Drop::class,
            counterColumn: 'nb_reposted',
            resourceName: 'Drop'
        );
    }

    /**
     * Toggle repost on a Product.
     * POST /api/interactions/products/{id}/repost or /api/products/{id}/repost
     */
    public function toggleProduct(Request $request, int|string $id): JsonResponse
    {
        return $this->toggle(
            request: $request,
            targetType: UserInteraction::TARGET_PRODUCT,
            targetId: (int) $id,
            modelClass: Product::class,
            counterColumn: 'nb_reposted',
            resourceName: 'Product'
        );
    }

    /**
     * Toggle repost on an Advertisement.
     * POST /api/interactions/ads/{id}/repost or /api/ads/{id}/repost
     */
    public function toggleAd(Request $request, int|string $id): JsonResponse
    {
        return $this->toggle(
            request: $request,
            targetType: UserInteraction::TARGET_ADVERTISEMENT,
            targetId: (int) $id,
            modelClass: Advertisement::class,
            counterColumn: 'nb_reposted',
            resourceName: 'Advertisement'
        );
    }

    /**
     * Core toggle repost interaction handler.
     */
    protected function toggle(
        Request $request,
        string $targetType,
        int $targetId,
        string $modelClass,
        ?string $counterColumn = null,
        string $resourceName = 'Item'
    ): JsonResponse {
        $user = $request->user('sanctum') ?? $request->user();
        $userId = $user?->id ?? $request->input('user_id');

        $item = $modelClass::find($targetId);
        if (! $item) {
            return response()->json([
                'message' => "{$resourceName} not found.",
            ], 404);
        }

        $existing = null;
        if ($userId) {
            $existing = UserInteraction::where('user_id', $userId)
                ->where('type', UserInteraction::TYPE_REPOST)
                ->where('target_type', $targetType)
                ->where('target_id', $item->id)
                ->first();
        }

        $note = $request->input('note') ?? $request->input('message') ?? $request->input('quote');

        if ($existing) {
            $existing->delete();
            if ($counterColumn && Schema::hasColumn($item->getTable(), $counterColumn)) {
                $item->decrement($counterColumn);
            }
            $isReposted = false;
        } else {
            UserInteraction::create([
                'user_id' => $userId,
                'type' => UserInteraction::TYPE_REPOST,
                'target_type' => $targetType,
                'target_id' => $item->id,
                'meta' => $note ? ['quote' => $note] : null,
            ]);
            if ($counterColumn && Schema::hasColumn($item->getTable(), $counterColumn)) {
                $item->increment($counterColumn);
            }
            $isReposted = true;
        }

        // Calculate total repost count
        $totalReposts = UserInteraction::where('type', UserInteraction::TYPE_REPOST)
            ->where('target_type', $targetType)
            ->where('target_id', $item->id)
            ->count();

        if ($counterColumn && isset($item->{$counterColumn})) {
            $item->refresh();
            $totalReposts = (int) $item->{$counterColumn};
        }

        return response()->json([
            'id' => (int) $item->id,
            'target_type' => $targetType,
            'is_reposted' => (bool) $isReposted,
            'nb_reposted' => (int) $totalReposts,
            'nb_reposts' => (int) $totalReposts,
            'message' => $isReposted ? "{$resourceName} reposted successfully." : "{$resourceName} unreposted successfully.",
        ], 200);
    }

    /**
     * Get reposts for the current authenticated user.
     * GET /api/interactions/my-reposts
     */
    public function myReposts(Request $request): JsonResponse
    {
        $user = $request->user('sanctum') ?? $request->user();
        $userId = $user?->id ?? $request->query('user_id');

        if (! $userId) {
            return response()->json([
                'data' => [],
                'total' => 0,
            ], 200);
        }

        return $this->getUserReposts((int) $userId, $request);
    }

    /**
     * Get reposts for a specific user.
     * GET /api/interactions/users/{userId}/reposts
     */
    public function userReposts(Request $request, int|string $userId): JsonResponse
    {
        return $this->getUserReposts((int) $userId, $request);
    }

    /**
     * Helper to fetch and hydrate user reposts.
     */
    protected function getUserReposts(int $userId, Request $request): JsonResponse
    {
        $targetType = $request->query('target_type'); // optional filter: 'drop', 'product', 'advertisement'

        $query = UserInteraction::where('user_id', $userId)
            ->where('type', UserInteraction::TYPE_REPOST)
            ->latest('id');

        if ($targetType) {
            $query->where('target_type', $targetType);
        }

        $perPage = min((int) $request->query('per_page', 20), 50);
        $reposts = $query->paginate($perPage);

        // Group IDs by target type to eagerly load relations efficiently
        $dropIds = [];
        $productIds = [];
        $adIds = [];

        foreach ($reposts->items() as $interaction) {
            if ($interaction->target_type === UserInteraction::TARGET_DROP) {
                $dropIds[] = $interaction->target_id;
            } elseif ($interaction->target_type === UserInteraction::TARGET_PRODUCT) {
                $productIds[] = $interaction->target_id;
            } elseif ($interaction->target_type === UserInteraction::TARGET_ADVERTISEMENT) {
                $adIds[] = $interaction->target_id;
            }
        }

        $drops = ! empty($dropIds) ? Drop::with(['creator', 'images', 'mainImage', 'likedUsers', 'savedUsers'])->whereIn('id', $dropIds)->get()->keyBy('id') : collect();
        $products = ! empty($productIds) ? Product::with(['mainImage', 'images', 'store', 'savedUsers', 'likedUsers'])->whereIn('id', $productIds)->get()->keyBy('id') : collect();
        $ads = ! empty($adIds) ? Advertisement::whereIn('id', $adIds)->get()->keyBy('id') : collect();

        $items = collect($reposts->items())->map(function ($interaction) use ($drops, $products, $ads, $userId) {
            $formattedItem = null;

            if ($interaction->target_type === UserInteraction::TARGET_DROP) {
                /** @var Drop|null $drop */
                $drop = $drops->get($interaction->target_id);
                if ($drop) {
                    $img = $drop->mainImage?->image ?? $drop->images->first()?->image;
                    if ($img && ! str_starts_with($img, 'http://') && ! str_starts_with($img, 'https://')) {
                        $img = url($img);
                    }

                    $formattedItem = [
                        'id' => (int) $drop->id,
                        'text1' => (string) ($drop->title ?? 'Drop #' . $drop->id),
                        'text2' => (string) ($drop->creator ? '@' . ltrim($drop->creator->username, '@') : ($drop->description ?? '')),
                        'image_url' => $img ? (string) $img : null,
                        'is_saved' => $drop->savedUsers ? $drop->savedUsers->contains('id', $userId) : false,
                        'is_liked' => $drop->likedUsers ? $drop->likedUsers->contains('id', $userId) : false,
                        'is_reposted' => true,
                    ];
                }
            } elseif ($interaction->target_type === UserInteraction::TARGET_PRODUCT) {
                /** @var Product|null $product */
                $product = $products->get($interaction->target_id);
                if ($product) {
                    $img = $product->mainImage?->image_url ?? $product->images->first()?->image_url ?? '';
                    if ($img && ! str_starts_with($img, 'http://') && ! str_starts_with($img, 'https://')) {
                        $img = url($img);
                    }

                    $priceShown = $product->price_shown ?? $product->price_original;
                    $priceOriginal = $product->price_original;
                    $promoPercentage = '';
                    if ($priceOriginal && $priceShown && (float) $priceOriginal > (float) $priceShown) {
                        $discount = round(((float) $priceOriginal - (float) $priceShown) / (float) $priceOriginal * 100);
                        $promoPercentage = "-{$discount}%";
                    }

                    $isSaved = $product->savedUsers ? $product->savedUsers->contains('id', $userId) : false;

                    $formattedItem = [
                        'id' => (int) $product->id,
                        'text' => (string) ($product->name ?? 'Product #' . $product->id),
                        'image_url' => (string) $img,
                        'prices' => [
                            'price1' => $priceShown !== null ? number_format((float) $priceShown, 0, '.', ' ') . ' DZD' : '',
                            'price2' => $priceOriginal !== null ? number_format((float) $priceOriginal, 0, '.', ' ') . ' DZD' : '',
                            'promo_percentage' => (string) $promoPercentage,
                        ],
                        'save' => [
                            'is_saved' => (bool) $isSaved,
                            'nb_save' => (int) ($product->saved_users_count ?? 0),
                        ],
                        'is_saved' => (bool) $isSaved,
                        'is_liked' => $product->likedUsers ? $product->likedUsers->contains('id', $userId) : false,
                        'is_reposted' => true,
                    ];
                }
            } elseif ($interaction->target_type === UserInteraction::TARGET_ADVERTISEMENT) {
                $formattedItem = $ads->get($interaction->target_id);
            }

            return [
                'interaction_id' => (int) $interaction->id,
                'target_type' => $interaction->target_type,
                'target_id' => (int) $interaction->target_id,
                'quote' => $interaction->meta['quote'] ?? null,
                'created_at' => $interaction->created_at,
                'item' => $formattedItem,
                // Flatten common fields for easier consumption
                ...($formattedItem ?? []),
            ];
        })->filter(fn ($item) => ! empty($item['item']))->values();

        return response()->json([
            'current_page' => $reposts->currentPage(),
            'data' => $items,
            'total' => $reposts->total(),
            'per_page' => $reposts->perPage(),
            'last_page' => $reposts->lastPage(),
            'next_page' => $reposts->hasMorePages() ? $reposts->currentPage() + 1 : null,
        ], 200);
    }
}
