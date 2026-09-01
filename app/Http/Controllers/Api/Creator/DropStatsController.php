<?php

namespace App\Http\Controllers\Api\Creator;

use App\Http\Controllers\Controller;
use App\Models\Drop;
use App\Models\Message;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DropStatsController extends Controller
{
    /**
     * List users who liked the drop.
     */
    public function likedBy(Request $request, int|string $id): JsonResponse
    {
        $drop = Drop::find($id);
        if (! $drop) {
            return response()->json(['message' => 'Drop not found.'], 404);
        }

        $perPage = max(1, min(100, (int) $request->query('per_page', 20)));
        $page = max(1, (int) $request->query('page', 1));
        $search = trim((string) ($request->query('search') ?? ''));

        $query = $drop->likedUsers()->getQuery();

        if ($search !== '') {
            $term = '%'.strtolower($search).'%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(full_name) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(username) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(email) LIKE ?', [$term]);
            });
        }

        $paginator = $query->paginate($perPage, ['users.*'], 'page', $page);

        $items = collect($paginator->items())->map(fn (User $user) => $this->formatUserItem($user))->values()->all();

        return response()->json([
            'data' => $items,
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'next_page' => $paginator->hasMorePages() ? ($page + 1) : null,
        ], 200);
    }

    /**
     * List users who saved the drop.
     */
    public function savedBy(Request $request, int|string $id): JsonResponse
    {
        $drop = Drop::find($id);
        if (! $drop) {
            return response()->json(['message' => 'Drop not found.'], 404);
        }

        $perPage = max(1, min(100, (int) $request->query('per_page', 20)));
        $page = max(1, (int) $request->query('page', 1));
        $search = trim((string) ($request->query('search') ?? ''));

        $query = $drop->savedUsers()->getQuery();

        if ($search !== '') {
            $term = '%'.strtolower($search).'%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(full_name) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(username) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(email) LIKE ?', [$term]);
            });
        }

        $paginator = $query->paginate($perPage, ['users.*'], 'page', $page);

        $items = collect($paginator->items())->map(fn (User $user) => $this->formatUserItem($user))->values()->all();

        return response()->json([
            'data' => $items,
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'next_page' => $paginator->hasMorePages() ? ($page + 1) : null,
        ], 200);
    }

    /**
     * List users who shared the drop.
     */
    public function sharedBy(Request $request, int|string $id): JsonResponse
    {
        $drop = Drop::find($id);
        if (! $drop) {
            return response()->json(['message' => 'Drop not found.'], 404);
        }

        $perPage = max(1, min(100, (int) $request->query('per_page', 20)));
        $page = max(1, (int) $request->query('page', 1));
        $search = trim((string) ($request->query('search') ?? ''));

        // Query distinct senders who shared this drop in messages
        $senderIds = Message::where('attachable_id', $drop->id)
            ->where(function ($q) {
                $q->where('attachable_type', 'drop')
                    ->orWhere('attachable_type', Drop::class);
            })
            ->distinct()
            ->pluck('sender_id');

        $query = User::whereIn('id', $senderIds);

        if ($search !== '') {
            $term = '%'.strtolower($search).'%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(full_name) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(username) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(email) LIKE ?', [$term]);
            });
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $items = collect($paginator->items())->map(fn (User $user) => $this->formatUserItem($user))->values()->all();

        return response()->json([
            'data' => $items,
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'next_page' => $paginator->hasMorePages() ? ($page + 1) : null,
        ], 200);
    }

    /**
     * List products belonging to this drop.
     */
    public function products(Request $request, int|string $id): JsonResponse
    {
        $drop = Drop::find($id);
        if (! $drop) {
            return response()->json(['message' => 'Drop not found.'], 404);
        }

        $currentUserId = $request->user('sanctum')?->id ?? $request->user()?->id;
        $perPage = max(1, min(100, (int) $request->query('per_page', 20)));
        $page = max(1, (int) $request->query('page', 1));
        $search = trim((string) ($request->query('search') ?? ''));

        $query = $drop->products()
            ->with(['mainImage', 'images', 'savedUsers'])
            ->withCount('savedUsers');

        if ($search !== '') {
            $term = '%'.strtolower($search).'%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(products.name) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(products.description) LIKE ?', [$term]);
            });
        }

        $paginator = $query->paginate($perPage, ['products.*'], 'page', $page);

        $items = collect($paginator->items())->map(function (Product $product) use ($currentUserId) {
            $imageUrl = $product->mainImage?->image_url
                ?? $product->images->first()?->image_url
                ?? '';

            if ($imageUrl && ! str_starts_with($imageUrl, 'http://') && ! str_starts_with($imageUrl, 'https://')) {
                $imageUrl = url($imageUrl);
            }

            $currentPrice = $product->price_shown ?? $product->price_original ?? 0;
            $originalPrice = $product->price_original ?? $currentPrice;

            $promoPercentage = '';
            if ($originalPrice > 0 && $currentPrice < $originalPrice) {
                $discount = round((($originalPrice - $currentPrice) / $originalPrice) * 100);
                $promoPercentage = "-{$discount}%";
            }

            $isSaved = false;
            if ($currentUserId) {
                $isSaved = $product->savedUsers->contains('id', $currentUserId);
            }

            return [
                'id' => (int) $product->id,
                'image_url' => (string) $imageUrl,
                'text' => (string) ($product->name ?? 'Product #'.$product->id),
                'prices' => [
                    'price1' => number_format($currentPrice, 0, '.', ' ').' DZD',
                    'price2' => ($originalPrice > $currentPrice) ? number_format($originalPrice, 0, '.', ' ').' DZD' : '',
                    'promo_percentage' => (string) $promoPercentage,
                ],
                'save' => [
                    'is_saved' => (bool) $isSaved,
                    'nb_save' => (int) ($product->saved_users_count ?? $product->savedUsers->count()),
                ],
            ];
        })->values()->all();

        return response()->json([
            'data' => $items,
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'next_page' => $paginator->hasMorePages() ? ($page + 1) : null,
        ], 200);
    }

    /**
     * Helper to format User model into FriendType schema.
     */
    private function formatUserItem(User $user): array
    {
        $avatar = $user->profile_photo_url ?? $user->avatar_url ?? $user->avatar ?? '';
        if ($avatar && ! str_starts_with($avatar, 'http://') && ! str_starts_with($avatar, 'https://')) {
            $avatar = url($avatar);
        }

        $displayName = $user->full_name ?? $user->name ?? ($user->username ? '@'.ltrim($user->username, '@') : 'User #'.$user->id);
        $username = '@'.ltrim($user->username ?? $user->name ?? 'user', '@');

        return [
            'id' => (int) $user->id,
            'image_url' => (string) $avatar,
            'text1' => (string) $displayName,
            'text2' => (string) $username,
        ];
    }
}
