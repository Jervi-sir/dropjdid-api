<?php

namespace App\Http\Controllers\Api\Friends;

use App\Http\Controllers\Controller;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FriendRequestController extends Controller
{
    /**
     * List incoming (or outgoing/all) friend requests for the authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user('sanctum')?->id ?? $request->user()?->id ?? (int) $request->query('user_id', 1);
        $type = $request->query('type', 'incoming'); // incoming | outgoing | all
        $perPage = (int) $request->query('per_page', 20);
        $page = (int) $request->query('page', 1);

        $query = Friendship::query()->with(['sender', 'recipient']);

        if ($type === 'outgoing') {
            $query->where('user_id', $userId)->where('status', Friendship::STATUS_PENDING);
        } elseif ($type === 'all') {
            $query->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)->orWhere('friend_id', $userId);
            });
        } else {
            // Default: incoming pending requests
            $query->where('friend_id', $userId)->where('status', Friendship::STATUS_PENDING);
        }

        $paginator = $query->latest()->paginate($perPage, ['*'], 'page', $page);

        $data = $paginator->getCollection()->map(function (Friendship $friendship) use ($userId) {
            $otherUser = (int) $friendship->user_id === (int) $userId
                ? $friendship->recipient
                : $friendship->sender;

            $imageUrl = $otherUser?->image_url ?? '';
            if ($imageUrl && ! str_starts_with($imageUrl, 'http://') && ! str_starts_with($imageUrl, 'https://')) {
                $imageUrl = url($imageUrl);
            }

            $text1 = (string) ($otherUser?->full_name ?? $otherUser?->name ?? $otherUser?->username ?? ('User #' . ($otherUser?->id ?? '')));
            $username = (string) ($otherUser?->username ?? '');
            $text2 = $username !== '' ? ('@' . ltrim($username, '@')) : '';

            return [
                'id' => (int) $friendship->id,
                'status' => $friendship->status,
                'created_at' => $friendship->created_at,
                'user' => [
                    'id' => (int) ($otherUser?->id ?? 0),
                    'text1' => $text1,
                    'text2' => $text2,
                    'image_url' => $imageUrl ?: null,
                ],
            ];
        })->values();

        $nextPage = $paginator->hasMorePages() ? $paginator->currentPage() + 1 : null;

        return response()->json([
            'data' => $data,
            'total' => $paginator->total(),
            'page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'next_page' => $nextPage,
        ], 200);
    }

    /**
     * Show details of a specific friend request.
     *
     * @param Request $request
     * @param int|string $id
     * @return JsonResponse
     */
    public function show(Request $request, int|string $id): JsonResponse
    {
        $userId = $request->user('sanctum')?->id ?? $request->user()?->id ?? (int) $request->query('user_id', 1);

        $friendship = Friendship::query()
            ->with(['sender', 'recipient'])
            ->where(function ($q) use ($id) {
                $q->where('id', $id)
                  ->orWhere('user_id', $id)
                  ->orWhere('friend_id', $id);
            })
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)->orWhere('friend_id', $userId);
            })
            ->first();

        if (! $friendship) {
            return response()->json([
                'message' => 'Friend request not found.',
            ], 404);
        }

        $otherUser = (int) $friendship->user_id === (int) $userId
            ? $friendship->recipient
            : $friendship->sender;

        $imageUrl = $otherUser?->image_url ?? '';
        if ($imageUrl && ! str_starts_with($imageUrl, 'http://') && ! str_starts_with($imageUrl, 'https://')) {
            $imageUrl = url($imageUrl);
        }

        $text1 = (string) ($otherUser?->full_name ?? $otherUser?->name ?? $otherUser?->username ?? ('User #' . ($otherUser?->id ?? '')));
        $username = (string) ($otherUser?->username ?? '');
        $text2 = $username !== '' ? ('@' . ltrim($username, '@')) : '';

        return response()->json([
            'data' => [
                'id' => (int) $friendship->id,
                'status' => $friendship->status,
                'created_at' => $friendship->created_at,
                'user' => [
                    'id' => (int) ($otherUser?->id ?? 0),
                    'text1' => $text1,
                    'text2' => $text2,
                    'image_url' => $imageUrl ?: null,
                ],
            ],
        ], 200);
    }

    /**
     * Send a friend request to a user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function send(Request $request): JsonResponse
    {
        $userId = $request->user('sanctum')?->id ?? $request->user()?->id ?? (int) $request->input('sender_id', 1);
        $targetUserId = (int) ($request->input('user_id') ?? $request->input('friend_id') ?? $request->route('id'));

        if (! $targetUserId || $targetUserId === (int) $userId) {
            return response()->json([
                'message' => 'Invalid user specified.',
            ], 422);
        }

        $targetUser = User::find($targetUserId);
        if (! $targetUser) {
            return response()->json([
                'message' => 'Target user not found.',
            ], 404);
        }

        // Check if there is an existing friendship record
        $existing = Friendship::where(function ($q) use ($userId, $targetUserId) {
            $q->where('user_id', $userId)->where('friend_id', $targetUserId);
        })->orWhere(function ($q) use ($userId, $targetUserId) {
            $q->where('user_id', $targetUserId)->where('friend_id', $userId);
        })->first();

        if ($existing) {
            if ($existing->status === Friendship::STATUS_ACCEPTED) {
                return response()->json([
                    'message' => 'You are already friends with this user.',
                    'friend_status' => 'accepted',
                    'friendship' => $existing,
                ], 200);
            }

            if ($existing->status === Friendship::STATUS_PENDING) {
                if ((int) $existing->user_id === (int) $userId) {
                    return response()->json([
                        'message' => 'Friend request already sent.',
                        'friend_status' => 'pending',
                        'friendship' => $existing,
                    ], 200);
                } else {
                    // Incoming pending request from target: auto-accept
                    $existing->update(['status' => Friendship::STATUS_ACCEPTED]);
                    return response()->json([
                        'message' => 'Friend request accepted.',
                        'friend_status' => 'accepted',
                        'friendship' => $existing,
                    ], 200);
                }
            }

            // If declined, re-open as pending
            $existing->update([
                'user_id' => $userId,
                'friend_id' => $targetUserId,
                'status' => Friendship::STATUS_PENDING,
            ]);

            return response()->json([
                'message' => 'Friend request sent successfully.',
                'friend_status' => 'pending',
                'friendship' => $existing,
            ], 201);
        }

        $friendship = Friendship::create([
            'user_id' => $userId,
            'friend_id' => $targetUserId,
            'status' => Friendship::STATUS_PENDING,
        ]);

        return response()->json([
            'message' => 'Friend request sent successfully.',
            'friend_status' => 'pending',
            'friendship' => $friendship,
        ], 201);
    }

    /**
     * Accept an incoming friend request.
     *
     * @param Request $request
     * @param int|string $id Friendship ID or requester User ID
     * @return JsonResponse
     */
    public function accept(Request $request, int|string $id): JsonResponse
    {
        $userId = $request->user('sanctum')?->id ?? $request->user()?->id ?? (int) $request->input('user_id', 1);

        $friendship = Friendship::where(function ($q) use ($id) {
            $q->where('id', $id)->orWhere('user_id', $id);
        })
        ->where('friend_id', $userId)
        ->where('status', Friendship::STATUS_PENDING)
        ->first();

        if (! $friendship) {
            // Also check reverse if id is friendship ID where user is sender/recipient
            $friendship = Friendship::where('id', $id)
                ->where('friend_id', $userId)
                ->first();
        }

        if (! $friendship) {
            return response()->json([
                'message' => 'Pending friend request not found.',
            ], 404);
        }

        $friendship->update(['status' => Friendship::STATUS_ACCEPTED]);

        return response()->json([
            'message' => 'Friend request accepted.',
            'friend_status' => 'accepted',
            'friendship' => $friendship,
        ], 200);
    }

    /**
     * Reject / decline an incoming friend request.
     *
     * @param Request $request
     * @param int|string $id Friendship ID or requester User ID
     * @return JsonResponse
     */
    public function reject(Request $request, int|string $id): JsonResponse
    {
        $userId = $request->user('sanctum')?->id ?? $request->user()?->id ?? (int) $request->input('user_id', 1);

        $friendship = Friendship::where(function ($q) use ($id) {
            $q->where('id', $id)->orWhere('user_id', $id);
        })
        ->where('friend_id', $userId)
        ->where('status', Friendship::STATUS_PENDING)
        ->first();

        if (! $friendship) {
            $friendship = Friendship::where('id', $id)
                ->where('friend_id', $userId)
                ->first();
        }

        if (! $friendship) {
            return response()->json([
                'message' => 'Pending friend request not found.',
            ], 404);
        }

        $friendship->delete();

        return response()->json([
            'message' => 'Friend request rejected.',
            'friend_status' => null,
        ], 200);
    }

    /**
     * Cancel an outgoing pending friend request.
     *
     * @param Request $request
     * @param int|string $id Friendship ID or target User ID
     * @return JsonResponse
     */
    public function cancel(Request $request, int|string $id): JsonResponse
    {
        $userId = $request->user('sanctum')?->id ?? $request->user()?->id ?? (int) $request->input('user_id', 1);

        $friendship = Friendship::where(function ($q) use ($id) {
            $q->where('id', $id)->orWhere('friend_id', $id);
        })
        ->where('user_id', $userId)
        ->where('status', Friendship::STATUS_PENDING)
        ->first();

        if (! $friendship) {
            return response()->json([
                'message' => 'Pending friend request not found.',
            ], 404);
        }

        $friendship->delete();

        return response()->json([
            'message' => 'Friend request cancelled.',
            'friend_status' => null,
        ], 200);
    }

    /**
     * Unfriend / remove friendship with a user.
     *
     * @param Request $request
     * @param int|string|null $id Friend User ID or Friendship ID
     * @return JsonResponse
     */
    public function unfriend(Request $request, int|string|null $id = null): JsonResponse
    {
        $userId = $request->user('sanctum')?->id ?? $request->user()?->id ?? (int) $request->input('user_id', 1);
        $friendId = (int) ($id ?? $request->input('friend_id') ?? $request->input('user_id'));

        if (! $friendId) {
            return response()->json([
                'message' => 'Friend ID required.',
            ], 422);
        }

        $deleted = Friendship::where(function ($q) use ($userId, $friendId) {
            $q->where('user_id', $userId)->where('friend_id', $friendId);
        })->orWhere(function ($q) use ($userId, $friendId) {
            $q->where('user_id', $friendId)->where('friend_id', $userId);
        })->orWhere(function ($q) use ($friendId, $userId) {
            $q->where('id', $friendId)->where(function ($sq) use ($userId) {
                $sq->where('user_id', $userId)->orWhere('friend_id', $userId);
            });
        })->delete();

        return response()->json([
            'message' => 'Friend removed successfully.',
            'friend_status' => null,
            'success' => (bool) $deleted,
        ], 200);
    }
}
