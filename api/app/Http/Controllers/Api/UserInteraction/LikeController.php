<?php

namespace App\Http\Controllers\Api\UserInteraction;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\Drop;
use App\Models\Product;
use App\Models\UserInteraction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    /**
     * Toggle like on an Advertisement.
     * POST /api/interactions/ads/{id}/like or /api/ads/{id}/like
     */
    public function toggleAd(Request $request, int|string $id): JsonResponse
    {
        return $this->toggle(
            request: $request,
            targetType: UserInteraction::TARGET_ADVERTISEMENT,
            targetId: (int) $id,
            modelClass: Advertisement::class,
            counterColumn: 'nb_liked',
            resourceName: 'Advertisement'
        );
    }

    /**
     * Toggle like on a Drop.
     * POST /api/interactions/drops/{id}/like or /api/drops/{id}/like
     */
    public function toggleDrop(Request $request, int|string $id): JsonResponse
    {
        return $this->toggle(
            request: $request,
            targetType: UserInteraction::TARGET_DROP,
            targetId: (int) $id,
            modelClass: Drop::class,
            counterColumn: null,
            resourceName: 'Drop',
            pivotRelation: 'likedUsers'
        );
    }

    /**
     * Toggle like on a Product.
     * POST /api/interactions/products/{id}/like or /api/products/{id}/like
     */
    public function toggleProduct(Request $request, int|string $id): JsonResponse
    {
        return $this->toggle(
            request: $request,
            targetType: UserInteraction::TARGET_PRODUCT,
            targetId: (int) $id,
            modelClass: Product::class,
            counterColumn: null,
            resourceName: 'Product',
            pivotRelation: 'likedUsers'
        );
    }

    /**
     * Core toggle like interaction handler.
     */
    protected function toggle(
        Request $request,
        string $targetType,
        int $targetId,
        string $modelClass,
        ?string $counterColumn = null,
        string $resourceName = 'Item',
        ?string $pivotRelation = null
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
                ->where('type', UserInteraction::TYPE_LIKE)
                ->where('target_type', $targetType)
                ->where('target_id', $item->id)
                ->first();
        }

        if ($existing) {
            $existing->delete();
            if ($counterColumn && \Illuminate\Support\Facades\Schema::hasColumn($item->getTable(), $counterColumn)) {
                $item->decrement($counterColumn);
            }
            if ($pivotRelation && $userId && method_exists($item, $pivotRelation)) {
                $item->{$pivotRelation}()->detach($userId);
            }
            $isLiked = false;
        } else {
            UserInteraction::create([
                'user_id' => $userId,
                'type' => UserInteraction::TYPE_LIKE,
                'target_type' => $targetType,
                'target_id' => $item->id,
            ]);
            if ($counterColumn && \Illuminate\Support\Facades\Schema::hasColumn($item->getTable(), $counterColumn)) {
                $item->increment($counterColumn);
            }
            if ($pivotRelation && $userId && method_exists($item, $pivotRelation)) {
                $item->{$pivotRelation}()->syncWithoutDetaching([$userId]);
            }
            $isLiked = true;
        }

        // Count total likes
        $totalLikes = UserInteraction::where('type', UserInteraction::TYPE_LIKE)
            ->where('target_type', $targetType)
            ->where('target_id', $item->id)
            ->count();

        if ($counterColumn && isset($item->{$counterColumn})) {
            $item->refresh();
            $totalLikes = (int) $item->{$counterColumn};
        }

        return response()->json([
            'id' => (int) $item->id,
            'target_type' => $targetType,
            'is_liked' => (bool) $isLiked,
            'nb_liked' => (int) $totalLikes,
            'message' => $isLiked ? "{$resourceName} liked successfully." : "{$resourceName} unliked successfully.",
        ], 200);
    }
}
