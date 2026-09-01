<?php

namespace App\Http\Controllers\Api\Ads;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\UserInteraction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdInteractionController extends Controller
{
    /**
     * Toggle like on an advertisement.
     *
     * @param Request $request
     * @param int|string $id
     * @return JsonResponse
     */
    public function toggleLike(Request $request, int|string $id): JsonResponse
    {
        $user = $request->user('sanctum') ?? $request->user();
        $userId = $user?->id ?? $request->input('user_id');

        $ad = Advertisement::find($id);
        if (! $ad) {
            return response()->json([
                'message' => 'Advertisement not found.',
            ], 404);
        }

        $existing = null;
        if ($userId) {
            $existing = UserInteraction::where('user_id', $userId)
                ->where('type', UserInteraction::TYPE_LIKE)
                ->where('target_type', UserInteraction::TARGET_ADVERTISEMENT)
                ->where('target_id', $ad->id)
                ->first();
        }

        if ($existing) {
            $existing->delete();
            $ad->decrement('nb_liked');
            $isLiked = false;
        } else {
            UserInteraction::create([
                'user_id' => $userId,
                'type' => UserInteraction::TYPE_LIKE,
                'target_type' => UserInteraction::TARGET_ADVERTISEMENT,
                'target_id' => $ad->id,
            ]);
            $ad->increment('nb_liked');
            $isLiked = true;
        }

        $ad->refresh();

        return response()->json([
            'is_liked' => (bool) $isLiked,
            'nb_liked' => (int) $ad->nb_liked,
            'message' => $isLiked ? 'Advertisement liked.' : 'Advertisement unliked.',
        ], 200);
    }

    /**
     * Toggle save on an advertisement.
     *
     * @param Request $request
     * @param int|string $id
     * @return JsonResponse
     */
    public function toggleSave(Request $request, int|string $id): JsonResponse
    {
        $user = $request->user('sanctum') ?? $request->user();
        $userId = $user?->id ?? $request->input('user_id');

        $ad = Advertisement::find($id);
        if (! $ad) {
            return response()->json([
                'message' => 'Advertisement not found.',
            ], 404);
        }

        $existing = null;
        if ($userId) {
            $existing = UserInteraction::where('user_id', $userId)
                ->where('type', UserInteraction::TYPE_SAVE)
                ->where('target_type', UserInteraction::TARGET_ADVERTISEMENT)
                ->where('target_id', $ad->id)
                ->first();
        }

        if ($existing) {
            $existing->delete();
            $ad->decrement('nb_saved');
            $isSaved = false;
        } else {
            UserInteraction::create([
                'user_id' => $userId,
                'type' => UserInteraction::TYPE_SAVE,
                'target_type' => UserInteraction::TARGET_ADVERTISEMENT,
                'target_id' => $ad->id,
            ]);
            $ad->increment('nb_saved');
            $isSaved = true;
        }

        $ad->refresh();

        return response()->json([
            'is_saved' => (bool) $isSaved,
            'nb_saved' => (int) $ad->nb_saved,
            'message' => $isSaved ? 'Advertisement saved.' : 'Advertisement unsaved.',
        ], 200);
    }

    /**
     * Record a share on an advertisement.
     *
     * @param Request $request
     * @param int|string $id
     * @return JsonResponse
     */
    public function recordShare(Request $request, int|string $id): JsonResponse
    {
        $user = $request->user('sanctum') ?? $request->user();
        $userId = $user?->id ?? $request->input('user_id');
        $sharedToUserId = $request->input('shared_to_user_id');
        $channel = $request->input('channel', 'app');

        $ad = Advertisement::find($id);
        if (! $ad) {
            return response()->json([
                'message' => 'Advertisement not found.',
            ], 404);
        }

        UserInteraction::create([
            'user_id' => $userId,
            'type' => UserInteraction::TYPE_SHARE,
            'target_type' => UserInteraction::TARGET_ADVERTISEMENT,
            'target_id' => $ad->id,
            'meta' => [
                'shared_to_user_id' => $sharedToUserId,
                'channel' => $channel,
            ],
        ]);

        $ad->increment('nb_shared');
        $ad->refresh();

        return response()->json([
            'nb_shared' => (int) $ad->nb_shared,
            'message' => 'Advertisement share recorded.',
        ], 200);
    }
}
