<?php

namespace App\Http\Controllers\Api\Sgm\Stores;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UpsertStoreController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $id = $request->input('id');
        $isUpdate = $id !== null;

        $validated = $request->validate([
            'id' => ['nullable', 'integer', 'exists:stores,id'],
            'wilaya_id' => ['required', 'integer', 'exists:wilayas,id'],
            'store_name' => ['required', 'string', 'max:255'],
            'phone_number' => [
                'required',
                'string',
                'max:255',
                $isUpdate ? Rule::unique('stores', 'phone_number')->ignore($id) : 'unique:stores,phone_number',
            ],
            'old_password' => [$isUpdate && $request->filled('new_password') ? 'required' : 'nullable', 'string'],
            'new_password' => [$isUpdate ? 'nullable' : 'required', 'string', 'min:8', 'max:255'],
            'password' => [! $isUpdate ? 'required' : 'nullable', 'string', 'min:8', 'max:255'], // Legacy field support if needed
            'description' => ['nullable', 'string'],
        ]);

        if ($isUpdate) {
            $store = Store::where('id', $id)
                ->where('user_id', $request->user()->id)
                ->firstOrFail();

            if ($request->filled('new_password')) {
                if (! Hash::check($validated['old_password'], $store->password)) {
                    return response()->json([
                        'message' => 'The old password you provided is incorrect.',
                        'errors' => ['old_password' => ['Incorrect old password.']],
                    ], 422);
                }
                $store->password = $validated['new_password'];
            }

            $store->update([
                'wilaya_id' => $validated['wilaya_id'],
                'store_name' => $validated['store_name'],
                'phone_number' => $validated['phone_number'],
                'description' => $validated['description'] ?? $store->description,
            ]);

            $message = 'Store updated successfully.';
        } else {
            $store = Store::create([
                'user_id' => $request->user()->id,
                'wilaya_id' => $validated['wilaya_id'],
                'store_name' => $validated['store_name'],
                'phone_number' => $validated['phone_number'],
                'password' => $validated['new_password'] ?? $validated['password'],
                'description' => $validated['description'] ?? null,
                'status' => 'pending',
            ]);

            $message = 'Store creation request submitted successfully.';
        }

        return response()->json([
            'data' => [
                'id' => $store->id,
                'store_name' => $store->store_name,
                'phone_number' => $store->phone_number,
                'status' => $store->status,
                'wilaya_id' => $store->wilaya_id,
            ],
            'message' => $message,
        ], $isUpdate ? 200 : 201);

    }
}
