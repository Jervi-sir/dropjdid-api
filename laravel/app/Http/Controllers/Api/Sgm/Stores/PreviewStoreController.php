<?php

namespace App\Http\Controllers\Api\Sgm\Stores;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\StoreWallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PreviewStoreController extends Controller
{
    public function __invoke(Request $request, $id): JsonResponse
    {
        $store = Store::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $wallet = $store->balanceWallet()->firstOrCreate([
            'store_id' => $store->id,
            'type' => StoreWallet::TYPE_BALANCE,
        ], [
            'balance' => 0,
            'pending_balance' => 0,
            'currency' => 'DZD',
            'status' => StoreWallet::STATUS_NEW,
            'is_identity_verified' => false,
        ]);

        return response()->json([
            'data' => [
                'id' => $store->id,
                'store_name' => $store->store_name,
                'phone_number' => $store->phone_number,
                'is_verified' => $store->is_verified,
                'wallet_is_verified' => (bool) $wallet->is_identity_verified,
                'wallet' => [
                    'is_verified' => (bool) $wallet->is_identity_verified,
                    'status' => StoreWallet::STATUSES[$wallet->status] ?? 'new',
                ],
            ],
        ]);
    }
}
