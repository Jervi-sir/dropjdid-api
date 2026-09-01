<?php

namespace App\Http\Controllers\Api\AffiliateProduct;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\Label;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListController extends Controller
{
    /**
     * List paginated labels, each containing up to 20 affiliate products,
     * with optional search, label_category, dropId, and comprehensive filters (price, quality, gender, type/size).
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user('sanctum')?->id ?? $request->user()?->id;
        $page = max(1, (int) $request->query('page', 1));
        $perPage = max(1, min(50, (int) $request->query('per_page', 5)));
        $productsPerSection = max(1, min(50, (int) $request->query('products_per_section', 20)));

        $labelCategory = $request->query('label_category');
        $search = trim((string) ($request->query('search') ?? $request->query('query') ?? $request->query('q') ?? $request->query('keyword') ?? ''));

        // Query labels that contain affiliate products matching the applied filters
        $labelsQuery = Label::query()
            ->with('category')
            ->whereHas('products', function (Builder $productQ) use ($request, $search) {
                $this->applyProductFilters($productQ, $request, $search);
            });

        // Filter by Label Category
        if ($labelCategory) {
            $labelsQuery->whereHas('category', function ($q) use ($labelCategory) {
                $q->where('code', $labelCategory)
                    ->orWhere('id', $labelCategory);
            });
        }

        // Filter by search query
        if ($search !== '') {
            $term = '%'.strtolower($search).'%';
            $labelsQuery->where(function ($q) use ($term) {
                $q->where('code', 'ILIKE', $term)
                    ->orWhere('en', 'ILIKE', $term)
                    ->orWhere('fr', 'ILIKE', $term)
                    ->orWhere('ar', 'ILIKE', $term)
                    ->orWhereHas('category', function ($catQ) use ($term) {
                        $catQ->where('code', 'ILIKE', $term)
                            ->orWhere('en', 'ILIKE', $term)
                            ->orWhere('fr', 'ILIKE', $term)
                            ->orWhere('ar', 'ILIKE', $term);
                    })
                    ->orWhereHas('products', function ($prodQ) use ($term) {
                        $prodQ->where('name', 'ILIKE', $term)
                            ->orWhere('description', 'ILIKE', $term);
                    });
            });
        }

        $totalLabels = $labelsQuery->count();
        $labels = $labelsQuery
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $sections = [];

        foreach ($labels as $label) {
            $productsQuery = $label->products()
                ->with(['mainImage', 'images', 'savedUsers'])
                ->withCount('savedUsers');

            $this->applyProductFilters($productsQuery, $request, $search);

            $products = $productsQuery
                ->latest('products.created_at')
                ->take($productsPerSection)
                ->get();

            if ($products->isEmpty()) {
                continue;
            }

            $sections[] = [
                'id' => (int) $label->id,
                'label_id' => (int) $label->id,
                'label_code' => (string) $label->code,
                'label' => (string) ($label->en ?? $label->code),
                'category_name' => (string) ($label->category?->en ?? $label->category?->code ?? ''),
                'section_type' => 'products',
                'products' => $products->map(fn ($p) => $this->formatProduct($p, $userId))->values(),
            ];
        }

        // Inject Ads section (with section_type => "ads" and at least 4 ads) every 2 product sections
        if ($search === '') {
            $sections = Advertisement::injectAds($sections, every: 2, minAds: 4);
        }

        $nextPage = (($page * $perPage) < $totalLabels) ? ($page + 1) : null;

        return response()->json([
            'data' => $sections,
            'current_page' => $page,
            'next_page' => $nextPage,
            'total_labels' => $totalLabels,
        ], 200);
    }

    /**
     * Get paginated affiliate products for a specific Label with optional search query and filters.
     */
    public function labelProducts(Request $request, string|int $label): JsonResponse
    {
        $userId = $request->user('sanctum')?->id ?? $request->user()?->id;

        $labelModel = is_numeric($label)
            ? Label::with('category')->find($label)
            : Label::with('category')->where('code', $label)->first();

        if (! $labelModel) {
            return response()->json([
                'message' => 'Label not found',
            ], 404);
        }

        $perPage = max(1, min(100, (int) $request->query('per_page', 20)));
        $search = trim((string) ($request->query('search') ?? $request->query('query') ?? $request->query('q') ?? $request->query('keyword') ?? ''));

        $productsQuery = Product::query()
            ->whereHas('labels', function ($q) use ($labelModel) {
                $q->where('labels.id', $labelModel->id);
            });

        $this->applyProductFilters($productsQuery, $request, $search);

        $paginated = $productsQuery
            ->with(['mainImage', 'images', 'savedUsers'])
            ->withCount('savedUsers')
            ->latest('products.created_at')
            ->paginate($perPage);

        return response()->json([
            'label' => [
                'id' => (int) $labelModel->id,
                'code' => (string) $labelModel->code,
                'name' => (string) ($labelModel->en ?? $labelModel->code),
                'category' => $labelModel->category ? [
                    'id' => (int) $labelModel->category->id,
                    'code' => (string) $labelModel->category->code,
                    'name' => (string) ($labelModel->category->en ?? $labelModel->category->code),
                ] : null,
            ],
            'data' => collect($paginated->items())->map(fn ($p) => $this->formatProduct($p, $userId))->values(),
            'current_page' => $paginated->currentPage(),
            'next_page' => $paginated->hasMorePages() ? $paginated->currentPage() + 1 : null,
            'total' => $paginated->total(),
        ], 200);
    }

    /**
     * Apply common affiliate product filters (Status, is_affiliate = true, Search, Price Min/Max, Quality, Gender, Type & Size).
     */
    protected function applyProductFilters(Builder|Relation $query, Request $request, string $search = ''): void
    {
        // 1. Published / active products only
        $query->where(function ($q) {
            $q->where('products.product_status', 'published')
                ->orWhereNull('products.product_status');
        });

        // 2. Affiliate products only
        $query->where('products.is_affiliate', true);

        // 3. Search query matching name or description
        if ($search !== '') {
            $term = '%'.strtolower($search).'%';
            $query->where(function ($q) use ($term) {
                $q->where('products.name', 'ILIKE', $term)
                    ->orWhere('products.description', 'ILIKE', $term);
            });
        }

        // 4. Price min and max
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

        // 5. Quality filter (IDs, names, or codes)
        $qualities = $this->parseFilterList($request->query('quality') ?? $request->query('qualities'));
        if (! empty($qualities)) {
            $query->whereHas('quality', function ($q) use ($qualities) {
                $q->whereIn('id', array_filter($qualities, 'is_numeric'))
                    ->orWhereIn('code', $qualities)
                    ->orWhereIn('en', $qualities);
            });
        }

        // 6. Gender filter (IDs, names, or codes)
        $genders = $this->parseFilterList($request->query('gender') ?? $request->query('genders'));
        if (! empty($genders)) {
            $query->whereHas('gender', function ($q) use ($genders) {
                $q->whereIn('id', array_filter($genders, 'is_numeric'))
                    ->orWhereIn('code', $genders)
                    ->orWhereIn('en', $genders);
            });
        }

        // 7. Types filter (Category IDs, codes, names)
        $types = $this->parseFilterList($request->query('type') ?? $request->query('types') ?? $request->query('category') ?? $request->query('categories'));
        if (! empty($types)) {
            $query->whereHas('category', function ($q) use ($types) {
                $q->whereIn('id', array_filter($types, 'is_numeric'))
                    ->orWhereIn('code', $types)
                    ->orWhereIn('en', $types);
            });
        }

        // 8. Size filter (Size IDs, names, codes)
        $sizes = $this->parseFilterList($request->query('size') ?? $request->query('sizes'));
        if (! empty($sizes)) {
            $query->whereHas('sizes', function ($q) use ($sizes) {
                $q->whereIn('sizes.id', array_filter($sizes, 'is_numeric'))
                    ->orWhereIn('sizes.code', $sizes)
                    ->orWhereIn('sizes.name', $sizes);
            });
        }
    }

    /**
     * Helper to parse comma-separated string or array into normalized list.
     */
    protected function parseFilterList(mixed $input): array
    {
        if (empty($input)) {
            return [];
        }

        if (is_array($input)) {
            $items = $input;
        } else {
            $items = explode(',', (string) $input);
        }

        return array_values(array_filter(array_map(fn ($item) => trim((string) $item), $items), fn ($item) => $item !== ''));
    }

    /**
     * Format a single Product model into ProductType format.
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

        $isSaved = false;
        if ($userId) {
            $isSaved = $product->savedUsers->contains('id', $userId);
        }

        return [
            'id' => (int) $product->id,
            'image_url' => (string) $imageUrl,
            'prices' => [
                'price1' => number_format($currentPrice, 0, '.', ' ').' DZD',
                'price2' => ($originalPrice > $currentPrice) ? number_format($originalPrice, 0, '.', ' ').' DZD' : '',
                'promo_percentage' => (string) $promoPercentage,
            ],
            'text' => (string) ($product->name ?? 'Product #'.$product->id),
            'save' => [
                'is_saved' => (bool) $isSaved,
                'nb_save' => (int) ($product->saved_users_count ?? $product->savedUsers->count()),
            ],
        ];
    }
}
