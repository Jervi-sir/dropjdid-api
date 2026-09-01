<?php

namespace App\Http\Controllers\Api\Settings;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\SupportRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SupportController extends Controller
{
    /**
     * Submit a new support request or password reset request.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'contact' => ['nullable', 'string', 'max:100'],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'note' => ['nullable', 'string', 'max:5000'],
            'message' => ['nullable', 'string', 'max:5000'],
            'type' => ['nullable', 'string', 'in:phone_number,username,email'],
            'target' => ['nullable', 'string', 'max:50'],
            'user_id' => ['nullable', 'integer'],
            'store_id' => ['nullable', 'integer'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $contact = trim((string) ($request->input('contact') ?: $request->input('phone_number')));
        $note = trim((string) ($request->input('note') ?: $request->input('message')));
        $type = $request->input('type', 'phone_number');
        $storeId = $request->input('store_id');
        $target = $request->input('target') ?: ($storeId ? 'store-forgot-password' : 'contact-support');

        $store = null;
        if ($storeId) {
            $store = Store::find($storeId);
            if ($store && empty($contact) && ! empty($store->phone_number)) {
                $contact = (string) $store->phone_number;
            }
        }

        if (empty($contact)) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide a valid contact (phone number or email).',
                'errors' => [
                    'contact' => ['Contact information is required.'],
                ],
            ], 422);
        }

        // Resolve user: from auth sanctum, request user_id, store owner, or phone lookup
        $user = $request->user('sanctum') ?? $request->user();

        if (! $user) {
            $userId = $request->input('user_id');
            if ($userId) {
                $user = User::find($userId);
            }
        }

        if (! $user && $store && $store->user_id) {
            $user = User::find($store->user_id);
        }

        if (! $user) {
            // Try finding user by phone_number or email
            $user = User::where('phone_number', $contact)
                ->orWhere('email', $contact)
                ->first();

            if (! $user) {
                $cleanDigits = preg_replace('/[^0-9]/', '', $contact);
                $randomSuffix = Str::lower(Str::random(6));
                $user = User::create([
                    'phone_number' => $type === 'phone_number' ? $contact : null,
                    'username' => 'guest_' . ($cleanDigits ?: $randomSuffix),
                    'full_name' => 'Support Guest',
                    'email' => 'support_' . ($cleanDigits ?: $randomSuffix) . '@djapp.local',
                    'password' => bcrypt(Str::random(16)),
                    'is_active' => true,
                ]);
            }
        }

        if ($user && ! $user->phone_number && $type === 'phone_number') {
            $user->phone_number = $contact;
            $user->save();
        }

        $supportRequest = SupportRequest::create([
            'user_id' => $user->id,
            'store_id' => $storeId ? (int) $storeId : null,
            'contact' => $contact,
            'type' => $type,
            'status' => 'pending',
            'note' => $note ?: null,
            'target' => $target,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Your request has been successfully submitted. Our support team will contact you soon.',
            'request' => [
                'id' => (int) $supportRequest->id,
                'user_id' => (int) $supportRequest->user_id,
                'store_id' => $supportRequest->store_id ? (int) $supportRequest->store_id : null,
                'contact' => (string) $supportRequest->contact,
                'phone_number' => (string) $supportRequest->contact,
                'type' => (string) ($supportRequest->type ?? 'phone_number'),
                'target' => (string) ($supportRequest->target ?? 'contact-support'),
                'status' => (string) ($supportRequest->status ?? 'pending'),
                'note' => $supportRequest->note,
                'created_at' => $supportRequest->created_at,
                'updated_at' => $supportRequest->updated_at,
            ],
        ], 201);
    }

    /**
     * Get the latest support request status for authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user('sanctum') ?? $request->user();

        if (! $user) {
            $userId = $request->query('user_id');
            if ($userId) {
                $user = User::find($userId);
            }
        }

        if (! $user) {
            return response()->json([
                'has_active_request' => false,
                'request' => null,
            ], 200);
        }

        $target = $request->query('target', 'contact-support');
        $storeId = $request->query('store_id');

        $query = SupportRequest::where('user_id', $user->id)
            ->where('target', $target);

        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        $latestRequest = $query->latest('id')->first();

        if (! $latestRequest) {
            return response()->json([
                'has_active_request' => false,
                'phone_number' => $user->phone_number ? (string) $user->phone_number : null,
                'request' => null,
            ], 200);
        }

        return response()->json([
            'has_active_request' => true,
            'phone_number' => (string) $latestRequest->contact,
            'request_status' => (string) ($latestRequest->status ?? 'pending'),
            'request' => [
                'id' => (int) $latestRequest->id,
                'user_id' => (int) $latestRequest->user_id,
                'store_id' => $latestRequest->store_id ? (int) $latestRequest->store_id : null,
                'contact' => (string) $latestRequest->contact,
                'phone_number' => (string) $latestRequest->contact,
                'type' => (string) ($latestRequest->type ?? 'phone_number'),
                'target' => (string) ($latestRequest->target ?? 'contact-support'),
                'status' => (string) ($latestRequest->status ?? 'pending'),
                'note' => $latestRequest->note,
                'created_at' => $latestRequest->created_at,
                'updated_at' => $latestRequest->updated_at,
            ],
        ], 200);
    }

    /**
     * List support requests of the current user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user('sanctum') ?? $request->user();

        if (! $user) {
            return response()->json([
                'data' => [],
            ], 200);
        }

        $requests = SupportRequest::where('user_id', $user->id)
            ->latest('id')
            ->paginate(15);

        return response()->json($requests, 200);
    }
}
