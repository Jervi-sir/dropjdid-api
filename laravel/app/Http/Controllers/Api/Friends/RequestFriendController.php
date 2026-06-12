<?php

namespace App\Http\Controllers\Api\Friends;

use App\Http\Controllers\Controller;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RequestFriendController extends Controller
{
    public function send(Request $request, int $user_id): JsonResponse
    {
        $authUser = $request->user();
        $user = User::find($user_id);

        $this->validateFriendshipTarget($authUser, $user);

        $friendship = $this->findFriendship($authUser->id, $user->id);

        $this->sendRequest($friendship, $authUser->id, $user->id);

        return $this->buildFriendshipResponse($authUser, $user);
    }

    public function cancel(Request $request, int $user_id): JsonResponse
    {
        $authUser = $request->user();
        $user = User::find($user_id);

        $this->validateFriendshipTarget($authUser, $user);

        $friendship = $this->findFriendship($authUser->id, $user->id);

        abort_if($friendship === null || $friendship->status !== Friendship::STATUS_PENDING || $friendship->sender_id !== $authUser->id, 422, 'No outgoing request to cancel.');

        $friendship->delete();

        return $this->buildFriendshipResponse($authUser, $user);
    }

    public function accept(Request $request, int $user_id): JsonResponse
    {
        $authUser = $request->user();
        $user = User::find($user_id);

        $this->validateFriendshipTarget($authUser, $user);

        $friendship = $this->findFriendship($authUser->id, $user->id);

        $this->acceptRequest($friendship, $authUser->id);

        return $this->buildFriendshipResponse($authUser, $user);
    }

    public function reject(Request $request, int $user_id): JsonResponse
    {
        $authUser = $request->user();
        $user = User::find($user_id);

        $this->validateFriendshipTarget($authUser, $user);

        $friendship = $this->findFriendship($authUser->id, $user->id);

        abort_if($friendship === null || $friendship->status !== Friendship::STATUS_PENDING || $friendship->receiver_id !== $authUser->id, 422, 'No incoming request to reject.');

        $friendship->delete();

        return $this->buildFriendshipResponse($authUser, $user);
    }

    public function unfriend(Request $request, int $user_id): JsonResponse
    {
        $authUser = $request->user();
        $user = User::find($user_id);

        $this->validateFriendshipTarget($authUser, $user);

        $friendship = $this->findFriendship($authUser->id, $user->id);

        abort_if($friendship === null || $friendship->status !== Friendship::STATUS_ACCEPTED, 422, 'No friendship to remove.');
        $friendship->delete();

        return $this->buildFriendshipResponse($authUser, $user);
    }

    private function validateFriendshipTarget(?User $authUser, User $user): void
    {
        abort_if($authUser === null, 401);
        abort_if($authUser->id === $user->id, 422, 'You cannot manage friendship with yourself.');
    }

    private function findFriendship(int $authUserId, int $profileUserId): ?Friendship
    {
        return Friendship::query()
            ->where(function (Builder $query) use ($authUserId, $profileUserId): void {
                $query
                    ->where('sender_id', $authUserId)
                    ->where('receiver_id', $profileUserId);
            })
            ->orWhere(function (Builder $query) use ($authUserId, $profileUserId): void {
                $query
                    ->where('sender_id', $profileUserId)
                    ->where('receiver_id', $authUserId);
            })
            ->first();
    }

    private function buildFriendshipResponse(User $authUser, User $user): JsonResponse
    {
        $updatedFriendship = $this->findFriendship($authUser->id, $user->id);

        return response()->json([
            'message' => 'Friend request updated successfully.',
            'data' => [
                'friend_status' => $this->resolveFriendStatus($updatedFriendship, $authUser->id, $user->id),
                'friend_request' => $this->formatFriendRequest($updatedFriendship, $authUser->id, $user->id),
            ],
        ]);
    }

    private function sendRequest(?Friendship $friendship, int $authUserId, int $profileUserId): void
    {
        abort_if($friendship !== null, 422, 'Friendship already exists.');

        Friendship::query()->create([
            'sender_id' => $authUserId,
            'receiver_id' => $profileUserId,
            'status' => Friendship::STATUS_PENDING,
        ]);
    }

    private function acceptRequest(?Friendship $friendship, int $authUserId): void
    {
        abort_if($friendship === null || $friendship->status !== Friendship::STATUS_PENDING || $friendship->receiver_id !== $authUserId, 422, 'No incoming request to accept.');

        $friendship->update([
            'status' => Friendship::STATUS_ACCEPTED,
            'accepted_at' => now(),
            'rejected_at' => null,
        ]);
    }

    private function resolveFriendStatus(?Friendship $friendship, int $authUserId, int $profileUserId): string
    {
        if ($authUserId === $profileUserId || $friendship === null) {
            return 'none';
        }

        return match ($friendship->status) {
            Friendship::STATUS_ACCEPTED => 'friends',
            Friendship::STATUS_PENDING => $friendship->sender_id === $authUserId ? 'requested' : 'request_received',
            Friendship::STATUS_BLOCKED => Friendship::STATUS_BLOCKED,
            default => 'none',
        };
    }

    private function formatFriendRequest(?Friendship $friendship, int $authUserId, int $profileUserId): ?array
    {
        if ($authUserId === $profileUserId || $friendship === null || $friendship->status !== Friendship::STATUS_PENDING) {
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
