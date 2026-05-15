<?php

namespace App\Http\Controllers\Api\Orders;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetAvailableDeliveriesController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|integer',
            'wilaya_id' => 'required|integer',
        ]);

        // Hardcoded for now as requested
        return response()->json([
            'data' => [
                [
                    'id' => 'home',
                    'name' => 'Home delivery',
                    'price' => 600,
                ],
                [
                    'id' => 'desk',
                    'name' => 'Desk delivery',
                    'price' => 350,
                ],
            ],
        ]);
    }
}
