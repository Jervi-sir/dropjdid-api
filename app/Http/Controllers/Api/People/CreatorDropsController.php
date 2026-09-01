<?php

namespace App\Http\Controllers\Api\People;

use App\Http\Controllers\Controller;
use App\Models\Drop;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CreatorDropsController extends Controller
{
    /**
     * Get drops created by this profile/creator.
     *
     * @param Request $request
     * @param int|string $id Target user/creator ID
     * @return JsonResponse
     */
    public function index(Request $request, int|string $id): JsonResponse
    {
        $currentUserId = $request->user('sanctum')?->id ?? $request->user()?->id;

        $targetUser = User::query()
            ->with(['roles'])
            ->where('id', $id)
            ->first();

        if (! $targetUser) {
            return response()->json([
                'message' => 'User not found.',
            ], 404);
        }

        $isSelf = $currentUserId && (int) $currentUserId === (int) $targetUser->id;

        // Check if user is a creator
        $roleCodes = $targetUser->roles->pluck('code')->all();
        $isCreator = in_array('creator', $roleCodes, true)
            || in_array('admin', $roleCodes, true)
            || Drop::where('creator_id', $targetUser->id)->exists();

        if (! $isCreator) {
            return response()->json([
                'data' => [],
                'is_creator' => false,
                'total' => 0,
                'current_page' => 1,
                'next_page' => null,
                'last_page' => 1,
            ], 200);
        }

        $perPage = max(1, min(100, (int) $request->query('per_page', 20)));
        $page = max(1, (int) $request->query('page', 1));

        $query = Drop::query()
            ->where('creator_id', $targetUser->id)
            ->with(['creator', 'images', 'mainImage'])
            ->latest('id');

        // Only creator viewing own profile can see drafts unless specified
        if (! $isSelf) {
            $query->where('drop_status', '!=', 'draft');
        } else {
            $isDraftParam = $request->query('is_draft');
            if ($isDraftParam !== null) {
                $isDraft = filter_var($isDraftParam, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($isDraft === true) {
                    $query->where('drop_status', 'draft');
                } elseif ($isDraft === false) {
                    $query->where('drop_status', '!=', 'draft');
                }
            }
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $creatorHandle = '@' . ltrim((string) ($targetUser->username ?? $targetUser->name ?? 'creator'), '@');

        $data = collect($paginator->items())->map(function (Drop $drop) use ($creatorHandle) {
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

            return [
                'id' => (int) $drop->id,
                'image_url' => (string) ($imageUrl ?? ''),
                'text1' => (string) ($drop->title ?? ('Drop #' . $drop->id)),
                'text2' => (string) $creatorHandle,
                'drop_status' => (string) ($drop->drop_status ?? 'published'),
                'created_at' => $drop->created_at,
            ];
        })->values()->all();

        $nextPage = $paginator->hasMorePages() ? ($page + 1) : null;

        return response()->json([
            'data' => $data,
            'is_creator' => true,
            'current_page' => $paginator->currentPage(),
            'next_page' => $nextPage,
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
        ], 200);
    }
}
