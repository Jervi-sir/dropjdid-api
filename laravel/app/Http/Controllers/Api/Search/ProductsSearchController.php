<?php

namespace App\Http\Controllers\Api\Search;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\Label;
use App\Models\Product;
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

        $sections = collect();

        if ($page == 1) {
            $similarProducts = Product::query()
                ->where('status', Product::STATUS_PUBLISHED)
                ->where(function ($q) use ($query) {
                    $q->where('name', 'ilike', "%$query%")
                        ->orWhereHas('keywords', fn ($qk) => $qk->where('code', 'ilike', "%$query%"))
                        ->orWhereHas('productKeywords.label', fn ($ql) => $ql->where('en', 'ilike', "%$query%"))
                        ->orWhereHas('productKeywords.label', fn ($ql) => $ql->where('fr', 'ilike', "%$query%"))
                        ->orWhereHas('productKeywords.label', fn ($ql) => $ql->where('ar', 'ilike', "%$query%"));
                })
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
            ->where(function ($q) use ($query) {
                // Label name matches query
                $q->where(function ($nq) use ($query) {
                    $nq->where('en', 'ilike', "%$query%")
                        ->orWhere('fr', 'ilike', "%$query%")
                        ->orWhere('ar', 'ilike', "%$query%")
                        ->orWhere('code', 'ilike', "%$query%");
                })
                    ->whereHas('keywords.productKeywords.product', function ($pq) {
                        $pq->where('status', Product::STATUS_PUBLISHED);
                    })
                // OR it has products whose name matches the query
                    ->orWhereHas('keywords.productKeywords.product', function ($pq) use ($query) {
                        $pq->where('status', Product::STATUS_PUBLISHED)
                            ->where('name', 'ilike', "%$query%");
                    });
            })
            ->simplePaginate($perPage);

        $labelSections = collect($labels->items())->map(function (Label $label) use ($query, $productsPerPage, $userId, $user) {
            $labelMatchesQuery = str_contains(strtolower($label->en ?? ''), strtolower($query))
                || str_contains(strtolower($label->fr ?? ''), strtolower($query))
                || str_contains(strtolower($label->ar ?? ''), strtolower($query))
                || str_contains(strtolower($label->code ?? ''), strtolower($query));

            $productsPaginator = Product::query()
                ->where('status', Product::STATUS_PUBLISHED)
                ->where(function ($q) use ($label) {
                    $q->whereHas('productKeywords', fn ($qk) => $qk->where('product_keywords.label_id', $label->id))
                        ->orWhereHas('keywords', fn ($k) => $k->where('keywords.label_id', $label->id));
                })
                ->when(! $labelMatchesQuery, function ($q) use ($query) {
                    $q->where('name', 'ilike', "%$query%");
                })
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
                ],
                'products' => collect($productsPaginator->items())->map(fn (Product $p) => $p->formatProduct($p, $user))->values(),
                'next_page' => $productsPaginator->hasMorePages() ? 2 : null,
                'nb_likes' => 0,
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
