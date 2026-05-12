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
}
