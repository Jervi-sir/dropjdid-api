<?php

namespace App\Http\Controllers\Api\Friends;

use App\Http\Controllers\Controller;
use App\Models\Friendship;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListController extends Controller
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

        $friendships = Friendship::query()
            ->where('status', 'accepted')
            ->where(function (Builder $query) use ($user): void {
                $query
                    ->where('sender_id', $user->id)
                    ->orWhere('receiver_id', $user->id);
            })
            ->when($search !== '', function (Builder $query) use ($search, $user): void {
                $query->where(function (Builder $query) use ($search, $user): void {
                    $query
                        ->where(function (Builder $senderQuery) use ($search, $user): void {
                            $senderQuery
                                ->where('sender_id', '!=', $user->id)
                                ->whereHas('sender', function (Builder $userQuery) use ($search): void {
                                    $userQuery
                                        ->where('username', 'like', '%'.$search.'%')
                                        ->orWhere('phone_number', 'like', '%'.$search.'%');
                                });
                        })
                        ->orWhere(function (Builder $receiverQuery) use ($search, $user): void {
                            $receiverQuery
                                ->where('receiver_id', '!=', $user->id)
                                ->whereHas('receiver', function (Builder $userQuery) use ($search): void {
                                    $userQuery
                                        ->where('username', 'like', '%'.$search.'%')
                                        ->orWhere('phone_number', 'like', '%'.$search.'%');
                                });
                        });
                });
            })
            ->with(['sender', 'receiver'])
            ->latest('accepted_at')
            ->simplePaginate($perPage);

        return response()->json([
            'data' => $friendships->getCollection()->map(function (Friendship $friendship) use ($user): array {
                $friend = $friendship->sender_id === $user->id ? $friendship->receiver : $friendship->sender;

                return [
                    'friendship_id' => $friendship->id,
                    'id' => $friend?->id,
                    'name' => $friend?->username,
                    'username' => $friend?->username,
                    'image' => $friend?->image,
                ];
            })->values(),
            'next_page' => $friendships->hasMorePages() ? $friendships->currentPage() + 1 : null,
        ]);
    }
}
