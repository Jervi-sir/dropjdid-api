<?php

namespace App\Http\Controllers\Api\Feeds;

use App\Http\Controllers\Controller;
use App\Models\CreatorFollower;
use App\Models\Drop;
use App\Models\Keyword;
use App\Models\Label;
use App\Models\LabelCategory;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Get search suggestions for keywords, labels, and categories matching user input,
     * along with direct preview results for profiles, products, and drops.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function suggestKeywords(Request $request): JsonResponse
    {
        $currentUserId = $request->user('sanctum')?->id ?? $request->user()?->id;
        $rawQuery = (string) ($request->query('query') ?? $request->query('q') ?? $request->query('keyword') ?? '');
        $query = trim($rawQuery);
        $limit = max(1, min(50, (int) $request->query('limit', 12)));

        if ($query === '') {
            // Return top popular labels & keywords when query is empty
            $popularLabels = Label::query()
                ->with('category')
                ->withCount('products')
                ->orderByDesc('products_count')
                ->limit($limit)
                ->get();

            $results = $popularLabels->map(function ($lbl) {
                return [
                    'id' => (int) $lbl->id,
                    'type' => 'label',
                    'text' => (string) ($lbl->en ?? $lbl->code),
                    'code' => (string) $lbl->code,
                    'label_id' => (int) $lbl->id,
                    'label' => (string) ($lbl->en ?? $lbl->code),
                    'category_id' => (int) ($lbl->category?->id ?? 0),
                    'category' => (string) ($lbl->category?->en ?? $lbl->category?->code ?? ''),
                    'products_count' => (int) ($lbl->products_count ?? 0),
                ];
            })->values();

            return response()->json([
                'query' => '',
                'data' => $results,
                'suggestions' => $results->pluck('text')->unique()->values(),
                'direct_results' => [
                    'profiles' => [],
                    'products' => [],
                    'drops' => [],
                ],
            ], 200);
        }

        $term = '%' . strtolower($query) . '%';
        $cleanSearch = ltrim($query, '@');
        $cleanTerm = '%' . strtolower($cleanSearch) . '%';

        // 1. Match Keywords
        $keywords = Keyword::query()
            ->with(['label.category'])
            ->where('code', 'ILIKE', $term)
            ->get();

        // 2. Match Labels
        $labels = Label::query()
            ->with(['category'])
            ->where(function ($q) use ($term) {
                $q->where('code', 'ILIKE', $term)
                  ->orWhere('en', 'ILIKE', $term)
                  ->orWhere('fr', 'ILIKE', $term)
                  ->orWhere('ar', 'ILIKE', $term);
            })
            ->withCount('products')
            ->get();

        // 3. Match Label Categories
        $categories = LabelCategory::query()
            ->with(['labels.category'])
            ->where(function ($q) use ($term) {
                $q->where('code', 'ILIKE', $term)
                  ->orWhere('en', 'ILIKE', $term)
                  ->orWhere('fr', 'ILIKE', $term)
                  ->orWhere('ar', 'ILIKE', $term);
            })
            ->get();

        $suggestionsCollection = collect();

        // Process Keywords matches
        foreach ($keywords as $kw) {
            $label = $kw->label;
            $cat = $label?->category;
            $productsCount = $label ? Product::whereHas('labels', fn ($q) => $q->where('labels.id', $label->id))->count() : 0;

            $kwCode = (string) $kw->code;
            $score = strcasecmp($kwCode, $query) === 0 ? 100 : (str_starts_with(strtolower($kwCode), strtolower($query)) ? 80 : 50);

            $suggestionsCollection->push([
                'id' => (int) $kw->id,
                'type' => 'keyword',
                'text' => ucfirst($kwCode),
                'code' => $kwCode,
                'label_id' => (int) ($label?->id ?? 0),
                'label' => (string) ($label?->en ?? $label?->code ?? ''),
                'category_id' => (int) ($cat?->id ?? 0),
                'category' => (string) ($cat?->en ?? $cat?->code ?? ''),
                'products_count' => $productsCount,
                'score' => $score,
            ]);
        }

        // Process Labels matches
        foreach ($labels as $lbl) {
            $labelText = (string) ($lbl->en ?? $lbl->code);
            $cat = $lbl->category;
            $score = strcasecmp($labelText, $query) === 0 || strcasecmp($lbl->code, $query) === 0
                ? 110
                : (str_starts_with(strtolower($labelText), strtolower($query)) ? 90 : 60);

            $suggestionsCollection->push([
                'id' => (int) $lbl->id,
                'type' => 'label',
                'text' => $labelText,
                'code' => (string) $lbl->code,
                'label_id' => (int) $lbl->id,
                'label' => $labelText,
                'category_id' => (int) ($cat?->id ?? 0),
                'category' => (string) ($cat?->en ?? $cat?->code ?? ''),
                'products_count' => (int) ($lbl->products_count ?? 0),
                'score' => $score,
            ]);
        }

        // Process Categories matches
        foreach ($categories as $cat) {
            $catText = (string) ($cat->en ?? $cat->code);
            $score = strcasecmp($catText, $query) === 0 ? 95 : (str_starts_with(strtolower($catText), strtolower($query)) ? 75 : 40);

            $suggestionsCollection->push([
                'id' => (int) $cat->id,
                'type' => 'category',
                'text' => $catText,
                'code' => (string) $cat->code,
                'label_id' => null,
                'label' => null,
                'category_id' => (int) $cat->id,
                'category' => $catText,
                'products_count' => Product::whereHas('labels', fn ($q) => $q->where('label_category_id', $cat->id))->count(),
                'score' => $score,
            ]);
        }

        // Sort by score descending and deduplicate by lowercased text
        $sorted = $suggestionsCollection
            ->sortByDesc('score')
            ->unique(fn ($item) => strtolower($item['text']))
            ->take($limit)
            ->values();

        // Strip internal score before returning
        $finalData = $sorted->map(function ($item) {
            unset($item['score']);
            return $item;
        });

        // 4. Fetch Direct Entity Matches (Instagram-style preview results)

        // A. Direct Profiles / Users
        $directUsers = User::query()
            ->where('is_active', '!=', false)
            ->where(function ($q) use ($cleanTerm) {
                $q->where('username', 'ILIKE', $cleanTerm)
                  ->orWhere('full_name', 'ILIKE', $cleanTerm)
                  ->orWhere('email', 'ILIKE', $cleanTerm);
            })
            ->latest('created_at')
            ->limit(6)
            ->get();

        $followedUserIds = collect();
        if ($currentUserId && $directUsers->isNotEmpty()) {
            $followedUserIds = CreatorFollower::where('user_id', $currentUserId)
                ->whereIn('creator_id', $directUsers->pluck('id'))
                ->pluck('creator_id');
        }

        $directProfiles = $directUsers->map(function (User $user) use ($followedUserIds) {
            $imageUrl = $user->image_url ?? '';
            if ($imageUrl && ! str_starts_with($imageUrl, 'http://') && ! str_starts_with($imageUrl, 'https://')) {
                $imageUrl = url($imageUrl);
            }

            $fullName = (string) ($user->full_name ?? $user->name ?? $user->username ?? 'User #' . $user->id);
            $username = (string) ($user->username ?? '');

            return [
                'id' => (int) $user->id,
                'name' => $fullName,
                'username' => '@' . ltrim($username, '@'),
                'image_url' => (string) $imageUrl,
                'is_following' => $followedUserIds->contains($user->id),
            ];
        })->values();

        // B. Direct Products
        $directProductsRaw = Product::query()
            ->with(['mainImage', 'images', 'store'])
            ->where(function ($q) {
                $q->where('product_status', 'published')
                  ->orWhereNull('product_status');
            })
            ->where(function ($q) use ($term) {
                $q->where('name', 'ILIKE', $term)
                  ->orWhere('description', 'ILIKE', $term)
                  ->orWhereHas('store', fn ($storeQuery) => $storeQuery->where('name', 'ILIKE', $term))
                  ->orWhereHas('labels', fn ($lblQuery) => $lblQuery->where('code', 'ILIKE', $term)->orWhere('en', 'ILIKE', $term));
            })
            ->latest('created_at')
            ->limit(6)
            ->get();

        $directProducts = $directProductsRaw->map(function (Product $product) {
            $imageUrl = $product->mainImage?->image_url
                ?? $product->images->first()?->image_url
                ?? '';

            if ($imageUrl && ! str_starts_with($imageUrl, 'http://') && ! str_starts_with($imageUrl, 'https://')) {
                $imageUrl = url($imageUrl);
            }

            $price = $product->price_shown ?? $product->price_original ?? 0;
            $formattedPrice = number_format((float) $price, 0, '.', ' ') . ' DZD';

            return [
                'id' => (int) $product->id,
                'title' => (string) ($product->name ?? 'Product #' . $product->id),
                'price' => $formattedPrice,
                'store_name' => (string) ($product->store?->name ?? 'Store'),
                'image_url' => (string) $imageUrl,
            ];
        })->values();

        // C. Direct Drops
        $directDropsRaw = Drop::query()
            ->with(['creator', 'mainImage', 'images'])
            ->where(function ($q) {
                $q->where('drop_status', 'published')
                  ->orWhereNull('drop_status');
            })
            ->where(function ($q) use ($term, $cleanTerm) {
                $q->where('title', 'ILIKE', $term)
                  ->orWhere('description', 'ILIKE', $term)
                  ->orWhereHas('creator', function ($creatorQuery) use ($cleanTerm) {
                      $creatorQuery->where('username', 'ILIKE', $cleanTerm)
                                   ->orWhere('full_name', 'ILIKE', $cleanTerm);
                  });
            })
            ->latest('created_at')
            ->limit(6)
            ->get();

        $directDrops = $directDropsRaw->map(function (Drop $drop) {
            $imageUrl = $drop->mainImage?->image
                ?? $drop->images->first()?->image
                ?? '';

            if ($imageUrl && ! str_starts_with($imageUrl, 'http://') && ! str_starts_with($imageUrl, 'https://')) {
                $imageUrl = url($imageUrl);
            }

            $creatorUsername = $drop->creator ? '@' . ltrim($drop->creator->username, '@') : '';

            return [
                'id' => (int) $drop->id,
                'title' => (string) ($drop->title ?? 'Drop #' . $drop->id),
                'creator' => $creatorUsername,
                'image_url' => (string) $imageUrl,
            ];
        })->values();

        return response()->json([
            'query' => $query,
            'data' => $finalData,
            'suggestions' => $finalData->pluck('text')->values(),
            'direct_results' => [
                'profiles' => $directProfiles,
                'products' => $directProducts,
                'drops' => $directDrops,
            ],
        ], 200);
    }
}

