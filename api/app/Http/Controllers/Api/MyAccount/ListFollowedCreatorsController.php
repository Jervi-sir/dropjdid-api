<?php

namespace App\Http\Controllers\Api\MyAccount;

use App\Http\Controllers\Controller;
use App\Models\CreatorFollower;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListFollowedCreatorsController extends Controller
{
  /**
   * Get list of followed creators for authenticated user matching FriendType interface:
   * - id: number
   * - image_url: string
   * - text1: string
   * - text2: string
   *
   * @param Request $request
   * @return JsonResponse
   */
  public function __invoke(Request $request): JsonResponse
  {
    $userId = $request->user('sanctum')?->id ?? $request->user()?->id ?? $request->query('user_id');

    if ($userId) {
      $creatorIds = CreatorFollower::where('user_id', $userId)->pluck('creator_id');
      $query = User::whereIn('id', $creatorIds);
    } else {
      // If no user context, query users with creator role or active users
      $query = User::whereHas('roles', function ($q) {
        $q->where('code', 'creator');
      });
    }

    $query->where('is_active', true);

    // Search query support
    $search = trim((string) ($request->query('search') ?? $request->query('query') ?? $request->query('q') ?? $request->query('keyword') ?? ''));
    if ($search !== '') {
      $cleanSearch = ltrim($search, '@');
      $term = '%' . strtolower($cleanSearch) . '%';
      $query->where(function ($q) use ($term) {
        $q->where('username', 'ILIKE', $term)
          ->orWhere('full_name', 'ILIKE', $term)
          ->orWhere('email', 'ILIKE', $term);
      });
    }

    $perPage = max(1, min(100, (int) $request->query('per_page', 20)));
    $page = max(1, (int) $request->query('page', 1));

    $paginator = $query->latest('created_at')->paginate($perPage, ['*'], 'page', $page);

    $data = $paginator->getCollection()->map(function (User $creator) {
      $imageUrl = $creator->image_url ?? '';
      if ($imageUrl && ! str_starts_with($imageUrl, 'http://') && ! str_starts_with($imageUrl, 'https://')) {
        $imageUrl = url($imageUrl);
      }

      $fullName = (string) ($creator->full_name ?? $creator->name ?? $creator->username ?? 'Creator #' . $creator->id);
      $username = (string) ($creator->username ? '@' . ltrim($creator->username, '@') : ($creator->email ?? ''));

      return [
        'id' => (int) $creator->id,
        'image_url' => (string) $imageUrl,
        'text1' => (string) $fullName,
        'text2' => (string) $username,
      ];
    })->values();

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
