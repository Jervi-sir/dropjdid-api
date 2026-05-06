<?php

namespace App\Http\Controllers\Api\Stores;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CreateNewStoreController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_if($user === null, 401);

        $validated = $request->validate([
            'wilaya_id' => ['required', 'integer', 'exists:wilayas,id'],
            'store_name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:255', 'unique:stores,phone_number'],
            'password' => ['required', 'string', 'min:8', 'max:255'],
        ]);

        $store = Store::query()->create([
            'user_id' => $user->id,
            'wilaya_id' => $validated['wilaya_id'],
            'store_name' => $validated['store_name'],
            'phone_number' => $validated['phone_number'],
            'password' => $validated['password'],
            'status' => 'pending',
        ]);

        $store->load('user');

        return response()->json([
            'data' => [
                'id' => $store->id,
                'store_name' => $store->store_name,
                'phone_number' => $store->phone_number,
                'status' => $store->status,
                'wilaya_id' => $store->wilaya_id,
            ],
            'message' => 'Store created successfully.',
        ], 201);
    }
}
