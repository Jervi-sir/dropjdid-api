<?php

namespace App\Http\Controllers\Api\Sgm\ThisStore;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    /**
     * Show store settings (e.g. store name and info).
     *
     * @param Request $request
     * @param int|null $id
     * @return JsonResponse
     */
    public function show(Request $request, ?int $id = null): JsonResponse
    {
        $storeId = $id ?? $request->query('store_id') ?? $request->query('id');

        if (! $storeId) {
            return response()->json([
                'message' => 'Store ID is required.',
            ], 400);
        }

        $store = Store::find($storeId);

        if (! $store) {
            return response()->json([
                'message' => 'Store not found.',
            ], 404);
        }

        return response()->json([
            'data' => [
                'id' => (int) $store->id,
                'name' => (string) ($store->name ?? ''),
                'phone_number' => (string) ($store->phone_number ?? ''),
                'description' => $store->description,
                'image_url' => $store->image_url,
                'store_status' => Store::formatStatus($store->store_status),
                'is_approved' => (bool) $store->is_approved,
                'created_at' => $store->created_at,
                'updated_at' => $store->updated_at,
            ],
        ], 200);
    }

    /**
     * Update store name and optional details.
     *
     * @param Request $request
     * @param int|null $id
     * @return JsonResponse
     */
    public function update(Request $request, ?int $id = null): JsonResponse
    {
        $storeId = $id ?? $request->input('store_id') ?? $request->input('id') ?? $request->query('store_id');

        if (! $storeId) {
            return response()->json([
                'message' => 'Store ID is required.',
            ], 400);
        }

        $store = Store::find($storeId);

        if (! $store) {
            return response()->json([
                'message' => 'Store not found.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $store->update([
            'name' => trim((string) $request->input('name')),
            'description' => $request->has('description') ? $request->input('description') : $store->description,
        ]);

        return response()->json([
            'message' => 'Store updated successfully!',
            'data' => [
                'id' => (int) $store->id,
                'name' => (string) $store->name,
                'phone_number' => (string) ($store->phone_number ?? ''),
                'description' => $store->description,
                'image_url' => $store->image_url,
                'store_status' => Store::formatStatus($store->store_status),
                'is_approved' => (bool) $store->is_approved,
                'updated_at' => $store->updated_at,
            ],
        ], 200);
    }
}
