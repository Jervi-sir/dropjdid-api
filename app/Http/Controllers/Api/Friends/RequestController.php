<?php

namespace App\Http\Controllers\Api\Friends;

use App\Http\Controllers\Controller;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    public function __invoke(Request $request, User $user): JsonResponse
    {
        $authUser = $request->user();

        abort_if($authUser === null, 401);
        abort_if($authUser->id === $user->id, 422, 'You cannot manage friendship with yourself.');

        $validated = $request->validate([
            'action' => ['required', 'in:send,accept,reject,unfriend'],
        ]);

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

        match ($validated['action']) {
            'send' => $this->sendRequest($friendship, $authUser->id, $user->id),
            'accept' => $this->acceptRequest($friendship, $authUser->id),
            'reject' => $this->rejectRequest($friendship, $authUser->id),
            'unfriend' => $this->unfriend($friendship),
        };

        $updatedFriendship = Friendship::query()
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
            'status' => 'pending',
        ]);
    }

    private function acceptRequest(?Friendship $friendship, int $authUserId): void
    {
        abort_if($friendship === null || $friendship->status !== 'pending' || $friendship->receiver_id !== $authUserId, 422, 'No incoming request to accept.');

        $friendship->update([
            'status' => 'accepted',
            'accepted_at' => now(),
            'rejected_at' => null,
        ]);
    }

    private function rejectRequest(?Friendship $friendship, int $authUserId): void
    {
        abort_if($friendship === null || $friendship->status !== 'pending' || $friendship->receiver_id !== $authUserId, 422, 'No incoming request to reject.');

        $friendship->delete();
    }

    private function unfriend(?Friendship $friendship): void
    {
        abort_if($friendship === null || ! in_array($friendship->status, ['accepted', 'pending'], true), 422, 'No friendship to remove.');

        $friendship->delete();
    }

    private function resolveFriendStatus(?Friendship $friendship, int $authUserId, int $profileUserId): string
    {
        if ($authUserId === $profileUserId || $friendship === null) {
            return 'none';
        }

        return match ($friendship->status) {
            'accepted' => 'friends',
            'pending' => $friendship->sender_id === $authUserId ? 'requested' : 'request_received',
            'blocked' => 'blocked',
            default => 'none',
        };
    }

    private function formatFriendRequest(?Friendship $friendship, int $authUserId, int $profileUserId): ?array
    {
        if ($authUserId === $profileUserId || $friendship === null || $friendship->status !== 'pending') {
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
