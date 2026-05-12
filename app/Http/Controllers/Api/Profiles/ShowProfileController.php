<?php

namespace App\Http\Controllers\Api\Profiles;

use App\Http\Controllers\Controller;
use App\Models\CreatorFollower;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShowProfileController extends Controller
{
    public function __invoke(Request $request, User $user): JsonResponse
    {
        $authUser = $request->user();

        $user->load(['contacts.socialPlatform']);

        $friendship = null;
        $isFollowing = false;

        if ($authUser !== null) {
            $friendship = Friendship::query()
                ->where(function (Builder $query) use ($authUser, $user): void {
                    $query
                        ->where('sender_id', $authUser->id)
                        ->where('receiver_id', $user->id);
                })
                ->orWhere(function (Builder $query) use ($authUser, $user): void {
                    $query
                        ->where('sender_id', $user->id)
                        ->where('receiver_id', $authUser->id);
                })
                ->first();

            $isFollowing = CreatorFollower::query()
                ->where('user_id', $authUser->id)
                ->where('creator_id', $user->id)
                ->exists();
        }

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->username,
                'username' => $user->username,
                'image' => $user->image,
                'is_me' => $authUser?->id === $user->id,
                'friend_status' => $this->resolveFriendStatus($friendship, $authUser?->id, $user->id),
                'friend_request' => $this->formatFriendRequest($friendship, $authUser?->id, $user->id),
                'is_following' => $isFollowing,
                'contacts' => $user->contacts->map(fn ($contact): array => [
                    'id' => $contact->id,
                    'url' => $contact->url,
                    'platform' => [
                        'id' => $contact->socialPlatform?->id,
                        'name' => $contact->socialPlatform?->code === null ? null : ucfirst($contact->socialPlatform->code),
                    ],
                ])->values()->all(),
            ],
        ]);
    }

    private function resolveFriendStatus(?Friendship $friendship, ?int $authUserId, int $profileUserId): string
    {
        if ($authUserId === null || $authUserId === $profileUserId || $friendship === null) {
            return 'none';
        }

        return match ($friendship->status) {
            'accepted' => 'friends',
            'pending' => $friendship->sender_id === $authUserId ? 'requested' : 'request_received',
            'blocked' => 'blocked',
            default => 'none',
        };
    }

    private function formatFriendRequest(?Friendship $friendship, ?int $authUserId, int $profileUserId): ?array
    {
        if ($authUserId === null || $authUserId === $profileUserId || $friendship === null || $friendship->status !== 'pending') {
            return null;
        }

        return [
            'id' => $friendship->id,
            'status' => $friendship->status,
            'type' => $friendship->sender_id === $authUserId ? 'outgoing' : 'incoming',
            'sender_id' => $friendship->sender_id,
            'receiver_id' => $friendship->receiver_id,
        ];
    }
}
