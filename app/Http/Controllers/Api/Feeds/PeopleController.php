<?php

namespace App\Http\Controllers\Api\Feeds;

use App\Http\Controllers\Controller;
use App\Models\CreatorFollower;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PeopleController extends Controller
{
    /**
     * Search and paginate users/creators.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $currentUserId = $request->user('sanctum')?->id ?? $request->user()?->id;
        $search = trim((string) ($request->query('search') ?? $request->query('query') ?? $request->query('q') ?? $request->query('keyword') ?? ''));
        $role = $request->query('role'); // e.g. 'creator' or null
        $perPage = max(1, min(100, (int) $request->query('per_page', 20)));
        $page = max(1, (int) $request->query('page', 1));

        $query = User::query()
            ->where('is_active', '!=', false);

        // Filter by role if requested
        if ($role) {
            $query->whereHas('roles', function ($q) use ($role) {
                $q->where('code', $role);
            });
        }

        // Apply search query
        if ($search !== '') {
            $cleanSearch = ltrim($search, '@');
            $term = '%' . strtolower($cleanSearch) . '%';

            $query->where(function ($q) use ($term) {
                $q->where('username', 'ILIKE', $term)
                  ->orWhere('full_name', 'ILIKE', $term)
                  ->orWhere('email', 'ILIKE', $term);
            });
        }

        // Order by relevance or newest
        $query->orderBy('created_at', 'desc');

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        // Preload following status if current user is logged in
        $followedUserIds = collect();
        if ($currentUserId) {
            $userIdsOnPage = $paginator->getCollection()->pluck('id');
            $followedUserIds = CreatorFollower::where('user_id', $currentUserId)
                ->whereIn('creator_id', $userIdsOnPage)
                ->pluck('creator_id');
        }

        $data = $paginator->getCollection()->map(function (User $user) use ($followedUserIds) {
            $imageUrl = $user->image_url ?? '';
            if ($imageUrl && ! str_starts_with($imageUrl, 'http://') && ! str_starts_with($imageUrl, 'https://')) {
                $imageUrl = url($imageUrl);
            }

            $fullName = (string) ($user->full_name ?? $user->name ?? $user->username ?? 'User #' . $user->id);
            $username = (string) ($user->username ?? '');

            return [
                'id' => (int) $user->id,
                'image_url' => (string) $imageUrl,
                'text1' => $fullName,
                'text2' => '@' . ltrim($username, '@'),
                'username' => $username,
                'full_name' => $fullName,
                'is_following' => $followedUserIds->contains($user->id),
            ];
        })->values();

        $nextPage = $paginator->hasMorePages() ? $paginator->currentPage() + 1 : null;

        return response()->json([
            'data' => $data,
            'current_page' => $paginator->currentPage(),
            'next_page' => $nextPage,
            'total' => $paginator->total(),
        ], 200);
    }
}
