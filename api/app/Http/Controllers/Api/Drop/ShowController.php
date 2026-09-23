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
            ->with(['creator', 'images', 'mainImage', 'likedUsers', 'savedUsers'])
            ->withCount(['likedUsers', 'savedUsers', 'products'])
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

        $text1 = (string) ($drop->title ?? 'Drop #' . $drop->id);
        $text2 = (string) ($drop->creator ? '@' . ltrim($drop->creator->username, '@') : ($drop->description ?? ''));

        $isLiked = false;
        $isSaved = false;
        $isReposted = false;
        $isFollowing = false;

        if ($userId) {
            $isLiked = $drop->likedUsers->contains('id', $userId);
            $isSaved = $drop->savedUsers->contains('id', $userId);
            $isReposted = UserInteraction::query()
                ->where('user_id', $userId)
                ->where('type', UserInteraction::TYPE_REPOST)
                ->where('target_type', UserInteraction::TARGET_DROP)
                ->where('target_id', $drop->id)
                ->exists();

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

        $data = [
            'id' => (int) $drop->id,
            'creator_id' => $drop->creator_id ? (int) $drop->creator_id : null,
            'user_id' => $drop->creator_id ? (int) $drop->creator_id : null,
            'image_urls' => $imageUrls,
            'text1' => $text1,
            'text2' => $text2,
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
