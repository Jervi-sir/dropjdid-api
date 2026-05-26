<?php

namespace App\Http\Controllers\Api\ForgotPassword;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\User;
use App\Models\UserSupportRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ShowController extends Controller
{
  public function __invoke(Request $request): JsonResponse
  {
    $user = $request->user();
    $target = $request->query('target');
    $storeId = $request->query('store_id');

    if (! $user) {
      $contact = $request->query('email') ?? $request->query('phone_number') ?? $request->query('username') ?? $request->query('contact');
      if ($contact) {
        if ($target === 'store') {
          $store = Store::where('phone_number', $contact)->first();
          if ($store) {
            $user = $store->user;
          }
        } else {
          $user = User::where('email', $contact)
            ->orWhere('phone_number', $contact)
            ->orWhere('username', $contact)
            ->first();
        }
      }
    }

    $targetType = ($target === 'store') ? UserSupportRequest::TARGET_STORE_FORGOT_PASSWORD : UserSupportRequest::TARGET_FORGOT_PASSWORD;

    $userRequest = null;
    if ($user) {
      $query = UserSupportRequest::where('user_id', $user->id)
        ->where('target', $targetType);

      if ($target === 'store' && $storeId) {
        $store = Store::find($storeId);
        if ($store) {
          $query->where('contact', $store->phone_number);
        }
      } elseif ($target === 'store' && isset($contact)) {
        $query->where('contact', $contact);
      }

      $userRequest = $query->latest()->first();
    }

    return response()->json([
      'data' => $userRequest ? $this->formatRequest($userRequest) : null,
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
