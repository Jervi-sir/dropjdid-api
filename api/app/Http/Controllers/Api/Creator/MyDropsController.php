<?php

namespace App\Http\Controllers\Api\Creator;

use App\Http\Controllers\Controller;
use App\Models\Drop;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MyDropsController extends Controller
{
    /**
     * List current creator's drops with draft/published filtering and pagination.
     *
     * Expected Response Schema:
     * - data: Array of DropsType { id: int, image_url: string, text1: string, text2: string }
     * - next_page: int|null
     *
     * Supported Query Filters:
     * - is_draft: boolean ("true", "false", 1, 0)
     * - filter: string ("draft", "published", "my-drops", "all")
     * - status: string ("draft", "published", "ended", "all")
     * - page: int (default: 1)
     * - per_page: int (default: 10)
     * - user_id / creator_id: int (optional target creator override)
     */
    public function index(Request $request): JsonResponse
    {
        // 1. Resolve Creator
        $user = $request->user('sanctum') ?? $request->user();

        $creatorId = $request->query('creator_id') ?? $request->query('user_id');
        if ($creatorId) {
            $user = User::find($creatorId);
        }

        if (! $user) {
            $user = User::whereHas('roles', fn ($q) => $q->where('roles.code', 'creator'))
                ->orWhereHas('drops')
                ->first()
                ?? User::first();
        }

        $creatorId = $user?->id;

        // 2. Build Query
        $query = Drop::query()
            ->with(['creator', 'images', 'mainImage'])
            ->latest('id');

        if ($creatorId) {
            $query->where('creator_id', $creatorId);
        }

        // 3. Filter by Draft vs Non-Draft / Status
        $isDraftParam = $request->query('is_draft');
        $filterParam = strtolower((string) $request->query('filter', ''));
        $statusParam = strtolower((string) $request->query('status', ''));

        if ($isDraftParam !== null) {
            $isDraft = filter_var($isDraftParam, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($isDraft === true) {
                $query->where('drop_status', 'draft');
            } elseif ($isDraft === false) {
                $query->where('drop_status', '!=', 'draft');
            }
        } elseif ($filterParam === 'draft' || $statusParam === 'draft') {
            $query->where('drop_status', 'draft');
        } elseif (in_array($filterParam, ['published', 'my-drops', 'non-draft', 'active']) || $statusParam === 'published') {
            $query->where('drop_status', '!=', 'draft');
        } elseif ($statusParam && $statusParam !== 'all') {
            $query->where('drop_status', $statusParam);
        }

        // 4. Pagination
        $perPage = max(1, min(100, (int) $request->query('per_page', $request->query('limit', 10))));
        $page = max(1, (int) $request->query('page', 1));

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        // 5. Map to DropsType schema
        $items = collect($paginator->items())->map(function (Drop $drop) {
            // Determine primary image url
            $imageUrl = '';
            $mainImg = $drop->mainImage?->image;
            if ($mainImg) {
                $imageUrl = $mainImg;
            } elseif ($drop->images->isNotEmpty()) {
                $imageUrl = $drop->images->first()->image;
            }

            if ($imageUrl && ! str_starts_with($imageUrl, 'http://') && ! str_starts_with($imageUrl, 'https://')) {
                $imageUrl = url($imageUrl);
            }

            $creatorName = $drop->creator
                ? ('@'.ltrim($drop->creator->username ?? $drop->creator->name ?? 'creator', '@'))
                : '@creator';

            return [
                'id' => (int) $drop->id,
                'image_url' => (string) ($imageUrl ?? ''),
                'text1' => (string) ($drop->title ?? ('Drop #'.$drop->id)),
                'text2' => (string) $creatorName,
            ];
        })->values()->all();

        $nextPage = $paginator->hasMorePages() ? ($page + 1) : null;

        return response()->json([
            'data' => $items,
            'next_page' => $nextPage,
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
        ], 200);
    }
}
