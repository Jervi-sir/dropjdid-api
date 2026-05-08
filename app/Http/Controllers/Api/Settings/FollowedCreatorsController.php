<?php

namespace App\Http\Controllers\Api\Settings;

use App\Http\Controllers\Controller;
use App\Models\CreatorFollower;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FollowedCreatorsController extends Controller
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

        $following = CreatorFollower::query()
            ->where('user_id', $user->id)
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->whereHas('creator', function (Builder $creatorQuery) use ($search): void {
                    $creatorQuery
                        ->where('username', 'like', '%'.$search.'%')
                        ->orWhere('phone_number', 'like', '%'.$search.'%');
                });
            })
            ->with('creator')
            ->latest('id')
            ->simplePaginate($perPage);

        return response()->json([
            'data' => $following->getCollection()->map(function (CreatorFollower $following): array {
                return [
                    'id' => $following->id,
                    'creator_id' => $following->creator?->id,
                    'name' => $following->creator?->username,
                    'username' => $following->creator?->username,
                    'image' => $following->creator?->image,
                ];
            })->values(),
            'next_page' => $following->hasMorePages() ? $following->currentPage() + 1 : null,
        ]);
    }
}
