<?php

namespace App\Http\Controllers\Api\Products\PaginatedBy;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\Label;
use App\Models\Product;
use App\Models\SavedLabel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ByLabelProductController extends Controller
{
    public function __invoke(Request $request, int $product_id): JsonResponse
    {
        $userId = $request->user()?->id;
        $startWithAds = $request->boolean('start_with_ads', false);

        $labelIds = Product::findOrFail($product_id)
            ->productKeywords()
            ->pluck('label_id')
            ->unique()
            ->values();

        $labels = Label::query()
            ->whereIn('id', $labelIds)
            ->simplePaginate(5);

        $data = collect($labels->items())->map(function (Label $label) use ($product_id, $userId) {
            $products = Product::query()
                ->where('status', Product::STATUS_PUBLISHED)
                ->where('id', '!=', $product_id)
                ->whereHas('productKeywords', fn (Builder $query) => $query->where('label_id', $label->id))
                ->with(['images', 'store.user', 'savedProducts' => function ($query) use ($userId) {
                    return $userId === null ? $query->whereRaw('1 = 0') : $query->where('user_id', $userId);
                }])
                ->inRandomOrder()
                ->limit(10)
                ->get();

            $nbLikes = SavedLabel::where('label_id', $label->id)->count();
            $isLiked = $userId !== null && SavedLabel::where('label_id', $label->id)->where('user_id', $userId)->exists();

            return [
                'type' => 'label',
                'label' => [
                    'id' => $label->id,
                    'code' => $label->code,
                    'en' => $label->en,
                    'fr' => $label->fr,
                    'ar' => $label->ar,
                    'is_liked' => $isLiked,
                ],
                'products' => $products->map(fn (Product $p) => [
                    'id' => $p->id,
                    'image' => $p->images->sortBy('sort_order')->first()?->image,
                    'images' => $p->images->sortBy('sort_order')->pluck('image')->values()->all(),
                    'price' => (float) ($p->show_price ?? $p->store_price ?? $p->original_price ?? 0),
                    'user' => [
                        'id' => $p->store?->user?->id,
                        'username' => $p->store?->user?->username,
                    ],
                    'is_saved' => $userId !== null && $p->savedProducts->isNotEmpty(),
                ]),
                'nb_likes' => $nbLikes,
                'next_page' => null,
            ];
        });

        $feed = Advertisement::injectIntoFeed($data, 2, 1, $startWithAds);

        return response()->json([
            'data' => $feed,
            'next_page' => $labels->hasMorePages() ? $labels->currentPage() + 1 : null,
        ]);
    }
}
