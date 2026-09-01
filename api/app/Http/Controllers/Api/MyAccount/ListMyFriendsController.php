<?php

namespace App\Http\Controllers\Api\MyAccount;

use App\Http\Controllers\Controller;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListMyFriendsController extends Controller
{
    /**
     * Get list of accepted friends for the authenticated user matching FriendType interface.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
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

            $query = User::whereIn('id', $friendIds);
        } else {
            $query = User::query();
        }

        $query->where('is_active', true);

        // Search query support
        $search = trim((string) ($request->query('search') ?? $request->query('query') ?? $request->query('q') ?? $request->query('keyword') ?? ''));
        if ($search !== '') {
            $cleanSearch = ltrim($search, '@');
            $term = '%' . strtolower($cleanSearch) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('username', 'ILIKE', $term)
                  ->orWhere('full_name', 'ILIKE', $term)
                  ->orWhere('email', 'ILIKE', $term);
            });
        }

        $perPage = max(1, min(100, (int) $request->query('per_page', 20)));
        $page = max(1, (int) $request->query('page', 1));

        $paginator = $query->latest('created_at')->paginate($perPage, ['*'], 'page', $page);

        $data = $paginator->getCollection()->map(function (User $user) {
            $imageUrl = $user->image_url ?? '';
            if ($imageUrl && ! str_starts_with($imageUrl, 'http://') && ! str_starts_with($imageUrl, 'https://')) {
                $imageUrl = url($imageUrl);
            }

            $fullName = (string) ($user->full_name ?? $user->name ?? $user->username ?? 'User #' . $user->id);
            $username = (string) ($user->username ? '@' . ltrim($user->username, '@') : ($user->email ?? ''));

            return [
                'id' => (int) $user->id,
                'image_url' => (string) $imageUrl,
                'text1' => (string) $fullName,
                'text2' => (string) $username,
            ];
        })->values();

        $nextPage = $paginator->hasMorePages() ? $paginator->currentPage() + 1 : null;

        return response()->json([
            'data' => $data,
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'next_page' => $nextPage,
        ], 200);
    }
}
