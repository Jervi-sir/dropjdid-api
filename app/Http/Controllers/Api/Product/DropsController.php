<?php

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Models\Drop;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DropsController extends Controller
{
    /**
     * Get paginated drops that contain this product.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function index(Request $request, int $id): JsonResponse
    {
        $product = Product::find($id);

        if (! $product) {
            return response()->json([
                'message' => 'Product not found.',
            ], 404);
        }

        $query = $product->drops()
            ->with(['creator', 'mainImage', 'images'])
            ->where(function ($q) {
                $q->where('drop_status', 'published')
                  ->orWhereNull('drop_status');
            })
            ->latest('drops.created_at');

        $perPage = (int) $request->query('per_page', 20);
        $page = (int) $request->query('page', 1);

        $paginator = $query->paginate($perPage, ['drops.*'], 'page', $page);

        $data = $paginator->getCollection()->map(function (Drop $drop) {
            $imageUrl = $drop->mainImage?->image
                ?? $drop->images->first()?->image
                ?? '';

            if ($imageUrl && ! str_starts_with($imageUrl, 'http://') && ! str_starts_with($imageUrl, 'https://')) {
                $imageUrl = url($imageUrl);
            }

            $text1 = $drop->title ?? 'Drop: #' . $drop->id;
            $text2 = $drop->creator ? '@' . $drop->creator->username : ($drop->description ?? '');

            return [
                'id' => (int) $drop->id,
                'image_url' => (string) $imageUrl,
                'text1' => (string) $text1,
                'text2' => (string) $text2,
            ];
        })->values();

        $nextPage = $paginator->hasMorePages() ? $paginator->currentPage() + 1 : null;

        return response()->json([
            'data' => $data,
            'total' => $paginator->total(),
            'page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'next_page' => $nextPage,
        ], 200);
    }
}
