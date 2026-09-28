<?php

namespace App\Http\Controllers\Api\Drop;

use App\Http\Controllers\Controller;
use App\Models\CreatorFollower;
use App\Models\Drop;
use App\Models\UserInteraction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShowController extends Controller
{
    /**
     * Get drop details matching DropType schema.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $userId = $request->user('sanctum')?->id ?? $request->user()?->id ?? $request->input('user_id');

        $drop = Drop::query()
            ->where('id', $id)
            ->with(['creator', 'images', 'mainImage'])
            ->withCount(['likes as liked_users_count', 'saves as saved_users_count', 'products'])
            ->first();

        if (! $drop) {
            return response()->json([
                'message' => 'Drop not found.',
            ], 404);
        }

        // Collect all images
        $imageUrls = $drop->images->map(function ($img) {
            $url = $img->image;
            if ($url && ! str_starts_with($url, 'http://') && ! str_starts_with($url, 'https://')) {
                return url($url);
            }
            return (string) $url;
        })->filter()->values()->all();

        if (empty($imageUrls)) {
            $mainImg = $drop->mainImage?->image;
            if ($mainImg) {
                $imageUrls[] = (str_starts_with($mainImg, 'http://') || str_starts_with($mainImg, 'https://'))
                    ? $mainImg
                    : url($mainImg);
            }
        }

        $title = (string) ($drop->title ?? '#' . $drop->id);
        $text1 = str_starts_with(strtolower($title), 'drop:') ? $title : 'drop: ' . $title;
        $text2 = (string) ($drop->creator ? '@' . ltrim($drop->creator->username, '@') : ($drop->description ?? ''));

        $isLiked = false;
        $isSaved = false;
        $isReposted = false;
        $isFollowing = false;

        if ($userId) {
            $userInteractions = UserInteraction::query()
                ->where('user_id', $userId)
                ->where('target_type', UserInteraction::TARGET_DROP)
                ->where('target_id', $drop->id)
                ->pluck('type')
                ->all();

            $isLiked = in_array(UserInteraction::TYPE_LIKE, $userInteractions, true);
            $isSaved = in_array(UserInteraction::TYPE_SAVE, $userInteractions, true);
            $isReposted = in_array(UserInteraction::TYPE_REPOST, $userInteractions, true);

            if ($drop->creator_id) {
                $isFollowing = CreatorFollower::query()
                    ->where('user_id', $userId)
                    ->where('creator_id', $drop->creator_id)
                    ->exists();
            }
        }

        $nbShares = UserInteraction::query()
            ->where('type', UserInteraction::TYPE_SHARE)
            ->where('target_type', UserInteraction::TARGET_DROP)
            ->where('target_id', $drop->id)
            ->count();

        $nbReposts = UserInteraction::query()
            ->where('type', UserInteraction::TYPE_REPOST)
            ->where('target_type', UserInteraction::TARGET_DROP)
            ->where('target_id', $drop->id)
            ->count();

        $nbFollowers = 0;
        if ($drop->creator_id) {
            $nbFollowers = CreatorFollower::query()
                ->where('creator_id', $drop->creator_id)
                ->count();
        }

        $rejectionReasonData = $drop->rejection_reason;
        $rejectionReasonText = is_array($rejectionReasonData)
            ? ($rejectionReasonData['reason'] ?? '')
            : (is_string($rejectionReasonData) ? $rejectionReasonData : '');

        $data = [
            'id' => (int) $drop->id,
            'creator_id' => $drop->creator_id ? (int) $drop->creator_id : null,
            'user_id' => $drop->creator_id ? (int) $drop->creator_id : null,
            'image_urls' => $imageUrls,
            'text1' => $text1,
            'text2' => $text2,
            'drop_status' => (string) ($drop->drop_status ?? 'published'),
            'rejection_reason' => $rejectionReasonData,
            'rejection_message' => $rejectionReasonText,
            'stats' => [
                'nb_liked' => (int) ($drop->liked_users_count ?? 0),
                'nb_saved' => (int) ($drop->saved_users_count ?? 0),
                'nb_products' => (int) ($drop->products_count ?? 0),
                'nb_shares' => (int) $nbShares,
                'nb_reposted' => (int) $nbReposts,
                'nb_reposts' => (int) $nbReposts,
                'nb_followers' => (int) $nbFollowers,
                'nb_follower' => (int) $nbFollowers,
            ],
            'is_saved' => (bool) $isSaved,
            'is_liked' => (bool) $isLiked,
            'is_reposted' => (bool) $isReposted,
            'is_following_creator' => (bool) $isFollowing,
            'nb_reposted' => (int) $nbReposts,
            'nb_reposts' => (int) $nbReposts,
            'nb_followers' => (int) $nbFollowers,
            'nb_follower' => (int) $nbFollowers,
        ];

        return response()->json([
            'data' => $data,
        ], 200);
    }
}
