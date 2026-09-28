<?php

namespace App\Http\Controllers\Api\Catalogs;

use App\Http\Controllers\Controller;
use App\Models\Commune;
use App\Models\DeliveryCompany;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreToDeliveryCost;
use App\Models\Wilaya;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliveryCatalogController extends Controller
{
    /**
     * 1. List all Wilayas
     * GET /api/catalogs/delivery/wilayas
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getWilayas(Request $request): JsonResponse
    {
        $withCommunes = filter_var($request->query('with_communes', false), FILTER_VALIDATE_BOOLEAN);

        $driver = config('database.default');
        if ($driver === 'pgsql') {
            $query = Wilaya::query()->orderByRaw('CAST(NULLIF(regexp_replace(number, \'[^0-9]\', \'\', \'g\'), \'\') AS INTEGER) ASC NULLS LAST, id ASC');
        } else {
            $query = Wilaya::query()->orderByRaw('CAST(number AS UNSIGNED) ASC, number ASC');
        }

        if ($withCommunes) {
            $query->with(['communes' => fn($q) => $q->orderBy('en', 'asc')]);
        }

        $wilayas = $query->get()->map(function (Wilaya $w) use ($withCommunes) {
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

        return response()->json([
            'success' => true,
            'count' => $wilayas->count(),
            'data' => $wilayas,
        ], 200);
    }

    /**
     * 2. List available delivery companies & pricing for a specific wilaya
     * GET /api/catalogs/delivery/options
     * or GET /api/catalogs/delivery/options/{wilayaId}
     *
     * Params:
     * - wilaya_id / wilaya: wilaya ID, code, or number (required or route param)
     * - store_id / store: optional store filter
     * - product_id / product: optional product filter to resolve store
     *
     * @param Request $request
     * @param int|string|null $wilayaId
     * @return JsonResponse
     */
    public function getDeliveryOptions(Request $request, int|string|null $wilayaId = null): JsonResponse
    {
        $resolvedWilayaParam = $wilayaId ?? $request->query('wilaya_id') ?? $request->query('wilaya');
        $storeId = $request->query('store_id') ?? $request->query('store');
        $productId = $request->query('product_id') ?? $request->query('product');

        // If product_id is provided and store_id is not, resolve store_id from product
        if (! $storeId && $productId) {
            $product = Product::find($productId);
            if ($product) {
                $storeId = $product->store_id;
            }
        }

        // Find Wilaya if parameter provided
        $wilaya = null;
        if ($resolvedWilayaParam !== null && $resolvedWilayaParam !== '') {
            if (is_numeric($resolvedWilayaParam)) {
                $wilaya = Wilaya::where('id', (int) $resolvedWilayaParam)
                    ->orWhere('number', (string) $resolvedWilayaParam)
                    ->first();
            } else {
                $wilaya = Wilaya::where('code', $resolvedWilayaParam)
                    ->orWhere('en', $resolvedWilayaParam)
                    ->orWhere('fr', $resolvedWilayaParam)
                    ->first();
            }
        }

        // Query delivery costs
        $costsQuery = StoreToDeliveryCost::query()
            ->with(['deliveryCompany', 'wilaya'])
            ->where('is_active', true);

        if ($storeId) {
            $costsQuery->where('store_id', (int) $storeId);
        }

        if ($wilaya) {
            $costsQuery->where('wilaya_id', $wilaya->id);
        }

        $costs = $costsQuery->get();

        // If specific wilaya requested but no specific DB cost rows found, build fallback
        if ($wilaya && $costs->isEmpty()) {
            $wilayaNum = (string) ($wilaya->number ?? '');
            $isAlgiersNearby = in_array($wilayaNum, ['16', '09', '35', '42'], true);
            $isSouth = in_array($wilayaNum, ['01', '03', '07', '08', '11', '17', '30', '32', '33', '37', '39', '40', '45', '47', '49', '50', '51', '52', '53', '54', '55', '56', '57', '58'], true);

            $costHome = $isAlgiersNearby ? 400.00 : ($isSouth ? 950.00 : 600.00);
            $costStopdesk = $isAlgiersNearby ? 250.00 : ($isSouth ? 600.00 : 350.00);

            $primaryCompany = DeliveryCompany::where('is_active', true)->first();

            $formattedOptions = [
                [
                    'id' => null,
                    'wilaya_id' => (int) $wilaya->id,
                    'wilaya_number' => (string) ($wilaya->number ?? ''),
                    'wilaya_name' => (string) ($wilaya->en ?? $wilaya->fr ?? $wilaya->code),
                    'delivery_company' => [
                        'id' => $primaryCompany ? (int) $primaryCompany->id : 1,
                        'code' => (string) ($primaryCompany?->code ?? 'swift_express'),
                        'name' => (string) ($primaryCompany?->name ?? 'Swift Express'),
                        'logo_url' => $primaryCompany?->logo_url ? url($primaryCompany->logo_url) : null,
                        'phone' => (string) ($primaryCompany?->phone ?? ''),
                        'website' => (string) ($primaryCompany?->website ?? ''),
                    ],
                    'has_home_delivery' => true,
                    'has_stopdesk_delivery' => true,
                    'cost_domicile' => (float) $costHome,
                    'cost_stopdesk' => (float) $costStopdesk,
                    'cost_cancel' => 200.00,
                    'currency' => 'DZD',
                ],
            ];
        } else {
            $formattedOptions = $costs->map(function (StoreToDeliveryCost $cost) {
                return [
                    'id' => (int) $cost->id,
                    'store_id' => (int) $cost->store_id,
                    'wilaya_id' => $cost->wilaya_id ? (int) $cost->wilaya_id : null,
                    'wilaya_number' => (string) ($cost->wilaya?->number ?? ''),
                    'wilaya_name' => (string) ($cost->wilaya_name ?? $cost->wilaya?->en ?? $cost->wilaya?->fr ?? ''),
                    'delivery_company' => $cost->deliveryCompany ? [
                        'id' => (int) $cost->deliveryCompany->id,
                        'code' => (string) $cost->deliveryCompany->code,
                        'name' => (string) $cost->deliveryCompany->name,
                        'logo_url' => $cost->deliveryCompany->logo_url ? url($cost->deliveryCompany->logo_url) : null,
                        'phone' => (string) ($cost->deliveryCompany->phone ?? ''),
                        'website' => (string) ($cost->deliveryCompany->website ?? ''),
                    ] : [
                        'id' => null,
                        'code' => (string) ($cost->delivery_company_code ?? 'swift_express'),
                        'name' => 'Swift Express',
                        'logo_url' => null,
                        'phone' => null,
                        'website' => null,
                    ],
                    'has_home_delivery' => (float) ($cost->cost_domicile ?? 0) > 0,
                    'has_stopdesk_delivery' => (float) ($cost->cost_stopdesk ?? 0) > 0,
                    'cost_domicile' => (float) ($cost->cost_domicile ?? 0.00),
                    'cost_stopdesk' => (float) ($cost->cost_stopdesk ?? 0.00),
                    'cost_cancel' => (float) ($cost->cost_cancel ?? 0.00),
                    'currency' => 'DZD',
                ];
            })->values();
        }

        return response()->json([
            'success' => true,
            'wilaya' => $wilaya ? [
                'id' => (int) $wilaya->id,
                'number' => (string) ($wilaya->number ?? ''),
                'code' => (string) $wilaya->code,
                'name' => (string) ($wilaya->en ?? $wilaya->fr ?? $wilaya->code),
                'en' => (string) ($wilaya->en ?? ''),
                'fr' => (string) ($wilaya->fr ?? ''),
                'ar' => (string) ($wilaya->ar ?? ''),
            ] : null,
            'delivery_methods' => [
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
                    'description' => 'Pickup from the nearest express delivery agency / desk',
                ],
            ],
            'options' => $formattedOptions,
        ], 200);
    }

    /**
     * 3. List Communes / Stopdesks under a Wilaya (and optionally filtered by delivery company / stopdesk)
     * GET /api/catalogs/delivery/communes
     * or GET /api/catalogs/delivery/communes/{wilayaId}
     *
     * Params:
     * - wilaya_id / wilaya: wilaya ID, code, or number (required or route param)
     * - is_stopdesk / type: boolean/string ('stopdesk' or 'home')
     * - delivery_company_id / company: delivery provider filter
     * - search: keyword to search commune name or postcode
     *
     * @param Request $request
     * @param int|string|null $wilayaId
     * @return JsonResponse
     */
    public function getCommunes(Request $request, int|string|null $wilayaId = null): JsonResponse
    {
        $resolvedWilayaParam = $wilayaId ?? $request->query('wilaya_id') ?? $request->query('wilaya');
        $search = $request->query('search') ?? $request->query('q');
        $isStopdesk = filter_var($request->query('is_stopdesk', false), FILTER_VALIDATE_BOOLEAN)
            || strtolower((string) $request->query('type')) === 'stopdesk'
            || strtolower((string) $request->query('delivery_method')) === 'stopdesk';

        $communesQuery = Commune::query()->with('wilaya')->orderBy('en', 'asc');

        $wilayaModel = null;
        if ($resolvedWilayaParam !== null && $resolvedWilayaParam !== '') {
            if (is_numeric($resolvedWilayaParam)) {
                $wilayaModel = Wilaya::where('id', (int) $resolvedWilayaParam)
                    ->orWhere('number', (string) $resolvedWilayaParam)
                    ->first();
                if ($wilayaModel) {
                    $communesQuery->where('wilaya_id', $wilayaModel->id);
                } else {
                    $communesQuery->where('wilaya_id', (int) $resolvedWilayaParam);
                }
            } else {
                $wilayaModel = Wilaya::where('code', $resolvedWilayaParam)
                    ->orWhere('en', $resolvedWilayaParam)
                    ->orWhere('fr', $resolvedWilayaParam)
                    ->first();
                if ($wilayaModel) {
                    $communesQuery->where('wilaya_id', $wilayaModel->id);
                } else {
                    $communesQuery->whereHas('wilaya', function ($q) use ($resolvedWilayaParam) {
                        $q->where('code', $resolvedWilayaParam)
                            ->orWhere('en', $resolvedWilayaParam)
                            ->orWhere('fr', $resolvedWilayaParam);
                    });
                }
            }
        }

        if (! empty($search)) {
            $s = trim((string) $search);
            $communesQuery->where(function ($q) use ($s) {
                $q->where('en', 'LIKE', "%{$s}%")
                    ->orWhere('fr', 'LIKE', "%{$s}%")
                    ->orWhere('ar', 'LIKE', "%{$s}%")
                    ->orWhere('code', 'LIKE', "%{$s}%")
                    ->orWhere('post_code', 'LIKE', "%{$s}%");
            });
        }

        $communes = $communesQuery->get()->map(function (Commune $c) use ($isStopdesk) {
            $wilaya = $c->wilaya;
            return [
                'id' => (int) $c->id,
                'wilaya_id' => (int) $c->wilaya_id,
                'wilaya_number' => (string) ($wilaya?->number ?? ''),
                'wilaya_name' => (string) ($wilaya?->en ?? $wilaya?->fr ?? $wilaya?->code ?? ''),
                'code' => (string) $c->code,
                'post_code' => (string) ($c->post_code ?? ''),
                'name' => (string) ($c->en ?? $c->code),
                'en' => (string) ($c->en ?? ''),
                'fr' => (string) ($c->fr ?? ''),
                'ar' => (string) ($c->ar ?? ''),
                'has_stopdesk' => true, // Communes in this wilaya support stopdesk agency pickup
                'stopdesk_address' => $isStopdesk
                    ? 'Bureau ' . ($c->en ?? $c->fr ?? $c->code) . ' - Agence Express'
                    : null,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'wilaya' => $wilayaModel ? [
                'id' => (int) $wilayaModel->id,
                'number' => (string) ($wilayaModel->number ?? ''),
                'code' => (string) $wilayaModel->code,
                'name' => (string) ($wilayaModel->en ?? $wilayaModel->fr ?? $wilayaModel->code),
                'en' => (string) ($wilayaModel->en ?? ''),
                'fr' => (string) ($wilayaModel->fr ?? ''),
                'ar' => (string) ($wilayaModel->ar ?? ''),
            ] : null,
            'is_stopdesk' => $isStopdesk,
            'count' => $communes->count(),
            'data' => $communes,
        ], 200);
    }

    /**
     * 4. List all active delivery companies / providers
     * GET /api/catalogs/delivery/companies
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getCompanies(Request $request): JsonResponse
    {
        $companies = DeliveryCompany::query()
            ->where('is_active', true)
            ->orderBy('id', 'asc')
            ->get()
            ->map(function (DeliveryCompany $comp) {
                return [
                    'id' => (int) $comp->id,
                    'code' => (string) $comp->code,
                    'name' => (string) $comp->name,
                    'logo_url' => $comp->logo_url ? url($comp->logo_url) : null,
                    'phone' => (string) ($comp->phone ?? ''),
                    'website' => (string) ($comp->website ?? ''),
                    'is_active' => (bool) $comp->is_active,
                ];
            })->values();

        return response()->json([
            'success' => true,
            'count' => $companies->count(),
            'data' => $companies,
        ], 200);
    }
}
