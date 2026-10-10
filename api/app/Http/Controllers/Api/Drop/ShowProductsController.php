<?php

namespace App\Http\Controllers\Api\Drop;

use App\Http\Controllers\Controller;
use App\Models\Drop;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShowProductsController extends Controller
{
    /**
     * Get list of products in a drop formatted as ProductType[].
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $userId = $request->user('sanctum')?->id ?? $request->user()?->id;

        $drop = Drop::find($id);

        if (! $drop) {
            return response()->json([
                'message' => 'Drop not found.',
            ], 404);
        }

        $query = $drop->products()
            ->with(['mainImage', 'images', 'classification'])
            ->withCount(['saves as saved_users_count']);

        $page = $request->query('page');
        $perPage = $request->query('per_page');

        if ($page !== null || $perPage !== null) {
            $paginator = $query->paginate(max(1, min(100, (int) ($perPage ?? 20))));
            $collection = $paginator->getCollection();
            $nextPage = $paginator->hasMorePages() ? $paginator->currentPage() + 1 : null;
            $total = $paginator->total();
            $currentPage = $paginator->currentPage();
        } else {
            $collection = $query->get();
            $nextPage = null;
            $total = $collection->count();
            $currentPage = 1;
        }

        $data = $collection->map(function (Product $product) use ($userId) {
            $overridePrice = $product->pivot->drop_price ?? null;
            return $product->toProductTypeArray($userId, $overridePrice !== null ? (float) $overridePrice : null);
        })->values();

        return response()->json([
            'data' => $data,
            'current_page' => $currentPage,
            'next_page' => $nextPage,
            'total' => $total,
        ], 200);
    }
}
