<?php

namespace App\Http\Controllers\Api\Order;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    /**
     * Validate a coupon code and calculate the discount reduction amount.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function check(Request $request): JsonResponse
    {
        $code = trim((string) ($request->query('code') ?? $request->input('code') ?? ''));
        $subtotal = (float) ($request->query('subtotal') ?? $request->input('subtotal') ?? 0);
        $productId = $request->query('product_id') ?? $request->input('product_id');

        if (empty($code)) {
            return response()->json([
                'is_valid' => false,
                'message' => 'Coupon code is required.',
                'reduction_amount' => 0,
                'discount_percentage' => 0,
                'code' => '',
            ], 400);
        }

        $normalized = strtoupper($code);

        // Predefined list of active promo coupons with rules
        $coupons = [
            'DROP15' => [
                'type' => 'percentage',
                'value' => 15, // 15% off
                'min_subtotal' => 5000,
                'max_discount' => 3000,
                'description' => '15% off on your purchase',
            ],
            'PROMO1500' => [
                'type' => 'fixed',
                'value' => 1500, // 1500 DZD off
                'min_subtotal' => 10000,
                'max_discount' => 1500,
                'description' => '1 500 DZD discount on orders above 10 000 DZD',
            ],
            'WELCOME10' => [
                'type' => 'percentage',
                'value' => 10, // 10% off
                'min_subtotal' => 0,
                'max_discount' => 2000,
                'description' => '10% welcome discount',
            ],
            'VIP500' => [
                'type' => 'fixed',
                'value' => 500, // 500 DZD off
                'min_subtotal' => 3000,
                'max_discount' => 500,
                'description' => '500 DZD discount',
            ],
            'SGM2025' => [
                'type' => 'percentage',
                'value' => 20, // 20% off
                'min_subtotal' => 8000,
                'max_discount' => 4000,
                'description' => '20% special SGM partner discount',
            ],
        ];

        // Check if coupon code exists
        if (! isset($coupons[$normalized])) {
            return response()->json([
                'is_valid' => false,
                'message' => 'Invalid or expired coupon code.',
                'reduction_amount' => 0,
                'discount_percentage' => 0,
                'code' => $code,
            ], 200);
        }

        $coupon = $coupons[$normalized];

        // Validate minimum subtotal requirement if subtotal is supplied
        if ($subtotal > 0 && $coupon['min_subtotal'] > 0 && $subtotal < $coupon['min_subtotal']) {
            $formattedMin = number_format($coupon['min_subtotal'], 0, '.', ' ');
            return response()->json([
                'is_valid' => false,
                'message' => "Minimum order amount of {$formattedMin} DZD required for this coupon.",
                'reduction_amount' => 0,
                'discount_percentage' => 0,
                'code' => $normalized,
            ], 200);
        }

        // Calculate discount reduction amount
        $reduction = 0;
        $discountPercentage = 0;

        if ($coupon['type'] === 'percentage') {
            $discountPercentage = (int) $coupon['value'];
            if ($subtotal > 0) {
                $calculated = round(($subtotal * $coupon['value']) / 100);
                $reduction = min($calculated, $coupon['max_discount']);
            } else {
                $reduction = min(1500, $coupon['max_discount']); // Fallback default estimate
            }
        } elseif ($coupon['type'] === 'fixed') {
            $reduction = (float) $coupon['value'];
            if ($subtotal > 0 && $reduction > $subtotal) {
                $reduction = $subtotal;
            }
        }

        return response()->json([
            'is_valid' => true,
            'code' => $normalized,
            'type' => $coupon['type'],
            'value' => $coupon['value'],
            'reduction_amount' => (float) $reduction,
            'discount_percentage' => $discountPercentage,
            'formatted_reduction' => '-' . number_format($reduction, 0, '.', ' ') . ' DZD',
            'description' => $coupon['description'],
            'message' => 'Coupon applied successfully!',
        ], 200);
    }
}
