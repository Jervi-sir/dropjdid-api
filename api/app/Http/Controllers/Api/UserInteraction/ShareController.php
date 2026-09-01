<?php

namespace App\Http\Controllers\Api\UserInteraction;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\Drop;
use App\Models\Product;
use App\Models\UserInteraction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShareController extends Controller
{
    /**
     * Record a share on an Advertisement.
     * POST /api/interactions/ads/{id}/share or /api/ads/{id}/share
     */
    public function shareAd(Request $request, int|string $id): JsonResponse
    {
        return $this->recordShare(
            request: $request,
            targetType: UserInteraction::TARGET_ADVERTISEMENT,
            targetId: (int) $id,
            modelClass: Advertisement::class,
            counterColumn: 'nb_shared',
            resourceName: 'Advertisement'
        );
    }

    /**
     * Record a share on a Drop.
     * POST /api/interactions/drops/{id}/share or /api/drops/{id}/share
     */
    public function shareDrop(Request $request, int|string $id): JsonResponse
    {
        return $this->recordShare(
            request: $request,
            targetType: UserInteraction::TARGET_DROP,
            targetId: (int) $id,
            modelClass: Drop::class,
            counterColumn: null,
            resourceName: 'Drop'
        );
    }

    /**
     * Record a share on a Product.
     * POST /api/interactions/products/{id}/share or /api/products/{id}/share
     */
    public function shareProduct(Request $request, int|string $id): JsonResponse
    {
        return $this->recordShare(
            request: $request,
            targetType: UserInteraction::TARGET_PRODUCT,
            targetId: (int) $id,
            modelClass: Product::class,
            counterColumn: null,
            resourceName: 'Product'
        );
    }

    /**
     * Record a share on a Profile / User.
     * POST /api/interactions/profiles/{id}/share or /api/interactions/people/{id}/share
     */
    public function shareProfile(Request $request, int|string $id): JsonResponse
    {
        return $this->recordShare(
            request: $request,
            targetType: UserInteraction::TARGET_PROFILE,
            targetId: (int) $id,
            modelClass: \App\Models\User::class,
            counterColumn: null,
            resourceName: 'Profile'
        );
    }

    /**
     * Core share recording handler.
     */
    protected function recordShare(
        Request $request,
        string $targetType,
        int $targetId,
        string $modelClass,
        ?string $counterColumn = null,
        string $resourceName = 'Item'
    ): JsonResponse {
        $user = $request->user('sanctum') ?? $request->user();
        $userId = $user?->id ?? $request->input('user_id');
        $sharedToUserId = $request->input('shared_to_user_id');
        $channel = $request->input('channel', 'app');

        $item = $modelClass::find($targetId);
        if (! $item) {
            return response()->json([
                'message' => "{$resourceName} not found.",
            ], 404);
        }

        UserInteraction::create([
            'user_id' => $userId,
            'type' => UserInteraction::TYPE_SHARE,
            'target_type' => $targetType,
            'target_id' => $item->id,
            'meta' => [
                'shared_to_user_id' => $sharedToUserId,
                'channel' => $channel,
            ],
        ]);

        if ($counterColumn && \Illuminate\Support\Facades\Schema::hasColumn($item->getTable(), $counterColumn)) {
            $item->increment($counterColumn);
            $item->refresh();
        }

        $totalShares = UserInteraction::where('type', UserInteraction::TYPE_SHARE)
            ->where('target_type', $targetType)
            ->where('target_id', $item->id)
            ->count();

        if ($counterColumn && isset($item->{$counterColumn})) {
            $totalShares = (int) $item->{$counterColumn};
        }

        return response()->json([
            'id' => (int) $item->id,
            'target_type' => $targetType,
            'nb_shares' => (int) $totalShares,
            'nb_shared' => (int) $totalShares,
            'message' => "{$resourceName} share recorded successfully.",
        ], 200);
    }
}
