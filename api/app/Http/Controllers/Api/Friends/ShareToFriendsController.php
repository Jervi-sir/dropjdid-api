<?php

namespace App\Http\Controllers\Api\Friends;

use App\Http\Controllers\Controller;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShareToFriendsController extends Controller
{
    /**
     * Get list of friends for the authenticated user with search and pagination.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user('sanctum')?->id ?? $request->user()?->id ?? $request->query('user_id');

        if ($userId) {
            $sentFriendIds = Friendship::where('user_id', $userId)
                ->where('status', Friendship::STATUS_ACCEPTED)
                ->pluck('friend_id');

            $receivedFriendIds = Friendship::where('friend_id', $userId)
                ->where('status', Friendship::STATUS_ACCEPTED)
                ->pluck('user_id');

            $friendIds = $sentFriendIds->merge($receivedFriendIds)->unique();

            if ($friendIds->isNotEmpty()) {
                $query = User::whereIn('id', $friendIds);
            } else {
                // If user has no accepted friendship records yet, return suggested active users (excluding current user)
                $query = User::where('id', '!=', $userId);
            }
        } else {
            $query = User::query();
        }

        $query->where('is_active', true);

        // Search ability
        $search = trim((string) ($request->query('search') ?? $request->query('query') ?? $request->query('q') ?? $request->query('keyword') ?? ''));
        if ($search !== '') {
            $term = '%' . strtolower($search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('username', 'ILIKE', $term)
                  ->orWhere('full_name', 'ILIKE', $term)
                  ->orWhere('email', 'ILIKE', $term);
            });
        }

        $perPage = (int) $request->query('per_page', 20);
        $page = (int) $request->query('page', 1);

        $paginator = $query->latest()->paginate($perPage, ['*'], 'page', $page);

        $data = $paginator->getCollection()->map(function (User $user) {
            $imageUrl = $user->image_url ?? '';

            if ($imageUrl && ! str_starts_with($imageUrl, 'http://') && ! str_starts_with($imageUrl, 'https://')) {
                $imageUrl = url($imageUrl);
            }

            $text1 = $user->full_name ?? $user->username ?? 'User #' . $user->id;
            $text2 = $user->username ? '@' . $user->username : ($user->email ?? '');

            return [
                'id' => (int) $user->id,
                'image_url' => (string) $imageUrl,
                'text1' => (string) $text1,
                'text2' => (string) $text2,
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
}
