<?php

namespace App\Http\Controllers\Api\Sgm\Stores;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginToStoreController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone_number' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $store = Store::where('phone_number', $validated['phone_number'])->first();

        if (! $store || ! Hash::check($validated['password'], $store->password)) {
            return response()->json([
                'message' => 'The credentials you provided are incorrect.',
                'errors' => [
                    'phone_number' => ['Incorrect phone number or password.'],
                ],
            ], 422);
        }

        $store->update([
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'data' => [
                'id' => $store->id,
                'store_name' => $store->store_name,
                'phone_number' => $store->phone_number,
                'status' => $store->status,
                'wilaya_id' => $store->wilaya_id,
            ],
            'message' => 'Successfully logged in to store.',
        ], 200);
    }
}
