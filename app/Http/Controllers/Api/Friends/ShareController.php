<?php

namespace App\Http\Controllers\Api\Friends;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Drop;
use App\Models\Friendship;
use App\Models\Message;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShareController extends Controller
{
    public function index(Request $request): JsonResponse
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

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_if($user === null, 401);

        $validated = $request->validate([
            'friend_id' => ['required', 'integer', 'exists:users,id', 'different:'.(string) $user->id],
            'item_type' => ['required', 'in:product,drop,profile'],
            'item_id' => ['required', 'integer'],
        ]);

        $friendshipExists = Friendship::query()
            ->where('status', 'accepted')
            ->where(function (Builder $query) use ($user, $validated): void {
                $query
                    ->where(function (Builder $pairQuery) use ($user, $validated): void {
                        $pairQuery
                            ->where('sender_id', $user->id)
                            ->where('receiver_id', $validated['friend_id']);
                    })
                    ->orWhere(function (Builder $pairQuery) use ($user, $validated): void {
                        $pairQuery
                            ->where('sender_id', $validated['friend_id'])
                            ->where('receiver_id', $user->id);
                    });
            })
            ->exists();

        abort_unless($friendshipExists, 422, 'You can only share with accepted friends.');

        [$type, $attachableType] = $this->resolveShareable($validated['item_type']);

        abort_unless($attachableType::query()->whereKey($validated['item_id'])->exists(), 422, 'The selected item is invalid.');

        $conversation = DB::transaction(function () use ($user, $validated, $type, $attachableType) {
            $conversation = Conversation::query()
                ->where('type', 'private')
                ->whereHas('participants', fn (Builder $query) => $query->where('user_id', $user->id))
                ->whereHas('participants', fn (Builder $query) => $query->where('user_id', $validated['friend_id']))
                ->whereRaw('(select count(*) from conversation_participants where conversation_participants.conversation_id = conversations.id) = 2')
                ->first();

            if ($conversation === null) {
                $conversation = Conversation::query()->create([
                    'type' => 'private',
                ]);

                ConversationParticipant::query()->create([
                    'conversation_id' => $conversation->id,
                    'user_id' => $user->id,
                ]);

                ConversationParticipant::query()->create([
                    'conversation_id' => $conversation->id,
                    'user_id' => $validated['friend_id'],
                ]);
            }

            $message = Message::query()->create([
                'conversation_id' => $conversation->id,
                'sender_id' => $user->id,
                'type' => $type,
                'attachable_type' => $attachableType,
                'attachable_id' => $validated['item_id'],
            ]);

            return [$conversation, $message];
        });

        return response()->json([
            'message' => 'Item shared successfully.',
            'conversation_id' => $conversation[0]->id,
            'message_id' => $conversation[1]->id,
        ]);
    }

    private function resolveShareable(string $itemType): array
    {
        return match ($itemType) {
            'product' => ['product', Product::class],
            'profile' => ['profile', User::class],
            'drop' => ['text', Drop::class],
        };
    }
}
