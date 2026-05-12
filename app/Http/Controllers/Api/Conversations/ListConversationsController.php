<?php

namespace App\Http\Controllers\Api\Conversations;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListConversationsController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_if($user === null, 401);

        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $perPage = $validated['per_page'] ?? 10;

        $conversations = Conversation::query()
            ->where(function (Builder $query) use ($user): void {
                $query
                    ->where('first_user_id', $user->id)
                    ->orWhere('second_user_id', $user->id);
            })
            ->with([
                'firstUser',
                'secondUser',
                'messages' => fn ($query) => $query->latest(),
            ])
            ->latest('updated_at')
            ->simplePaginate($perPage);

        return response()->json([
            'data' => collect($conversations->items())
                ->map(fn (Conversation $conversation): array => $conversation->formatForList($user))
                ->values(),
            'next_page' => $conversations->hasMorePages() ? $conversations->currentPage() + 1 : null,
        ]);
    }
}
