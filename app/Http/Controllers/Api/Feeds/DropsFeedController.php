<?php

namespace App\Http\Controllers\Api\Feeds;

use App\Http\Controllers\Controller;
use App\Models\CreatorFollower;
use App\Models\Drop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DropsFeedController extends Controller
{
    /**
     * Get drops feed with selected filter/target, optional search query, and pagination.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        // Support both 'target' and 'filter' query parameters
        $rawTarget = (string) ($request->query('target') ?? $request->query('filter') ?? 'for-you');

        // Normalize hyphenated or underscored variants
        $target = match (strtolower(trim($rawTarget))) {
            'creator-i-follow', 'creator_i_follow', 'createor_i_follow', 'createor-i-follow' => 'creator-i-follow',
            'trending' => 'trending',
            default => 'for-you',
        };

        $query = Drop::query()
            ->with(['creator', 'mainImage', 'images']);

        // Filter out drafts and rejected drops
        $query->where(function ($q) {
            $q->where('drop_status', 'published')
              ->orWhereNull('drop_status');
        });

        // Search query support
        $search = trim((string) ($request->query('search') ?? $request->query('query') ?? $request->query('q') ?? $request->query('keyword') ?? ''));
        if ($search !== '') {
            $term = '%' . strtolower($search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('title', 'ILIKE', $term)
                  ->orWhere('description', 'ILIKE', $term)
                  ->orWhereHas('creator', function ($creatorQuery) use ($term) {
                      $creatorQuery->where('username', 'ILIKE', $term)
                                   ->orWhere('full_name', 'ILIKE', $term);
                  });
            });
        }

        // Apply target / filter logic
        if ($target === 'creator-i-follow') {
            $userId = $request->user('sanctum')?->id ?? $request->user()?->id ?? $request->query('user_id');

            if ($userId) {
                $followedCreatorIds = CreatorFollower::where('user_id', $userId)->pluck('creator_id');
                $query->whereIn('creator_id', $followedCreatorIds);
            } else {
                // If unauthenticated or following nobody, return empty result
                $query->whereRaw('1 = 0');
            }
            $query->latest();
        } elseif ($target === 'trending') {
            $query->withCount('likedUsers')
                ->orderByDesc('liked_users_count')
                ->orderByDesc('created_at');
        } else {
            // 'for-you'
            $query->latest();
        }

        $perPage = (int) $request->query('per_page', 20);
        $page = (int) $request->query('page', 1);

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $data = $paginator->getCollection()->map(function (Drop $drop) {
            $imageUrl = $drop->mainImage?->image
                ?? $drop->images->first()?->image
                ?? '';

            // If image is a local storage path, format to full URL
            if ($imageUrl && ! str_starts_with($imageUrl, 'http://') && ! str_starts_with($imageUrl, 'https://')) {
                $imageUrl = url($imageUrl);
            }

            $text1 = $drop->title ?? 'Drop: #' . $drop->id;
            $text2 = $drop->creator ? '@' . $drop->creator->username : ($drop->description ?? '');

            return [
                'id' => (int) $drop->id,
                'image_url' => (string) $imageUrl,
                'text1' => (string) $text1,
                'text2' => (string) $text2,
            ];
        })->values();

        $nextPage = $paginator->hasMorePages() ? $paginator->currentPage() + 1 : null;

        return response()->json([
            'data' => $data,
            'target' => $target,
            'selected_filter' => $target,
            'next_page' => $nextPage,
        ], 200);
    }
}
