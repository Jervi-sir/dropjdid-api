<?php

namespace App\Http\Controllers\Api\MyAccount;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListSavedProductsController extends Controller
{
    /**
     * Get paginated saved products for the authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $userId = $request->user('sanctum')?->id ?? $request->user()?->id ?? $request->query('user_id');

        if (! $userId) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $perPage = max(1, min(100, (int) $request->query('per_page', 20)));
        $page = max(1, (int) $request->query('page', 1));
        $search = trim((string) ($request->query('search') ?? $request->query('query') ?? $request->query('q') ?? $request->query('keyword') ?? ''));

        $query = Product::query()
            ->join('saved_products', 'products.id', '=', 'saved_products.product_id')
            ->where('saved_products.user_id', $userId)
            ->select('products.*')
            ->with(['mainImage', 'images', 'savedUsers'])
            ->withCount('savedUsers');

        // Published / active products only
        $query->where(function ($q) {
            $q->where('products.product_status', 'published')
              ->orWhereNull('products.product_status');
        });

        // Search query matching name or description
        if ($search !== '') {
            $term = '%' . strtolower($search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('products.name', 'ILIKE', $term)
                  ->orWhere('products.description', 'ILIKE', $term);
            });
        }

        // Price min / max filter
        $priceMin = $request->query('price_min') ?? $request->query('min_price');
        $priceMax = $request->query('price_max') ?? $request->query('max_price');

        if ($priceMin !== null && is_numeric($priceMin)) {
            $query->where(function ($q) use ($priceMin) {
                $q->where('products.price_shown', '>=', (float) $priceMin)
                  ->orWhere(function ($subQ) use ($priceMin) {
                      $subQ->whereNull('products.price_shown')
                           ->where('products.price_original', '>=', (float) $priceMin);
                  });
            });
        }

        if ($priceMax !== null && is_numeric($priceMax)) {
            $query->where(function ($q) use ($priceMax) {
                $q->where('products.price_shown', '<=', (float) $priceMax)
                  ->orWhere(function ($subQ) use ($priceMax) {
                      $subQ->whereNull('products.price_shown')
                           ->where('products.price_original', '<=', (float) $priceMax);
                  });
            });
        }

        $paginator = $query->orderBy('saved_products.created_at', 'desc')->paginate($perPage, ['products.*'], 'page', $page);

        $data = $paginator->getCollection()->map(fn(Product $p) => $this->formatProduct($p, $userId))->values();

        $nextPage = $paginator->hasMorePages() ? $paginator->currentPage() + 1 : null;

        return response()->json([
            'data' => $data,
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'next_page' => $nextPage,
        ], 200);
    }

    /**
     * Format a single Product model into ProductType format.
     *
     * @param Product $product
     * @param int|null $userId
     * @return array
     */
    private function formatProduct(Product $product, ?int $userId = null): array
    {
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

        $isSaved = true;
        if ($userId) {
            $isSaved = $product->savedUsers->contains('id', $userId);
        }

        return [
            'id' => (int) $product->id,
            'image_url' => (string) $imageUrl,
            'prices' => [
                'price1' => number_format($currentPrice, 0, '.', ' ') . ' DZD',
                'price2' => ($originalPrice > $currentPrice) ? number_format($originalPrice, 0, '.', ' ') . ' DZD' : '',
                'promo_percentage' => (string) $promoPercentage,
            ],
            'text' => (string) ($product->name ?? 'Product #' . $product->id),
            'save' => [
                'is_saved' => (bool) $isSaved,
                'nb_save' => (int) ($product->saved_users_count ?? $product->savedUsers->count()),
            ],
        ];
    }
}
