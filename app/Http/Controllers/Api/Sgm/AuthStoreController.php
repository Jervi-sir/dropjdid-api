<?php

namespace App\Http\Controllers\Api\Sgm;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Store;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthStoreController extends Controller
{
  /**
   * Create a new store (Request create store).
   *
   * @param Request $request
   * @return JsonResponse
   */
  public function createStore(Request $request): JsonResponse
  {
    $validator = Validator::make($request->all(), [
      'name' => ['required', 'string', 'min:2', 'max:100'],
      'phone_number' => ['required', 'string', 'min:8', 'max:25'],
      'password' => ['required', 'string', 'min:4', 'max:100'],
      'description' => ['nullable', 'string', 'max:1000'],
    ]);

    if ($validator->fails()) {
      return response()->json([
        'message' => $validator->errors()->first(),
        'errors' => $validator->errors(),
      ], 422);
    }

    $user = $request->user('sanctum') ?? $request->user();

    if (! $user) {
      $userId = $request->input('user_id');
      if ($userId) {
        $user = User::find($userId);
      }
    }

    $phoneNumber = trim((string) $request->input('phone_number'));
    $name = trim((string) $request->input('name'));
    $password = (string) $request->input('password');

    if (! $user) {
      $user = User::where('phone_number', $phoneNumber)->first();

      if (! $user) {
        $randomStr = Str::lower(Str::random(6));
        $user = User::create([
          'phone_number' => $phoneNumber,
          'username' => 'store_' . $randomStr,
          'full_name' => $name,
          'email' => 'store_' . preg_replace('/[^0-9]/', '', $phoneNumber) . '_' . $randomStr . '@djapp.local',
          'password' => Hash::make($password),
          'is_active' => true,
        ]);
      }
    }

    // Grant SGM / Store role if not already assigned
    $sgmRole = Role::firstOrCreate(
      ['code' => Role::SGM],
      [
        'en' => 'Store General Manager',
        'fr' => 'Gérant de magasin',
        'ar' => 'مدير متجر',
      ]
    );

    UserRole::firstOrCreate([
      'user_id' => $user->id,
      'role_id' => $sgmRole->id,
    ]);

    // Create the Store record
    $store = Store::create([
      'user_id' => $user->id,
      'name' => $name,
      'phone_number' => $phoneNumber,
      'password' => Hash::make($password),
      'password_plaintext' => $password,
      'description' => $request->input('description'),
      'store_status' => Store::STATUS_PENDING,
      'is_approved' => false,
    ]);

    // Create a store wallet for this new store
    Wallet::firstOrCreate(
      [
        'store_id' => $store->id,
        'level' => Wallet::LEVEL_STORE,
      ],
      [
        'user_id' => $user->id,
        'balance' => 0,
        'currency' => 'DZD',
      ]
    );

    return response()->json([
      'message' => 'Store created successfully!',
      'data' => [
        'id' => (int) $store->id,
        'text1' => (string) $store->name,
        'phone_number' => (string) $store->phone_number,
        'store_status' => Store::formatStatus($store->store_status),
        'is_approved' => (bool) $store->is_approved,
        'created_at' => $store->created_at,
      ],
    ], 201);
  }

  /**
   * Log in to existing store.
   *
   * @param Request $request
   * @return JsonResponse
   */
  public function loginStore(Request $request): JsonResponse
  {
    $validator = Validator::make($request->all(), [
      'phone_number' => ['required', 'string'],
      'password' => ['required', 'string'],
    ]);

    if ($validator->fails()) {
      return response()->json([
        'message' => $validator->errors()->first(),
        'errors' => $validator->errors(),
      ], 422);
    }

    $phoneNumber = trim((string) $request->input('phone_number'));
    $password = (string) $request->input('password');

    $store = Store::where('phone_number', $phoneNumber)->first();

    if (! $store) {
      return response()->json([
        'message' => 'No store found with this phone number.',
      ], 404);
    }

    $passwordMatches = false;
    if ($store->password && Hash::check($password, $store->password)) {
      $passwordMatches = true;
    } elseif ($store->password_plaintext && $store->password_plaintext === $password) {
      $passwordMatches = true;
    }

    if (! $passwordMatches) {
      return response()->json([
        'message' => 'Invalid password credentials for this store.',
      ], 401);
    }

    $user = $request->user('sanctum') ?? $request->user();
    if (! $user && $request->input('user_id')) {
      $user = User::find($request->input('user_id'));
    }

    if ($user && $store->user_id !== $user->id) {
      $store->user_id = $user->id;
      $store->save();
    }

    return response()->json([
      'message' => 'Store login successful!',
      'data' => [
        'id' => (int) $store->id,
        'text1' => (string) $store->name,
        'phone_number' => (string) $store->phone_number,
        'store_status' => Store::formatStatus($store->store_status),
        'is_approved' => (bool) $store->is_approved,
        'created_at' => $store->created_at,
      ],
    ], 200);
  }

  /**
   * Log out / unlink store from user.
   *
   * @param Request $request
   * @param Store|int|null $store
   * @return JsonResponse
   */
  public function logoutStore(Request $request, $store = null): JsonResponse
  {
    $storeId = $request->input('store_id') ?? ($store instanceof Store ? $store->id : $store);

    $targetStore = null;
    if ($storeId) {
      $targetStore = Store::find($storeId);
    }

    if (! $targetStore) {
      $phoneNumber = $request->input('phone_number');
      if ($phoneNumber) {
        $targetStore = Store::where('phone_number', $phoneNumber)->first();
      }
    }

    if (! $targetStore) {
      return response()->json([
        'message' => 'Store not found.',
      ], 404);
    }

    // Unlink store from user without deleting
    $targetStore->user_id = null;
    $targetStore->save();

    return response()->json([
      'message' => 'Logged out and unlinked store successfully.',
    ], 200);
  }
}
