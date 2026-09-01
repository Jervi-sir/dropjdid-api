<?php

namespace App\Http\Controllers\Api\Creator;

use App\Http\Controllers\Controller;
use App\Models\CreatorFollower;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FollowersController extends Controller
{
    /**
     * Get list of followers for a creator matching FriendType interface:
     * - id: number
     * - image_url: string
     * - text1: string
     * - text2: string
     */
    public function __invoke(Request $request): JsonResponse
    {
        $creatorId = $request->user('sanctum')?->id
            ?? $request->user()?->id
            ?? $request->query('creator_id')
            ?? $request->query('user_id');

        if (! $creatorId) {
            // Development fallback to the first user with 'creator' role
            $creator = User::whereHas('roles', fn ($q) => $q->where('code', 'creator'))->first() ?? User::first();
            $creatorId = $creator?->id;
        }

        if ($creatorId) {
            $followerUserIds = CreatorFollower::where('creator_id', $creatorId)->pluck('user_id');
            $query = User::whereIn('id', $followerUserIds);
        } else {
            $query = User::query();
        }

        $query->where('is_active', true);

        // Search query support
        $search = trim((string) ($request->query('search') ?? $request->query('query') ?? $request->query('q') ?? $request->query('keyword') ?? ''));
        if ($search !== '') {
            $cleanSearch = ltrim($search, '@');
            $term = '%'.strtolower($cleanSearch).'%';
            $query->where(function ($q) use ($term) {
                $q->where('username', 'ILIKE', $term)
                    ->orWhere('full_name', 'ILIKE', $term)
                    ->orWhere('email', 'ILIKE', $term);
            });
        }

        $perPage = max(1, min(100, (int) $request->query('per_page', 20)));
        $page = max(1, (int) $request->query('page', 1));

        $paginator = $query->latest('created_at')->paginate($perPage, ['*'], 'page', $page);

        $data = $paginator->getCollection()->map(function (User $follower) {
            $imageUrl = $follower->image_url ?? '';
            if ($imageUrl && ! str_starts_with($imageUrl, 'http://') && ! str_starts_with($imageUrl, 'https://')) {
                $imageUrl = url($imageUrl);
            }

            $fullName = (string) ($follower->full_name ?? $follower->name ?? $follower->username ?? 'User #'.$follower->id);
            $username = (string) ($follower->username ? '@'.ltrim($follower->username, '@') : ($follower->email ?? ''));

            return [
                'id' => (int) $follower->id,
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
