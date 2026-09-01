<?php

namespace App\Http\Controllers\Api\Feeds;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\Drop;
use App\Models\Label;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExploreController extends Controller
{
    /**
     * Get explore feed with paginated Label sections (each returning up to 20 products),
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
        $dropId = $request->query('drop_id') ?? $request->query('dropId') ?? $request->query('drop');

        // Only query labels that contain products matching the applied filters
        $labelsQuery = Label::query()
            ->with('category')
            ->whereHas('products', function (Builder $productQ) use ($request, $search) {
                $this->applyProductFilters($productQ, $request, $search);
            });

        // Filter by Drop relationship (suggestions related to a specific drop)
        if ($dropId) {
            $drop = Drop::with('products')->find($dropId);
            if ($drop) {
                $dropProductIds = $drop->products->pluck('id');
                if ($dropProductIds->isNotEmpty()) {
                    $labelsQuery->whereHas('products', function ($q) use ($dropProductIds) {
                        $q->whereIn('products.id', $dropProductIds);
                    });
                }
            }
        }

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

        foreach ($labels as $index => $label) {
            $productsQuery = Product::query()
                ->whereHas('labels', function ($q) use ($label) {
                    $q->where('labels.id', $label->id);
                });

            $this->applyProductFilters($productsQuery, $request, $search);

            $products = $productsQuery
                ->with(['mainImage', 'images', 'savedUsers'])
                ->withCount('savedUsers')
                ->latest('created_at')
                ->limit($productsPerSection)
                ->get();

            if ($products->isNotEmpty()) {
                $sections[] = [
                    'id' => (string) $label->id,
                    'label_id' => (int) $label->id,
                    'label_code' => (string) $label->code,
                    'label' => (string) ($label->en ?? $label->code),
                    'category' => $label->category ? [
                        'id' => (int) $label->category->id,
                        'code' => (string) $label->category->code,
                        'name' => (string) ($label->category->en ?? $label->category->code),
                    ] : null,
                    'section_type' => 'products',
                    'products' => $products->map(fn ($p) => $this->formatProduct($p, $userId))->values(),
                ];
            }
        }

        // On explore feed (when not searching or drop filtering), inject Ads section every 2 product sections
        if ($search === '' && ! $dropId) {
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
     * Get paginated products for a specific Label with optional search query, dropId, and filters.
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
            ->latest('created_at')
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
     * Apply common product filters (Status, Search, Price Min/Max, Quality, Gender, Type & Size).
     */
    protected function applyProductFilters(Builder $query, Request $request, string $search = ''): void
    {
        // 1. Published / active products only
        $query->where(function ($q) {
            $q->where('product_status', 'published')
                ->orWhereNull('product_status');
        });

        // 2. Search query matching name or description
        if ($search !== '') {
            $term = '%'.strtolower($search).'%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'ILIKE', $term)
                    ->orWhere('description', 'ILIKE', $term);
            });
        }

        // 3. Price min and max (supports price_min, price_max, min_price, max_price)
        $priceMin = $request->query('price_min') ?? $request->query('min_price');
        $priceMax = $request->query('price_max') ?? $request->query('max_price');

        if ($priceMin !== null && is_numeric($priceMin)) {
            $query->where(function ($q) use ($priceMin) {
                $q->where('price_shown', '>=', (float) $priceMin)
                    ->orWhere(function ($subQ) use ($priceMin) {
                        $subQ->whereNull('price_shown')
                            ->where('price_original', '>=', (float) $priceMin);
                    });
            });
        }

        // If priceMax is provided, numeric, and not 'unlimited' / 'no limits'
        if ($priceMax !== null && is_numeric($priceMax) && (float) $priceMax > 0) {
            $query->where(function ($q) use ($priceMax) {
                $q->where(function ($subQ) use ($priceMax) {
                    $subQ->whereNotNull('price_shown')
                        ->where('price_shown', '<=', (float) $priceMax);
                })->orWhere(function ($subQ) use ($priceMax) {
                    $subQ->whereNull('price_shown')
                        ->where('price_original', '<=', (float) $priceMax);
                });
            });
        }

        // 4. Quality filter (supports quality=original,premium_copy,copy or array)
        $qualities = $this->parseFilterList($request->query('quality') ?? $request->query('qualities'));
        if (! empty($qualities)) {
            $query->whereHas('quality', function ($q) use ($qualities) {
                $q->where(function ($sub) use ($qualities) {
                    foreach ($qualities as $quality) {
                        $term = '%'.strtolower($quality).'%';
                        $sub->orWhere('code', 'ILIKE', $term)
                            ->orWhere('en', 'ILIKE', $term)
                            ->orWhere('fr', 'ILIKE', $term)
                            ->orWhere('ar', 'ILIKE', $term);
                    }
                });
            });
        }

        // 5. Gender / For filter (supports gender=men,women,kids or for=man,woman,kids)
        $genders = $this->parseFilterList(
            $request->query('gender')
            ?? $request->query('genders')
            ?? $request->query('for')
        );

        if (! empty($genders)) {
            // Map common aliases (e.g. man -> men, woman -> women)
            $normalizedGenders = array_map(function ($g) {
                $lower = strtolower($g);

                return match ($lower) {
                    'man' => 'men',
                    'woman' => 'women',
                    default => $lower,
                };
            }, $genders);

            $query->whereHas('gender', function ($q) use ($normalizedGenders) {
                $q->where(function ($sub) use ($normalizedGenders) {
                    foreach ($normalizedGenders as $gender) {
                        $term = '%'.$gender.'%';
                        $sub->orWhere('code', 'ILIKE', $term)
                            ->orWhere('en', 'ILIKE', $term)
                            ->orWhere('fr', 'ILIKE', $term)
                            ->orWhere('ar', 'ILIKE', $term);
                    }
                });
            });
        }

        // 6. Category Type & Size filter (e.g. type="Upper body,Bags" or size="M,L,42" or types / sizes)
        $types = $this->parseFilterList(
            $request->query('type')
            ?? $request->query('types')
            ?? $request->query('category')
            ?? $request->query('categories')
            ?? $request->query('category_type')
        );

        if (! empty($types)) {
            $query->where(function ($q) use ($types) {
                // Check category table code/names
                $q->whereHas('category', function ($catQ) use ($types) {
                    $catQ->where(function ($sub) use ($types) {
                        foreach ($types as $type) {
                            $term = '%'.strtolower($type).'%';
                            $sub->orWhere('code', 'ILIKE', $term)
                                ->orWhere('en', 'ILIKE', $term)
                                ->orWhere('fr', 'ILIKE', $term)
                                ->orWhere('ar', 'ILIKE', $term);
                        }
                    });
                })
                // Also check matching labels/keywords (e.g. "Upper body", "Wears in head", "Accessories")
                    ->orWhereHas('labels', function ($labelQ) use ($types) {
                        $labelQ->where(function ($sub) use ($types) {
                            foreach ($types as $type) {
                                $term = '%'.strtolower($type).'%';
                                $sub->orWhere('code', 'ILIKE', $term)
                                    ->orWhere('en', 'ILIKE', $term)
                                    ->orWhere('fr', 'ILIKE', $term)
                                    ->orWhere('ar', 'ILIKE', $term);
                            }
                        });
                    });
            });
        }

        $sizes = $this->parseFilterList(
            $request->query('size')
            ?? $request->query('sizes')
        );

        if (! empty($sizes)) {
            $query->whereHas('sizes', function ($sizeQ) use ($sizes) {
                $sizeQ->where(function ($sub) use ($sizes) {
                    foreach ($sizes as $size) {
                        $term = '%'.strtolower($size).'%';
                        $sub->orWhere('code', 'ILIKE', $term)
                            ->orWhere('en', 'ILIKE', $term)
                            ->orWhere('fr', 'ILIKE', $term)
                            ->orWhere('ar', 'ILIKE', $term);
                    }
                });
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

    /**
     * Format an Advertisement model into AdType format.
     */
    private function formatAd(Advertisement $ad): array
    {
        $imageUrl = $ad->image_url ?? '';
        if ($imageUrl && ! str_starts_with($imageUrl, 'http://') && ! str_starts_with($imageUrl, 'https://')) {
            $imageUrl = url($imageUrl);
        }

        return [
            'id' => (int) $ad->id,
            'image_url' => (string) $imageUrl,
            'text1' => (string) ($ad->title ?? $ad->name ?? 'Sponsored'),
            'text2' => (string) ($ad->subtitle ?? $ad->description ?? ''),
            'url' => (string) ($ad->link_url ?? ''),
        ];
    }
}
