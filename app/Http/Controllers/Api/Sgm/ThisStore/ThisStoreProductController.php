<?php

namespace App\Http\Controllers\Api\Sgm\ThisStore;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ThisStoreProductController extends Controller
{
    /**
     * Get list of products belonging to a specific store with product_status filtering.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        return $this->index($request);
    }

    /**
     * List products of a store with status and keyword filters.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $storeId = $request->query('store_id') ?? $request->route('store_id') ?? $request->route('id');

        $query = Product::query()
            ->with(['mainImage', 'images', 'category', 'quality', 'gender']);

        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        // Filter by product_status (e.g. "draft", "published", "archived", "rejected", or "all")
        $status = $request->query('product_status') ?? $request->query('status');
        if ($status && $status !== 'all') {
            if (is_array($status)) {
                $query->whereIn('product_status', $status);
            } elseif (str_contains($status, ',')) {
                $statuses = array_map('trim', explode(',', $status));
                $query->whereIn('product_status', $statuses);
            } else {
                $query->where('product_status', trim($status));
            }
        }

        // Search by keyword
        $search = trim((string) ($request->query('search') ?? $request->query('query') ?? $request->query('q') ?? $request->query('keyword') ?? ''));
        if ($search !== '') {
            $term = '%' . strtolower($search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'ILIKE', $term)
                  ->orWhere('description', 'ILIKE', $term);
            });
        }

        $perPage = max(1, min(100, (int) $request->query('per_page', 20)));
        $page = max(1, (int) $request->query('page', 1));

        $paginator = $query->latest('created_at')->paginate($perPage, ['*'], 'page', $page);

        $data = $paginator->getCollection()->map(function (Product $product) {
            $imageUrl = $product->mainImage?->image_url
                ?? $product->images->first()?->image_url
                ?? '';

            if ($imageUrl && ! str_starts_with($imageUrl, 'http://') && ! str_starts_with($imageUrl, 'https://')) {
                $imageUrl = url($imageUrl);
            }

            $priceShown = $product->price_shown ?? $product->price_original;
            $priceOriginal = $product->price_original;

            $promoPercentage = '';
            if ($priceOriginal && $priceShown && (float) $priceOriginal > (float) $priceShown) {
                $discount = round(((float) $priceOriginal - (float) $priceShown) / (float) $priceOriginal * 100);
                $promoPercentage = "-{$discount}%";
            }

            $name = (string) ($product->name ?? 'Product #' . $product->id);

            return [
                'id' => (int) $product->id,
                'store_id' => $product->store_id ? (int) $product->store_id : null,
                'name' => $name,
                'text' => $name,
                'image_url' => (string) $imageUrl,
                'imageUrl' => (string) $imageUrl,
                'price_original' => $priceOriginal !== null ? (float) $priceOriginal : null,
                'price_shown' => $priceShown !== null ? (float) $priceShown : null,
                'price_store' => $product->price_store !== null ? (float) $product->price_store : null,
                'price1' => $priceShown !== null ? number_format((float) $priceShown, 0, '.', ' ') . ' DZD' : '',
                'price2' => $priceOriginal !== null ? number_format((float) $priceOriginal, 0, '.', ' ') . ' DZD' : '',
                'promo_percentage' => (string) $promoPercentage,
                'promoPercentage' => (string) $promoPercentage,
                'product_status' => Product::formatStatus($product->product_status),
                'status_raw' => $product->product_status,
                'rejection_reason' => $product->rejection_reason,
                'created_at' => $product->created_at,
                'updated_at' => $product->updated_at,
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
