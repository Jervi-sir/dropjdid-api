<?php

namespace App\Http\Controllers\Api\Search;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\Label;
use App\Models\Product;
use App\Models\SavedLabel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductsSearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'max:255'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'products_per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'qualities' => ['nullable', 'array'],
            'qualities.*' => ['integer'],
            'genders' => ['nullable', 'array'],
            'genders.*' => ['integer'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['integer'],
            'sizes' => ['nullable', 'array'],
            'sizes.*' => ['integer'],
            'price_min' => ['nullable', 'numeric'],
            'price_max' => ['nullable', 'numeric'],
        ]);

        $query = trim($validated['query']);

        if ($query === '') {
            return response()->json([
                'data' => [],
                'next_page' => null,
            ]);
        }

        $perPage = $validated['per_page'] ?? 5;
        $productsPerPage = $validated['products_per_page'] ?? 10;
        $user = $request->user();
        $userId = $user?->getAuthIdentifier();
        $page = $request->query('page', 1);

        $qualities = $request->input('qualities');
        $genders = $request->input('genders');
        $categories = $request->input('categories');
        $sizes = $request->input('sizes');
        $priceMin = $request->input('price_min');
        $priceMax = $request->input('price_max');

        $applyFilters = function ($q) use ($qualities, $genders, $categories, $sizes, $priceMin, $priceMax) {
            $q->when($qualities, function ($q) use ($qualities) {
                $q->whereIn('quality_id', (array) $qualities);
            })
                ->when($genders, function ($q) use ($genders) {
                    $q->whereIn('gender_id', (array) $genders);
                })
                ->when($categories, function ($q) use ($categories) {
                    $q->whereIn('category_id', (array) $categories);
                })
                ->when($sizes, function ($q) use ($sizes) {
                    $q->whereHas('variants', fn ($qv) => $qv->whereIn('size_id', (array) $sizes));
                })
                ->when($priceMin, function ($q) use ($priceMin) {
                    $q->where(function ($sub) use ($priceMin) {
                        $sub->where('show_price', '>=', $priceMin)
                            ->orWhere(function ($sub2) use ($priceMin) {
                                $sub2->whereNull('show_price')->where('store_price', '>=', $priceMin);
                            });
                    });
                })
                ->when($priceMax, function ($q) use ($priceMax) {
                    $q->where(function ($sub) use ($priceMax) {
                        $sub->where('show_price', '<=', $priceMax)
                            ->orWhere(function ($sub2) use ($priceMax) {
                                $sub2->whereNull('show_price')->where('store_price', '<=', $priceMax);
                            });
                    });
                });
        };

        $sections = collect();

        if ($page == 1) {
            $similarProductsQuery = Product::query()
                ->where('status', Product::STATUS_PUBLISHED)
                ->where(function ($q) use ($query) {
                    $q->where('name', 'ilike', "%$query%")
                        ->orWhereHas('keywords', fn ($qk) => $qk->where('code', 'ilike', "%$query%"))
                        ->orWhereHas('productKeywords.label', fn ($ql) => $ql->where('en', 'ilike', "%$query%"))
                        ->orWhereHas('productKeywords.label', fn ($ql) => $ql->where('fr', 'ilike', "%$query%"))
                        ->orWhereHas('productKeywords.label', fn ($ql) => $ql->where('ar', 'ilike', "%$query%"));
                });

            $applyFilters($similarProductsQuery);

            $similarProducts = $similarProductsQuery
                ->with([
                    'images',
                    'store.user',
                    'savedProducts' => function ($query) use ($userId) {
                        return $userId === null
                            ? $query->whereRaw('1 = 0')
                            : $query->where('user_id', $userId);
                    },
                ])
                ->latest('id')
                ->limit(10)
                ->get();

            if ($similarProducts->isNotEmpty()) {
                $sections->push([
                    'type' => 'label',
                    'label' => [
                        'id' => -1,
                        'code' => 'similar',
                        'en' => 'Similar',
                        'fr' => 'Similaire',
                        'ar' => 'مشابه',
                    ],
                    'products' => $similarProducts->map(fn (Product $p) => $p->formatProduct($p, $user))->values(),
                    'next_page' => null,
                    'nb_likes' => 0,
                ]);
            }
        }

        // Find labels that have matching products
        $labels = Label::query()
            ->where(function ($q) use ($query, $applyFilters) {
                // Label name matches query
                $q->where(function ($nq) use ($query) {
                    $nq->where('en', 'ilike', "%$query%")
                        ->orWhere('fr', 'ilike', "%$query%")
                        ->orWhere('ar', 'ilike', "%$query%")
                        ->orWhere('code', 'ilike', "%$query%");
                })
                    ->whereHas('keywords.productKeywords.product', function ($pq) use ($applyFilters) {
                        $pq->where('status', Product::STATUS_PUBLISHED);
                        $applyFilters($pq);
                    })
                    // OR it has products whose name matches the query
                    ->orWhereHas('keywords.productKeywords.product', function ($pq) use ($query, $applyFilters) {
                        $pq->where('status', Product::STATUS_PUBLISHED)
                            ->where('name', 'ilike', "%$query%");
                        $applyFilters($pq);
                    });
            })
            ->simplePaginate($perPage);

        $labelSections = collect($labels->items())->map(function (Label $label) use ($query, $productsPerPage, $userId, $user, $applyFilters) {
            $labelMatchesQuery = str_contains(strtolower($label->en ?? ''), strtolower($query))
                || str_contains(strtolower($label->fr ?? ''), strtolower($query))
                || str_contains(strtolower($label->ar ?? ''), strtolower($query))
                || str_contains(strtolower($label->code ?? ''), strtolower($query));

            $productsQuery = Product::query()
                ->where('status', Product::STATUS_PUBLISHED)
                ->where(function ($q) use ($label) {
                    $q->whereHas('productKeywords', fn ($qk) => $qk->where('product_keywords.label_id', $label->id))
                        ->orWhereHas('keywords', fn ($k) => $k->where('keywords.label_id', $label->id));
                })
                ->when(! $labelMatchesQuery, function ($q) use ($query) {
                    $q->where('name', 'ilike', "%$query%");
                });

            $applyFilters($productsQuery);

            $productsPaginator = $productsQuery
                ->with([
                    'images',
                    'store.user',
                    'savedProducts' => function ($query) use ($userId) {
                        return $userId === null
                            ? $query->whereRaw('1 = 0')
                            : $query->where('user_id', $userId);
                    },
                ])
                ->latest('id')
                ->simplePaginate($productsPerPage);

            if ($productsPaginator->isEmpty()) {
                return null;
            }

            return [
                'type' => 'label',
                'label' => [
                    'id' => $label->id,
                    'code' => $label->code,
                    'en' => $label->en,
                    'fr' => $label->fr,
                    'ar' => $label->ar,
                    'is_liked' => $userId !== null && SavedLabel::where('label_id', $label->id)->where('user_id', $userId)->exists(),
                ],
                'products' => collect($productsPaginator->items())->map(fn (Product $p) => $p->formatProduct($p, $user))->values(),
                'next_page' => $productsPaginator->hasMorePages() ? 2 : null,
                'nb_likes' => SavedLabel::where('label_id', $label->id)->count(),
            ];
        })->filter();

        $data = $sections->concat($labelSections)->values();

        $data = Advertisement::injectIntoFeed($data)->values();

        return response()->json([
            'data' => $data,
            'next_page' => $labels->hasMorePages() ? $labels->currentPage() + 1 : null,
        ]);
    }
}
