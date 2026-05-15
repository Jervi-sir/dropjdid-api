<?php

namespace App\Http\Controllers\Api\Creators;

use App\Http\Controllers\Controller;
use App\Models\CreatorFollower;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListMyFollowersController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_if($user === null, 401);

        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $perPage = $validated['per_page'] ?? 10;
        $search = trim((string) ($validated['search'] ?? ''));

        $followers = CreatorFollower::query()
            ->where('creator_id', $user->id)
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->whereHas('user', function (Builder $userQuery) use ($search): void {
                    $userQuery
                        ->where('username', 'like', '%'.$search.'%')
                        ->orWhere('phone_number', 'like', '%'.$search.'%');
                });
            })
            ->with('user')
            ->latest('id')
            ->simplePaginate($perPage);

        return response()->json([
            'data' => collect($followers->items())->map(function (CreatorFollower $follower): array {
                return [
                    'id' => $follower->id,
                    'user_id' => $follower->user?->id,
                    'name' => $follower->user?->username,
                    'username' => $follower->user?->username,
                    'image' => $follower->user?->image,
                ];
            })->values(),
            'next_page' => $followers->hasMorePages() ? $followers->currentPage() + 1 : null,
        ]);
    }
}
