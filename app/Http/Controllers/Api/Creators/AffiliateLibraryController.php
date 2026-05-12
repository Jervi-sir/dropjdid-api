<?php

namespace App\Http\Controllers\Api\Creators;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\Label;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\SavedProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AffiliateLibraryController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'saved_page' => ['nullable', 'integer', 'min:1'],
            'saved_per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $user = $request->user();
        $userId = $user?->getAuthIdentifier();

        $savedPayload = $this->savedProductsPayload(
            $validated['saved_page'] ?? 1,
            $validated['saved_per_page'] ?? 10,
            $userId,
            $user,
        );

        $savedProductIds = $userId === null
            ? []
            : SavedProduct::query()
                ->where('user_id', $userId)
                ->pluck('product_id')
                ->all();

        $sections = [
            [
                'type' => 'saved_products',
                'label' => 'Saved products',
                'products' => $savedPayload['data']->values()->all(),
                'next_page' => $savedPayload['next_page'],
            ],
        ];

        $labels = Label::with('keywords')->get();

        foreach ($labels as $label) {
            $pageKey = "label_{$label->id}_page";
            $perPageKey = "label_{$label->id}_per_page";

            $labelPage = (int) $request->input($pageKey, 1);
            $labelPerPage = (int) $request->input($perPageKey, 10);

            $payload = $this->labelProductsPayload($label, $labelPage, $labelPerPage, $userId, $user, $savedProductIds);

            if ($payload['data']->isNotEmpty() || $labelPage > 1) {
                $sections[] = [
                    'type' => "label_{$label->id}",
                    'label' => $label->feedName(),
                    'products' => $payload['data']->values()->all(),
                    'next_page' => $payload['next_page'],
                ];
            }
        }

        $sections = collect($sections);
        $sections = Advertisement::injectIntoFeed($sections, interval: 2, adsCount: 4);

        return response()->json([
            'data' => $sections->values()->all(),
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

    /**
     * @return array{data: Collection<int, array>, next_page: ?int}
     */
    private function savedProductsPayload(int $page, int $perPage, ?int $userId, $user): array
    {
        if ($userId === null) {
            return [
                'data' => collect(),
                'next_page' => null,
            ];
        }

        $paginator = SavedProduct::query()
            ->where('user_id', $userId)
            ->whereHas('product', fn ($query) => $this->applyOnlinePublishedConstraints($query))
            ->with([
                'product.images',
                'product.store.user',
                'product.savedProducts' => fn ($query) => $query->where('user_id', $userId),
            ])
            ->latest('id')
            ->simplePaginate($perPage, ['*'], 'saved_page', $page);

        return [
            'data' => collect($paginator->items())
                ->map(fn (SavedProduct $savedProduct): ?array => $savedProduct->product?->formatProduct($savedProduct->product, $user))
                ->filter()
                ->values(),
            'next_page' => $paginator->hasMorePages() ? $paginator->currentPage() + 1 : null,
        ];
    }

    private function baseProductsQuery(?int $userId)
    {
        return Product::query()
            ->where('status', 'published')
            ->whereHas('paymentMethod', fn ($query) => $query->where('code', PaymentMethod::ONLINE))
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

    private function applyOnlinePublishedConstraints($query): void
    {
        $query
            ->where('status', 'published')
            ->whereHas('paymentMethod', fn ($paymentMethodQuery) => $paymentMethodQuery->where('code', PaymentMethod::ONLINE));
    }
}
