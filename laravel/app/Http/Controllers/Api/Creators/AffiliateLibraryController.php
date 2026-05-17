<?php

namespace App\Http\Controllers\Api\Creators;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\Label;
use App\Models\Product;
use App\Models\SavedProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AffiliateLibraryController extends Controller
{
    public function suggest(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $page = $validated['page'] ?? 1;
        $perPage = $validated['per_page'] ?? 10;

        $user = $request->user();
        $userId = $user?->getAuthIdentifier();

        $sections = [];

        $savedProductIds = $userId === null
            ? []
            : SavedProduct::query()
                ->where('user_id', $userId)
                ->pluck('product_id')
                ->all();

        $labelsPaginator = Label::with('keywords')
            ->simplePaginate($perPage, ['*'], 'page', $page);

        foreach ($labelsPaginator as $label) {
            $payload = $this->labelProductsPayload($label, 1, 10, $userId, $user, $savedProductIds);

            if ($payload['data']->isNotEmpty()) {
                $sections[] = [
                    'type' => 'label',
                    'label_id' => $label->id,
                    'label_name' => $label->feedName(),
                    'products' => $payload['data']->values()->all(),
                    'next_page' => $payload['next_page'],
                ];
            }
        }

        $sections = collect($sections);
        $sections = Advertisement::injectIntoFeed($sections, interval: 2, adsCount: 4);

        return response()->json([
            'data' => $sections->values()->all(),
            'next_page' => $labelsPaginator->hasMorePages() ? $labelsPaginator->currentPage() + 1 : null,
        ]);
    }

    /**
     * @return array{data: Collection<int, array>, next_page: ?int}
     */
    private function labelProductsPayload(Label $label, int $page, int $perPage, ?int $userId, $user, array $savedProductIds): array
    {
        $keywordIds = $label->keywords->pluck('id')->all();

        $paginator = $this->baseProductsQuery($userId)
            ->whereHas('keywords', fn ($query) => $query->whereIn('keywords.id', $keywordIds))
            ->when($savedProductIds !== [], fn ($query) => $query->whereNotIn('id', $savedProductIds))
            ->latest('id')
            ->simplePaginate($perPage, ['*'], "label_{$label->id}_page", $page);

        return [
            'data' => collect($paginator->items())
                ->map(fn (Product $product): array => $product->formatProduct($product, $user)),
            'next_page' => $paginator->hasMorePages() ? $paginator->currentPage() + 1 : null,
        ];
    }

    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => ['nullable', 'string', 'max:255'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'products_per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $query = trim($validated['query'] ?? '');

        $perPage = $validated['per_page'] ?? 5;
        $productsPerPage = $validated['products_per_page'] ?? 10;
        $user = $request->user();
        $userId = $user?->getAuthIdentifier();
        $page = $request->query('page', 1);

        $sections = collect();

        if ($page == 1) {
            $similarProducts = $this->baseProductsQuery($userId)
                ->where(function ($q) use ($query) {
                    $q->where('name', 'ilike', "%$query%")
                        ->orWhere('description', 'ilike', "%$query%")
                        ->orWhereHas('keywords', fn ($qk) => $qk->where('code', 'ilike', "%$query%"))
                        ->orWhereHas('productKeywords.label', fn ($ql) => $ql->where('en', 'ilike', "%$query%"))
                        ->orWhereHas('productKeywords.label', fn ($ql) => $ql->where('fr', 'ilike', "%$query%"))
                        ->orWhereHas('productKeywords.label', fn ($ql) => $ql->where('ar', 'ilike', "%$query%"));
                })
                ->latest('id')
                ->limit(10)
                ->get();

            if ($similarProducts->isNotEmpty()) {
                $sections->push([
                    'type' => 'label',
                    'label_id' => -1,
                    'label_name' => 'Similar',
                    'products' => $similarProducts->map(fn (Product $p) => $p->formatProduct($p, $user))->values(),
                    'next_page' => null,
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
                        $pq->whereHas('paymentMethod', fn ($query) => $query->where('is_online', true));
                    })
                // OR it has keywords matching the query
                    ->orWhereHas('keywords', function ($kq) use ($query) {
                        $kq->where('code', 'ilike', "%$query%")
                            ->whereHas('productKeywords.product', function ($pq) {
                                $pq->where('status', Product::STATUS_PUBLISHED);
                                $pq->whereHas('paymentMethod', fn ($query) => $query->where('is_online', true));
                            });
                    })
                // OR it has products whose name or description matches the query
                    ->orWhereHas('keywords.productKeywords.product', function ($pq) use ($query) {
                        $pq->where('status', Product::STATUS_PUBLISHED)
                            ->whereHas('paymentMethod', fn ($query) => $query->where('is_online', true))
                            ->where(function ($sq) use ($query) {
                                $sq->where('name', 'ilike', "%$query%")
                                    ->orWhere('description', 'ilike', "%$query%");
                            });
                    });
            })
            ->simplePaginate($perPage);

        $labelSections = collect($labels->items())->map(function (Label $label) use ($query, $productsPerPage, $userId, $user) {
            $labelMatchesQuery = str_contains(strtolower($label->en ?? ''), strtolower($query))
                || str_contains(strtolower($label->fr ?? ''), strtolower($query))
                || str_contains(strtolower($label->ar ?? ''), strtolower($query))
                || str_contains(strtolower($label->code ?? ''), strtolower($query))
                || $label->keywords()->where('code', 'ilike', "%$query%")->exists();

            $productsPaginator = $this->baseProductsQuery($userId)
                ->where(function ($q) use ($label) {
                    $q->whereHas('productKeywords', fn ($qk) => $qk->where('product_keywords.label_id', $label->id))
                        ->orWhereHas('keywords', fn ($k) => $k->where('keywords.label_id', $label->id));
                })
                ->when(! $labelMatchesQuery, function ($q) use ($query) {
                    $q->where(function ($sq) use ($query) {
                        $sq->where('name', 'ilike', "%$query%")
                            ->orWhere('description', 'ilike', "%$query%");
                    });
                })
                ->latest('id')
                ->simplePaginate($productsPerPage);

            if ($productsPaginator->isEmpty()) {
                return null;
            }

            return [
                'type' => 'label',
                'label_id' => $label->id,
                'label_name' => $label->feedName(),
                'products' => collect($productsPaginator->items())->map(fn (Product $p) => $p->formatProduct($p, $user))->values(),
                'next_page' => $productsPaginator->hasMorePages() ? 2 : null,
            ];
        })->filter();

        $data = $sections->concat($labelSections)->values();

        $data = Advertisement::injectIntoFeed($data)->values();

        return response()->json([
            'data' => $data,
            'next_page' => $labels->hasMorePages() ? $labels->currentPage() + 1 : null,
        ]);
    }

    private function baseProductsQuery(?int $userId)
    {
        return Product::query()
            ->where('status', Product::STATUS_PUBLISHED)
            // ->whereHas('paymentMethod', fn ($query) => $query->where('is_online', true))
            ->with([
                'images',
                'store.user',
                'savedProducts' => function ($query) use ($userId) {
                    return $userId === null
                        ? $query->whereRaw('1 = 0')
                        : $query->where('user_id', $userId);
                },
            ]);
    }
}
