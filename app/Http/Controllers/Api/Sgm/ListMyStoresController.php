<?php

namespace App\Http\Controllers\Api\Sgm;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListMyStoresController extends Controller
{
    /**
     * Get list of stores belonging to the authenticated user matching StoreType interface:
     * - id: number
     * - text1: string
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $userId = $request->user('sanctum')?->id ?? $request->user()?->id ?? $request->query('user_id');

        if ($userId) {
            $query = Store::where('user_id', $userId);
        } else {
            $query = Store::query();
        }

        // Search query support
        $search = trim((string) ($request->query('search') ?? $request->query('query') ?? $request->query('q') ?? $request->query('keyword') ?? ''));
        if ($search !== '') {
            $term = '%' . strtolower($search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'ILIKE', $term)
                  ->orWhere('phone_number', 'ILIKE', $term);
            });
        }

        $perPage = max(1, min(100, (int) $request->query('per_page', 20)));
        $page = max(1, (int) $request->query('page', 1));

        $paginator = $query->latest('created_at')->paginate($perPage, ['*'], 'page', $page);

        $data = $paginator->getCollection()->map(function (Store $store) {
            $name = (string) ($store->name ?: 'Store #' . $store->id);

            return [
                'id' => (int) $store->id,
                'text1' => (string) $name,
                'store_status' => Store::formatStatus($store->store_status),
                'is_approved' => (bool) $store->is_approved,
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
