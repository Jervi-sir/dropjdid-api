<?php

namespace App\Http\Controllers\Api\Catalogs;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Commune;
use App\Models\DeliveryCompany;
use App\Models\Gender;
use App\Models\Product;
use App\Models\Quality;
use App\Models\Size;
use App\Models\StoreToDeliveryCost;
use App\Models\Wilaya;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FilterCatalogController extends Controller
{
    /**
     * Return the available catalog metadata that can be used in filters & checkout:
     * - Price range: minimum price, maximum price, default/step, is_unlimited
     * - Qualities: list of quality options
     * - Genders / For: list of gender options
     * - Types & Sizes: categories with their associated sizes
     * - Wilayas: list of wilayas (optional with nested communes)
     * - Communes: list of communes (filtered by wilaya_id / wilaya_code if provided)
     * - Delivery types: available delivery methods (home, stopdesk)
     * - Delivery costs: delivery cost table for store or selected wilaya
     *
     * @param Request $request
     * @return JsonResponse
     * 
     * includes
     * price, qualities, genders, types, wilayas, communes, delivery_types, delivery_costs
     */
    public function __invoke(Request $request): JsonResponse
    {
        $wilayaId = $request->query('wilaya_id') ?? $request->query('wilaya');
        $storeId = $request->query('store_id') ?? $request->query('store');
        $withCommunes = filter_var($request->query('with_communes', false), FILTER_VALIDATE_BOOLEAN);

        // Parse optional `includes` query parameter: e.g. "price,qualities,genders"
        $includesParam = $request->query('includes') ?? $request->query('include');
        $includes = null;
        if (!empty($includesParam)) {
            $includes = array_map('trim', explode(',', (string) $includesParam));
        }

        $shouldInclude = function (string $key) use ($includes): bool {
            return $includes === null || in_array($key, $includes, true);
        };

        $response = [];

        // 1. Calculate price range from actual published products (or fallbacks)
        if ($shouldInclude('price')) {
            $dbMinPrice = Product::where('product_status', 'published')
                ->min('price_shown');
            if ($dbMinPrice === null) {
                $dbMinPrice = Product::where('product_status', 'published')->min('price_original');
            }

            $dbMaxPrice = Product::where('product_status', 'published')
                ->max('price_shown');
            if ($dbMaxPrice === null) {
                $dbMaxPrice = Product::where('product_status', 'published')->max('price_original');
            }

            $minPrice = $dbMinPrice !== null ? (int) floor($dbMinPrice) : 500;
            $maxPrice = $dbMaxPrice !== null ? (int) ceil($dbMaxPrice) : 50000;

            $response['price'] = [
                'min' => $minPrice,
                'max' => $maxPrice,
                'default_min' => $minPrice,
                'default_max' => null, // null represents unlimited / 'no limits'
                'is_unlimited' => true,
                'currency' => 'DZD',
            ];
        }

        // 2. Qualities
        if ($shouldInclude('qualities') || $shouldInclude('quality')) {
            $response['qualities'] = Quality::query()
                ->orderBy('id', 'asc')
                ->get()
                ->map(function (Quality $quality) {
                    return [
                        'id' => (int) $quality->id,
                        'code' => (string) $quality->code,
                        'name' => (string) ($quality->en ?? $quality->code),
                        'en' => (string) ($quality->en ?? ''),
                        'fr' => (string) ($quality->fr ?? ''),
                        'ar' => (string) ($quality->ar ?? ''),
                    ];
                })->values();
        }

        // 3. Genders ("For" section)
        if ($shouldInclude('genders') || $shouldInclude('gender')) {
            $response['genders'] = Gender::query()
                ->orderBy('id', 'asc')
                ->get()
                ->map(function (Gender $gender) {
                    return [
                        'id' => (int) $gender->id,
                        'code' => (string) $gender->code,
                        'name' => (string) ($gender->en ?? $gender->code),
                        'en' => (string) ($gender->en ?? ''),
                        'fr' => (string) ($gender->fr ?? ''),
                        'ar' => (string) ($gender->ar ?? ''),
                    ];
                })->values();
        }

        // 4. Types with their associated sizes
        if ($shouldInclude('types') || $shouldInclude('type') || $shouldInclude('sizes')) {
            $response['types'] = Category::query()
                ->with(['sizes' => function ($q) {
                    $q->orderBy('id', 'asc');
                }])
                ->orderBy('id', 'asc')
                ->get()
                ->map(function (Category $category) {
                    return [
                        'id' => (int) $category->id,
                        'code' => (string) $category->code,
                        'name' => (string) ($category->en ?? $category->code),
                        'en' => (string) ($category->en ?? ''),
                        'fr' => (string) ($category->fr ?? ''),
                        'ar' => (string) ($category->ar ?? ''),
                        'sizes' => $category->sizes->map(function (Size $size) {
                            return [
                                'id' => (int) $size->id,
                                'code' => (string) $size->code,
                                'name' => (string) ($size->en ?? $size->code),
                                'type' => (string) ($size->type ?? ''),
                                'en' => (string) ($size->en ?? ''),
                                'fr' => (string) ($size->fr ?? ''),
                                'ar' => (string) ($size->ar ?? ''),
                            ];
                        })->values(),
                    ];
                })->values();
        }

        // 5. Wilayas (with optional nested communes)
        if ($shouldInclude('wilayas') || $shouldInclude('wilaya')) {
            $wilayasQuery = Wilaya::query()->orderBy('number', 'asc');
            if ($withCommunes) {
                $wilayasQuery->with(['communes' => fn($q) => $q->orderBy('en', 'asc')]);
            }

            $response['wilayas'] = $wilayasQuery->get()->map(function (Wilaya $w) use ($withCommunes) {
                $item = [
                    'id' => (int) $w->id,
                    'number' => (string) ($w->number ?? str_pad((string) $w->id, 2, '0', STR_PAD_LEFT)),
                    'code' => (string) $w->code,
                    'name' => (string) ($w->en ?? $w->code),
                    'en' => (string) ($w->en ?? ''),
                    'fr' => (string) ($w->fr ?? ''),
                    'ar' => (string) ($w->ar ?? ''),
                ];

                if ($withCommunes && $w->relationLoaded('communes')) {
                    $item['communes'] = $w->communes->map(function (Commune $c) {
                        return [
                            'id' => (int) $c->id,
                            'wilaya_id' => (int) $c->wilaya_id,
                            'code' => (string) $c->code,
                            'post_code' => (string) ($c->post_code ?? ''),
                            'name' => (string) ($c->en ?? $c->code),
                            'en' => (string) ($c->en ?? ''),
                            'fr' => (string) ($c->fr ?? ''),
                            'ar' => (string) ($c->ar ?? ''),
                        ];
                    })->values();
                }

                return $item;
            })->values();
        }

        // 6. Communes (all or filtered by specific wilaya)
        if ($shouldInclude('communes') || $shouldInclude('commune')) {
            $communesQuery = Commune::query()->orderBy('en', 'asc');
            if ($wilayaId) {
                if (is_numeric($wilayaId)) {
                    $communesQuery->where('wilaya_id', (int) $wilayaId);
                } else {
                    $communesQuery->whereHas('wilaya', function ($q) use ($wilayaId) {
                        $q->where('code', $wilayaId)->orWhere('number', $wilayaId);
                    });
                }
            }

            $response['communes'] = $communesQuery->get()->map(function (Commune $c) {
                return [
                    'id' => (int) $c->id,
                    'wilaya_id' => (int) $c->wilaya_id,
                    'code' => (string) $c->code,
                    'post_code' => (string) ($c->post_code ?? ''),
                    'name' => (string) ($c->en ?? $c->code),
                    'en' => (string) ($c->en ?? ''),
                    'fr' => (string) ($c->fr ?? ''),
                    'ar' => (string) ($c->ar ?? ''),
                ];
            })->values();
        }

        // 7. Delivery Types (Delivery Methods)
        if ($shouldInclude('delivery_types') || $shouldInclude('delivery_type')) {
            $response['delivery_types'] = [
                [
                    'code' => 'domicile',
                    'name' => 'Home Delivery',
                    'en' => 'Home Delivery',
                    'fr' => 'Livraison à Domicile',
                    'ar' => 'التوصيل للمنزل',
                    'description' => 'Delivery directly to your address',
                ],
                [
                    'code' => 'stopdesk',
                    'name' => 'Delivery to Office (Stop Desk)',
                    'en' => 'Delivery to Office (Stop Desk)',
                    'fr' => 'Livraison au Bureau (Stop Desk)',
                    'ar' => 'التوصيل للمكتب',
                    'description' => 'Pickup from the nearest express delivery agency',
                ],
            ];
        }

        // 8. Delivery Costs (Filtered by store and/or wilaya if provided, or default overview)
        if ($shouldInclude('delivery_costs') || $shouldInclude('delivery_cost')) {
            $costsQuery = StoreToDeliveryCost::query()
                ->with(['deliveryCompany', 'wilaya'])
                ->where('is_active', true);

            if ($storeId) {
                $costsQuery->where('store_id', (int) $storeId);
            }

            if ($wilayaId) {
                if (is_numeric($wilayaId)) {
                    $costsQuery->where('wilaya_id', (int) $wilayaId);
                } else {
                    $costsQuery->whereHas('wilaya', function ($q) use ($wilayaId) {
                        $q->where('code', $wilayaId)->orWhere('number', $wilayaId);
                    });
                }
            }

            $response['delivery_costs'] = $costsQuery->limit(100)->get()->map(function (StoreToDeliveryCost $cost) {
                return [
                    'id' => (int) $cost->id,
                    'store_id' => (int) $cost->store_id,
                    'wilaya_id' => $cost->wilaya_id ? (int) $cost->wilaya_id : null,
                    'wilaya_number' => (string) ($cost->wilaya?->number ?? ''),
                    'wilaya_name' => (string) ($cost->wilaya_name ?? $cost->wilaya?->en ?? ''),
                    'delivery_company' => $cost->deliveryCompany ? [
                        'id' => (int) $cost->deliveryCompany->id,
                        'code' => (string) $cost->deliveryCompany->code,
                        'name' => (string) $cost->deliveryCompany->name,
                        'logo_url' => $cost->deliveryCompany->logo_url ? url($cost->deliveryCompany->logo_url) : null,
                    ] : [
                        'code' => (string) ($cost->delivery_company_code ?? 'swift_express'),
                        'name' => 'Swift Express',
                    ],
                    'cost_domicile' => (float) ($cost->cost_domicile ?? 0.00),
                    'cost_stopdesk' => (float) ($cost->cost_stopdesk ?? 0.00),
                    'cost_cancel' => (float) ($cost->cost_cancel ?? 0.00),
                    'currency' => 'DZD',
                ];
            })->values();
        }

        return response()->json($response, 200);
    }
}
