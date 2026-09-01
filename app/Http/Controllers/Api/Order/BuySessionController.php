<?php

namespace App\Http\Controllers\Api\Order;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\Size;
use App\Models\StoreToDeliveryCost;
use App\Models\User;
use App\Models\Wilaya;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BuySessionController extends Controller
{
    /**
     * Resolve authenticated or requested user.
     */
    protected function resolveUser(Request $request): ?User
    {
        $user = $request->user('sanctum') ?? $request->user();
        if (! $user) {
            $userId = $request->input('user_id') ?? $request->header('X-User-Id') ?? $request->query('user_id');
            if ($userId) {
                $user = User::find($userId);
            }
        }
        return $user;
    }

    /**
     * Return the available categories, sizes, and variant details for the selected product during a buy session.
     *
     * @param Request $request
     * @param int|null $id
     * @return JsonResponse
     */
    public function show(Request $request, ?int $id = null): JsonResponse
    {
        $productId = $id ?? $request->query('product_id') ?? $request->input('product_id');

        if (! $productId) {
            return response()->json([
                'message' => 'Product ID is required.',
            ], 400);
        }

        $product = Product::query()
            ->where('id', $productId)
            ->with(['variants.size.category', 'mainImage', 'images', 'category', 'store'])
            ->first();

        if (! $product) {
            return response()->json([
                'message' => 'Product not found.',
            ], 404);
        }

        // Map product variants with their size & category info
        $variantItems = $product->variants->map(function ($variant) {
            $size = $variant->size;
            if (! $size) {
                return null;
            }

            $category = $size->category;
            $catCode = (string) ($category?->code ?? $size->type ?? 'universal');
            $catNameEn = (string) ($category?->en ?? ucfirst($catCode));
            $catNameFr = (string) ($category?->fr ?? ucfirst($catCode));
            $catNameAr = (string) ($category?->ar ?? $catCode);

            return [
                'variant_id' => (int) $variant->id,
                'size_id' => (int) $size->id,
                'size_code' => (string) ($size->code ?? ''),
                'category_id' => $category ? (int) $category->id : null,
                'category_code' => $catCode,
                'category_en' => $catNameEn,
                'category_fr' => $catNameFr,
                'category_ar' => $catNameAr,
                'en' => (string) ($size->en ?? $size->code ?? ''),
                'fr' => (string) ($size->fr ?? $size->code ?? ''),
                'ar' => (string) ($size->ar ?? $size->code ?? ''),
                'stock_quantity' => (int) ($variant->quantity ?? 0),
                'in_stock' => (int) ($variant->quantity ?? 0) > 0,
            ];
        })->filter()->values();

        // Group into available categories with their sizes
        $categoriesGrouped = $variantItems->groupBy('category_code')->map(function ($items, $catCode) {
            $first = $items->first();
            $sizes = $items->map(function ($item) {
                return [
                    'id' => $item['size_id'],
                    'variant_id' => $item['variant_id'],
                    'code' => $item['size_code'],
                    'en' => $item['en'],
                    'fr' => $item['fr'],
                    'ar' => $item['ar'],
                    'stock_quantity' => $item['stock_quantity'],
                    'in_stock' => $item['in_stock'],
                ];
            })->values();

            return [
                'id' => $first['category_id'] ?? null,
                'code' => (string) $catCode,
                'en' => $first['category_en'],
                'fr' => $first['category_fr'],
                'ar' => $first['category_ar'],
                'sizes' => $sizes,
            ];
        })->values();

        $imageUrl = $product->mainImage?->image_url
            ?? $product->images->first()?->image_url
            ?? '';

        if ($imageUrl && ! str_starts_with($imageUrl, 'http://') && ! str_starts_with($imageUrl, 'https://')) {
            $imageUrl = url($imageUrl);
        }

        $priceShown = $product->price_shown ?? $product->price_original;
        $priceOriginal = $product->price_original;

        return response()->json([
            'product' => [
                'id' => (int) $product->id,
                'store_id' => (int) ($product->store_id ?? 1),
                'name' => (string) ($product->name ?? ''),
                'image_url' => (string) $imageUrl,
                'price_shown' => $priceShown !== null ? (float) $priceShown : null,
                'price_original' => $priceOriginal !== null ? (float) $priceOriginal : null,
                'price_formatted' => $priceShown !== null ? number_format((float) $priceShown, 0, '.', ' ') . ' DZD' : '',
            ],
            'categories' => $categoriesGrouped,
            'sizes' => $variantItems,
            'data' => [
                'categories' => $categoriesGrouped,
                'sizes' => $variantItems,
            ],
        ], 200);
    }

    /**
     * Create an order from the buy screen checkout form.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|integer',
            'full_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:50',
            'wilaya_id' => 'nullable|integer',
            'wilaya' => 'nullable|string|max:255',
            'baladiya' => 'nullable|string|max:255',
            'home_address' => 'required|string',
            'delivery_method' => 'nullable|string|in:home,desk,domicile,stopdesk',
            'coupon_code' => 'nullable|string|max:50',
            'sizes' => 'nullable|array', // e.g. [{"size_id": 8, "quantity": 1}] or {"shoes": {"38": 1}}
            'drop_id' => 'nullable|integer',
            'notes' => 'nullable|string',
        ]);

        $product = Product::query()
            ->with(['store', 'variants.size'])
            ->find($validated['product_id']);

        if (! $product) {
            return response()->json([
                'message' => 'Product not found.',
            ], 404);
        }

        $user = $this->resolveUser($request);
        $storeId = $product->store_id ?? 1;

        // Resolve Wilaya
        $wilayaModel = null;
        if (! empty($validated['wilaya_id'])) {
            $wilayaModel = Wilaya::find($validated['wilaya_id']);
        } elseif (! empty($validated['wilaya'])) {
            $wilayaModel = Wilaya::where('code', $validated['wilaya'])
                ->orWhere('number', $validated['wilaya'])
                ->orWhere('en', $validated['wilaya'])
                ->orWhere('fr', $validated['wilaya'])
                ->first();
        }

        $wilayaName = $wilayaModel ? ($wilayaModel->fr ?? $wilayaModel->en ?? $wilayaModel->name ?? $wilayaModel->code) : ($validated['wilaya'] ?? 'Alger');
        $baladiyaName = $validated['baladiya'] ?? 'Alger Centre';

        // Delivery method normalization
        $rawDeliveryMethod = strtolower($validated['delivery_method'] ?? 'home');
        $deliveryMethod = in_array($rawDeliveryMethod, ['desk', 'stopdesk'], true) ? 'desk' : 'home';

        // Calculate delivery fees
        $deliveryCostRecord = null;
        if ($wilayaModel) {
            $deliveryCostRecord = StoreToDeliveryCost::where('store_id', $storeId)
                ->where('wilaya_id', $wilayaModel->id)
                ->first();
        }

        if ($deliveryCostRecord) {
            $deliveryFees = $deliveryMethod === 'desk'
                ? (float) $deliveryCostRecord->cost_stopdesk
                : (float) $deliveryCostRecord->cost_domicile;
        } else {
            // Default zone fallback
            $isCapitalNearby = in_array((string) ($wilayaModel?->number ?? '16'), ['16', '09', '35', '42'], true);
            $deliveryFees = $deliveryMethod === 'desk'
                ? ($isCapitalNearby ? 250.00 : 350.00)
                : ($isCapitalNearby ? 400.00 : 600.00);
        }

        // Parse items & quantities from sizes payload
        $itemsToCreate = [];
        $subtotal = 0;
        $unitPrice = (float) ($product->price_shown ?? $product->price_original ?? 0);

        $sizesInput = $validated['sizes'] ?? [];

        // Handle dictionary structure: {"shoes": {"38": 2, "39": 1}} or flat list: [{"size_id": 8, "quantity": 2}]
        if (is_array($sizesInput) && ! empty($sizesInput)) {
            $isNumericIndexed = array_keys($sizesInput) === range(0, count($sizesInput) - 1);

            if ($isNumericIndexed) {
                foreach ($sizesInput as $entry) {
                    $sizeId = $entry['size_id'] ?? $entry['id'] ?? null;
                    $sizeCode = $entry['size_code'] ?? $entry['code'] ?? null;
                    $qty = max(1, (int) ($entry['quantity'] ?? 1));

                    $sizeModel = null;
                    if ($sizeId) {
                        $sizeModel = Size::find($sizeId);
                    } elseif ($sizeCode) {
                        $sizeModel = Size::where('code', $sizeCode)->first();
                    }

                    if (! $sizeModel) {
                        $sizeModel = Size::first();
                    }

                    $itemTotal = $unitPrice * $qty;
                    $subtotal += $itemTotal;

                    $itemsToCreate[] = [
                        'product_id' => $product->id,
                        'drop_id' => $validated['drop_id'] ?? null,
                        'size_id' => $sizeModel ? $sizeModel->id : 1,
                        'product_name' => (string) ($product->name ?? 'Product #' . $product->id),
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                        'total_price' => $itemTotal,
                    ];
                }
            } else {
                // Nested category -> sizeCode -> quantity
                foreach ($sizesInput as $catCode => $sizesMap) {
                    if (is_array($sizesMap)) {
                        foreach ($sizesMap as $sizeCode => $qty) {
                            $qtyInt = (int) $qty;
                            if ($qtyInt <= 0) continue;

                            $sizeModel = Size::where('code', $sizeCode)->first() ?? Size::first();

                            $itemTotal = $unitPrice * $qtyInt;
                            $subtotal += $itemTotal;

                            $itemsToCreate[] = [
                                'product_id' => $product->id,
                                'drop_id' => $validated['drop_id'] ?? null,
                                'size_id' => $sizeModel ? $sizeModel->id : 1,
                                'product_name' => (string) ($product->name ?? 'Product #' . $product->id),
                                'quantity' => $qtyInt,
                                'unit_price' => $unitPrice,
                                'total_price' => $itemTotal,
                            ];
                        }
                    }
                }
            }
        }

        // Default 1 item if no sizes provided
        if (empty($itemsToCreate)) {
            $defaultSize = Size::first();
            $itemTotal = $unitPrice * 1;
            $subtotal = $itemTotal;

            $itemsToCreate[] = [
                'product_id' => $product->id,
                'drop_id' => $validated['drop_id'] ?? null,
                'size_id' => $defaultSize ? $defaultSize->id : 1,
                'product_name' => (string) ($product->name ?? 'Product #' . $product->id),
                'quantity' => 1,
                'unit_price' => $unitPrice,
                'total_price' => $itemTotal,
            ];
        }

        // Apply coupon discount if provided
        $discountAmount = 0;
        $couponCode = strtoupper(trim((string) ($validated['coupon_code'] ?? '')));
        if (! empty($couponCode)) {
            $couponController = new CouponController();
            $couponCheckRequest = Request::create('/api/orders/check-coupon', 'GET', [
                'code' => $couponCode,
                'subtotal' => $subtotal,
                'product_id' => $product->id,
            ]);
            $couponCheckResponse = $couponController->check($couponCheckRequest);
            $couponData = $couponCheckResponse->getData(true);
            if (! empty($couponData['is_valid'])) {
                $discountAmount = (float) ($couponData['reduction_amount'] ?? 0);
            }
        }

        $transactionFees = 40.00;
        $totalAmount = max(0, ($subtotal - $discountAmount)) + $deliveryFees + $transactionFees;

        $orderNumber = 'ORD-' . strtoupper(Str::random(4)) . '-' . rand(1000, 9999);

        // Transactional creation
        $order = DB::transaction(function () use (
            $product,
            $user,
            $storeId,
            $wilayaModel,
            $orderNumber,
            $validated,
            $wilayaName,
            $baladiyaName,
            $deliveryMethod,
            $deliveryFees,
            $subtotal,
            $totalAmount,
            $itemsToCreate
        ) {
            $order = Order::create([
                'order_number' => $orderNumber,
                'store_id' => $storeId,
                'user_id' => $user ? $user->id : null,
                'wilaya_id' => $wilayaModel ? $wilayaModel->id : null,
                'full_name' => $validated['full_name'],
                'phone_number' => $validated['phone_number'],
                'wilaya' => $wilayaName,
                'baladiya' => $baladiyaName,
                'home_address' => $validated['home_address'],
                'delivery_method' => $deliveryMethod,
                'delivery_fees' => $deliveryFees,
                'subtotal' => $subtotal,
                'total' => $totalAmount,
                'order_status_code' => OrderStatus::PENDING,
                'has_claim_issue' => false,
            ]);

            foreach ($itemsToCreate as $itemData) {
                $itemData['order_id'] = $order->id;
                OrderItem::create($itemData);
            }

            return $order;
        });

        $order->load(['items.product', 'items.size', 'wilayaModel', 'store', 'orderStatus']);

        return response()->json([
            'success' => true,
            'message' => 'Order placed successfully!',
            'order' => [
                'id' => (int) $order->id,
                'order_number' => (string) $order->order_number,
                'full_name' => (string) $order->full_name,
                'phone_number' => (string) $order->phone_number,
                'wilaya' => (string) $order->wilaya,
                'baladiya' => (string) $order->baladiya,
                'home_address' => (string) $order->home_address,
                'delivery_method' => (string) $order->delivery_method,
                'delivery_fees' => (float) $order->delivery_fees,
                'subtotal' => (float) $order->subtotal,
                'discount' => (float) $discountAmount,
                'total' => (float) $order->total,
                'order_status_code' => (string) ($order->order_status_code ?? 'pending'),
                'status' => (string) ($order->order_status_code ?? 'pending'),
                'created_at' => $order->created_at ? $order->created_at->toISOString() : null,
                'items_count' => $order->items->count(),
            ],
            'data' => $order,
        ], 201);
    }
}
