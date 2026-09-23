<?php

namespace App\Http\Controllers\Api\People;

use App\Http\Controllers\Controller;
use App\Models\CreatorFollower;
use App\Models\Drop;
use App\Models\User;
use App\Models\UserInteraction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RepostedDropsController extends Controller
{
    /**
     * Get drops reposted by this user/profile.
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
            ->where('target_type', UserInteraction::TARGET_DROP)
            ->latest('id');

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);
        $interactions = collect($paginator->items());

        $dropIds = $interactions->pluck('target_id')->filter()->unique()->values()->all();

        $drops = ! empty($dropIds)
            ? Drop::query()
                ->whereIn('id', $dropIds)
                ->with(['creator', 'images', 'mainImage', 'likedUsers', 'savedUsers'])
                ->get()
                ->keyBy('id')
            : collect();

        $data = $interactions->map(function (UserInteraction $interaction) use ($drops, $currentUserId) {
            /** @var Drop|null $drop */
            $drop = $drops->get($interaction->target_id);

            if (! $drop) {
                return null;
            }

            $imageUrl = '';
            $mainImg = $drop->mainImage?->image;
            if ($mainImg) {
                $imageUrl = $mainImg;
            } elseif ($drop->images->isNotEmpty()) {
                $imageUrl = $drop->images->first()->image;
            }

            if ($imageUrl && ! str_starts_with($imageUrl, 'http://') && ! str_starts_with($imageUrl, 'https://')) {
                $imageUrl = url($imageUrl);
            }

            $creatorHandle = '@' . ltrim((string) ($drop->creator?->username ?? $drop->creator?->name ?? 'creator'), '@');

            $isLiked = false;
            $isSaved = false;
            $isReposted = false;
            $isFollowingCreator = false;

            if ($currentUserId) {
                $isLiked = $drop->likedUsers ? $drop->likedUsers->contains('id', $currentUserId) : false;
                $isSaved = $drop->savedUsers ? $drop->savedUsers->contains('id', $currentUserId) : false;
                $isReposted = UserInteraction::query()
                    ->where('user_id', $currentUserId)
                    ->where('type', UserInteraction::TYPE_REPOST)
                    ->where('target_type', UserInteraction::TARGET_DROP)
                    ->where('target_id', $drop->id)
                    ->exists();

                if ($drop->creator_id) {
                    $isFollowingCreator = CreatorFollower::query()
                        ->where('user_id', $currentUserId)
                        ->where('creator_id', $drop->creator_id)
                        ->exists();
                }
            }

            return [
                'id' => (int) $drop->id,
                'interaction_id' => (int) $interaction->id,
                'image_url' => (string) ($imageUrl ?? ''),
                'text1' => (string) ($drop->title ?? ('Drop #' . $drop->id)),
                'text2' => (string) $creatorHandle,
                'drop_status' => (string) ($drop->drop_status ?? 'published'),
                'creator_id' => $drop->creator_id ? (int) $drop->creator_id : null,
                'quote' => $interaction->meta['quote'] ?? null,
                'reposted_at' => $interaction->created_at,
                'created_at' => $drop->created_at,
                'is_saved' => (bool) $isSaved,
                'is_liked' => (bool) $isLiked,
                'is_reposted' => (bool) $isReposted,
                'is_following_creator' => (bool) $isFollowingCreator,
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
