<?php

namespace App\Http\Controllers\Api\Conversations;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListController extends Controller
{
    /**
     * Get paginated conversations for authenticated user matching ConversationType interface:
     * - id: number
     * - image_url: string
     * - text1: string
     * - text2: string
     * - has_unread_messages?: boolean | null
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        return $this->index($request);
    }

    /**
     * Get list of conversations for the authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user('sanctum')?->id ?? $request->user()?->id ?? $request->query('user_id');

        $query = Conversation::query()
            ->with(['firstUser', 'secondUser', 'latestMessage'])
            ->latest('updated_at');

        if ($userId) {
            $query->where(function ($q) use ($userId) {
                $q->where(function ($sub) use ($userId) {
                    $sub->where('first_user_id', $userId)
                        ->whereNull('first_user_deleted_at');
                })->orWhere(function ($sub) use ($userId) {
                    $sub->where('second_user_id', $userId)
                        ->whereNull('second_user_deleted_at');
                });
            });
        }

        $perPage = max(1, min(100, (int) $request->query('per_page', 20)));
        $page = max(1, (int) $request->query('page', 1));

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $currentUserId = $userId ? (int) $userId : 0;
        $data = $paginator->getCollection()
            ->map(fn (Conversation $c) => $c->toConversationType($currentUserId))
            ->values();

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
