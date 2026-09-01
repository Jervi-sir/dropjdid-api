<?php

namespace App\Http\Controllers\Api\UserInteraction;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\Drop;
use App\Models\Product;
use App\Models\UserInteraction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SaveController extends Controller
{
    /**
     * Toggle save on an Advertisement.
     * POST /api/interactions/ads/{id}/save or /api/ads/{id}/save
     */
    public function toggleAd(Request $request, int|string $id): JsonResponse
    {
        return $this->toggle(
            request: $request,
            targetType: UserInteraction::TARGET_ADVERTISEMENT,
            targetId: (int) $id,
            modelClass: Advertisement::class,
            counterColumn: 'nb_saved',
            resourceName: 'Advertisement'
        );
    }

    /**
     * Toggle save on a Drop.
     * POST /api/interactions/drops/{id}/save or /api/drops/{id}/save
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
            pivotRelation: 'savedUsers'
        );
    }

    /**
     * Toggle save on a Product.
     * POST /api/interactions/products/{id}/save or /api/products/{id}/save
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
            pivotRelation: 'savedUsers'
        );
    }

    /**
     * Core toggle save interaction handler.
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
                ->where('type', UserInteraction::TYPE_SAVE)
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
            $isSaved = false;
        } else {
            UserInteraction::create([
                'user_id' => $userId,
                'type' => UserInteraction::TYPE_SAVE,
                'target_type' => $targetType,
                'target_id' => $item->id,
            ]);
            if ($counterColumn && \Illuminate\Support\Facades\Schema::hasColumn($item->getTable(), $counterColumn)) {
                $item->increment($counterColumn);
            }
            if ($pivotRelation && $userId && method_exists($item, $pivotRelation)) {
                $item->{$pivotRelation}()->syncWithoutDetaching([$userId]);
            }
            $isSaved = true;
        }

        // Count total saves
        $totalSaves = UserInteraction::where('type', UserInteraction::TYPE_SAVE)
            ->where('target_type', $targetType)
            ->where('target_id', $item->id)
            ->count();

        if ($counterColumn && isset($item->{$counterColumn})) {
            $item->refresh();
            $totalSaves = (int) $item->{$counterColumn};
        }

        return response()->json([
            'id' => (int) $item->id,
            'target_type' => $targetType,
            'is_saved' => (bool) $isSaved,
            'nb_saved' => (int) $totalSaves,
            'message' => $isSaved ? "{$resourceName} saved successfully." : "{$resourceName} unsaved successfully.",
        ], 200);
    }
}
