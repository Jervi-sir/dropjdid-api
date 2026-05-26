<?php

namespace App\Http\Controllers\Api\Orders;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TermsConditionsController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        return response()->json([
            'data' => [
                'terms' => [
                    [
                        'title' => '1. Acceptance of Terms',
                        'content' => 'By proceeding with this order, you agree to these Terms and Conditions. These terms govern the relationship between you and the platform regarding the order and purchase of products.'
                    ],
                    [
                        'title' => '2. Ordering and Purchases',
                        'content' => 'All orders placed are subject to product availability. We reserve the right to decline or cancel any order for reasons including but not limited to product availability, errors in the description or price of the product, or fraud detection.'
                    ],
                    [
                        'title' => '3. Shipping and Delivery',
                        'content' => 'We offer both Home Delivery and Desk Delivery options depending on your location and wilaya. Delivery times provided are estimates and are not guaranteed.'
                    ],
                    [
                        'title' => '4. Cash on Delivery (COD) Policy',
                        'content' => 'If cash on delivery is chosen as the payment method, you are obligated to pay the courier the full order amount upon delivery. Unjustified refusal to accept deliveries may result in temporary or permanent restriction of your account.'
                    ],
                    [
                        'title' => '5. Return and Refund Policy',
                        'content' => 'In case of defective products or size mismatch from what was ordered, please contact support within 48 hours of receiving your delivery to initiate an exchange or refund request.'
                    ],
                    [
                        'title' => '6. Privacy and Data Security',
                        'content' => 'Your personal details, including your full name, phone number, and address, are used solely for fulfilling your orders and will not be shared with third parties except delivery services.'
                    ]
                ]
            ]
        ]);
    }
}
