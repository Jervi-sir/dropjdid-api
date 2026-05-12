<?php

namespace App\Http\Controllers\Api\Search;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
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
        ]);

        $query = trim($validated['query']);

        if ($query === '') {
            return response()->json([
                'data' => [],
                'next_page' => null,
            ]);
        }

        $perPage = $validated['per_page'] ?? 10;
        $user = $request->user();
        $userId = $user?->getAuthIdentifier();

        $products = Product::query()
            ->where('status', 'published')
            ->where(function (Builder $builder) use ($query): void {
                $builder
                    ->where('name', 'like', '%'.$query.'%')
                    ->orWhereHas('keywords', function (Builder $keywordQuery) use ($query): void {
                        $keywordQuery
                            ->where('code', 'like', '%'.$query.'%')
                            ->orWhereHas('label', function (Builder $labelQuery) use ($query): void {
                                $labelQuery
                                    ->where('code', 'like', '%'.$query.'%')
                                    ->orWhere('en', 'like', '%'.$query.'%')
                                    ->orWhere('fr', 'like', '%'.$query.'%')
                                    ->orWhere('ar', 'like', '%'.$query.'%');
                            });
                    })
                    ->orWhereHas('productKeywords.label', function (Builder $labelQuery) use ($query): void {
                        $labelQuery
                            ->where('code', 'like', '%'.$query.'%')
                            ->orWhere('en', 'like', '%'.$query.'%')
                            ->orWhere('fr', 'like', '%'.$query.'%')
                            ->orWhere('ar', 'like', '%'.$query.'%');
                    });
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
            ->simplePaginate($perPage);

        return response()->json([
            'data' => collect($products->items())->map(fn (Product $product): array => $product->formatProduct($product, $user))->values(),
            'next_page' => $products->hasMorePages() ? $products->currentPage() + 1 : null,
        ]);
    }
}
