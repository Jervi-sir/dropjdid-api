<?php

namespace App\Http\Controllers\Api\Creator;

use App\Http\Controllers\Controller;
use App\Models\CreatorFollower;
use App\Models\Notification;
use App\Models\NotificationType;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SendFollowController extends Controller
{
    /**
     * Follow, unfollow, or toggle following a creator.
     *
     * Supports:
     * - Action 'follow': follow target creator
     * - Action 'unfollow': unfollow target creator
     * - Action 'toggle' (default): follow if not followed, unfollow if followed
     *
     * @param Request $request
     * @param int|string|null $id Optional creator ID from URL route
     * @return JsonResponse
     */
    public function __invoke(Request $request, int|string|null $id = null): JsonResponse
    {
        $userId = $request->user('sanctum')?->id
            ?? $request->user()?->id
            ?? (int) $request->header('X-User-Id')
            ?? (int) $request->input('user_id', 1);

        $creatorId = (int) ($id ?? $request->input('creator_id') ?? $request->input('id'));

        if (! $creatorId) {
            return response()->json([
                'success' => false,
                'message' => 'Creator ID is required.',
            ], 422);
        }

        if ($userId === $creatorId) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot follow yourself.',
            ], 422);
        }

        $creator = User::find($creatorId);
        if (! $creator) {
            return response()->json([
                'success' => false,
                'message' => 'Creator not found.',
            ], 404);
        }

        $action = strtolower((string) $request->input('action', 'toggle')); // 'follow', 'unfollow', or 'toggle'

        $existingFollow = CreatorFollower::where('user_id', $userId)
            ->where('creator_id', $creatorId)
            ->first();

        // Handle explicit 'unfollow' or toggle when already following
        if ($action === 'unfollow' || ($action === 'toggle' && $existingFollow)) {
            if ($existingFollow) {
                $existingFollow->delete();
            }

            $followersCount = CreatorFollower::where('creator_id', $creatorId)->count();

            return response()->json([
                'success' => true,
                'is_following' => false,
                'creator_follow_status' => null,
                'message' => "Unfollowed {$creator->name} successfully.",
                'followers_count' => $followersCount,
            ], 200);
        }

        // Handle 'follow' or toggle when not following
        if (! $existingFollow) {
            $followRecord = CreatorFollower::create([
                'user_id' => $userId,
                'creator_id' => $creatorId,
            ]);

            // Create in-app notification for the creator
            try {
                $followerType = NotificationType::where('code', 'follower')->first();
                if ($followerType) {
                    $currentUser = User::find($userId);
                    $followerName = $currentUser?->full_name ?? $currentUser?->username ?? 'Someone';

                    Notification::create([
                        'notification_type_id' => $followerType->id,
                        'user_id' => $creatorId,
                        'notifiable_type' => CreatorFollower::class,
                        'notifiable_id' => $followRecord->id,
                        'data' => [
                            'text1' => $followerName,
                            'text2' => 'started following you',
                            'image_url' => $currentUser?->image_url,
                        ],
                    ]);
                }
            } catch (\Throwable $e) {
                // Ignore notification creation failure to avoid blocking follow action
            }
        }

        $followersCount = CreatorFollower::where('creator_id', $creatorId)->count();

        return response()->json([
            'success' => true,
            'is_following' => true,
            'creator_follow_status' => 'followed',
            'message' => "Now following {$creator->name}.",
            'followers_count' => $followersCount,
        ], 200);
    }
}
