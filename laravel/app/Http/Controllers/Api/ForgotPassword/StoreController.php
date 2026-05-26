<?php

namespace App\Http\Controllers\Api\ForgotPassword;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\User;
use App\Models\UserSupportRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class StoreController extends Controller
{
  public function __invoke(Request $request): JsonResponse
  {
    $validated = $request->validate([
      'username' => ['nullable', 'string'],
      'phone_number' => ['nullable', 'string'],
      'email' => ['nullable', 'email'],
      'target' => ['nullable', 'string'],
      'store_id' => ['nullable', 'integer'],
    ]);

    $target = $validated['target'] ?? 'user';
    $targetType = ($target === 'store') ? UserSupportRequest::TARGET_STORE_FORGOT_PASSWORD : UserSupportRequest::TARGET_FORGOT_PASSWORD;

    $user = null;
    $type = null;
    $contact = null;

    if ($target === 'store') {
      if (! empty($validated['phone_number'])) {
        $contact = $validated['phone_number'];
        $store = Store::where('phone_number', $contact)->first();
      } elseif (! empty($validated['store_id'])) {
        $store = Store::find($validated['store_id']);
        if ($store) {
          $contact = $store->phone_number;
        }
      }

      if (isset($store)) {
        $user = $store->user;
        $type = UserSupportRequest::TYPE_PHONE_NUMBER;
      }
    } else {
      if (! empty($validated['email'])) {
        $contact = $validated['email'];
        $type = UserSupportRequest::TYPE_EMAIL;
        $user = User::where('email', $contact)->first();
      } elseif (! empty($validated['phone_number'])) {
        $contact = $validated['phone_number'];
        $user = User::where('phone_number', $contact)->first();
        if ($user) {
          $type = UserSupportRequest::TYPE_PHONE_NUMBER;
        } else {
          $user = User::where('username', $contact)->first();
          if ($user) {
            $type = UserSupportRequest::TYPE_USERNAME;
          }
        }
      } elseif (! empty($validated['username'])) {
        $contact = $validated['username'];
        $type = UserSupportRequest::TYPE_USERNAME;
        $user = User::where('username', $contact)->first();
      }
    }

    if (! $user) {
      throw ValidationException::withMessages([
        'email' => [$target === 'store' ? 'We could not find a store with the provided information.' : 'We could not find a user with the provided information.'],
      ]);
    }

    $existingRequest = UserSupportRequest::where('user_id', $user->id)
      ->where('target', $targetType)
      ->where('contact', $contact)
      ->where('status', UserSupportRequest::STATUS_PENDING)
      ->first();

    if ($existingRequest) {
      return response()->json([
        'message' => 'You already have a pending password reset request.',
        'data' => $this->formatRequest($existingRequest),
      ], 422);
    }

    $userRequest = UserSupportRequest::create([
      'user_id' => $user->id,
      'contact' => $contact,
      'type' => $type,
      'target' => $targetType,
      'status' => UserSupportRequest::STATUS_PENDING,
    ]);

    return response()->json([
      'message' => 'Your password reset request has been sent to our support team for review.',
      'data' => $this->formatRequest($userRequest),
    ]);
  }

  private function formatRequest(UserSupportRequest $userRequest): array
  {
    return [
      'id' => $userRequest->id,
      'user_id' => $userRequest->user_id,
      'contact' => $userRequest->contact,
      'type' => UserSupportRequest::TYPES[$userRequest->type] ?? 'phone_number',
      'status' => UserSupportRequest::STATUS[$userRequest->status] ?? 'pending',
      'note' => $userRequest->note,
      'reviewed_at' => $userRequest->reviewed_at?->toISOString(),
      'target' => UserSupportRequest::TARGETS[$userRequest->target] ?? 'forgot-password',
      'created_at' => $userRequest->created_at?->toISOString(),
      'updated_at' => $userRequest->updated_at?->toISOString(),
    ];
  }
}
