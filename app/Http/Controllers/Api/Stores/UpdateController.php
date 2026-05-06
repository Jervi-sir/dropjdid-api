<?php

namespace App\Http\Controllers\Api\Stores;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UpdateController extends Controller
{
    public function __invoke(Request $request, Store $store): JsonResponse
    {
        $user = $request->user();

        abort_if($user === null, 401);
        abort_unless($store->user_id === $user->id, 404);

        $validated = $request->validate([
            'wilaya_id' => ['required', 'integer', 'exists:wilayas,id'],
            'store_name' => ['required', 'string', 'max:255'],
            'phone_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('stores', 'phone_number')->ignore($store->id),
            ],
            'password' => ['nullable', 'string', 'min:8', 'max:255'],
        ]);

        $store->fill([
            'wilaya_id' => $validated['wilaya_id'],
            'store_name' => $validated['store_name'],
            'phone_number' => $validated['phone_number'],
        ]);

        if (! empty($validated['password'])) {
            $store->password = $validated['password'];
        }

        $store->save();
        $store->load('wilaya');

        return response()->json([
            'data' => [
                'id' => $store->id,
                'store_name' => $store->store_name,
                'phone_number' => $store->phone_number,
                'status' => $store->status,
                'wilaya' => [
                    'id' => $store->wilaya?->id,
                    'code' => $store->wilaya?->code,
                    'number' => $store->wilaya?->number,
                    'name' => $store->wilaya?->en,
                ],
            ],
            'message' => 'Store updated successfully.',
        ]);
    }
}
