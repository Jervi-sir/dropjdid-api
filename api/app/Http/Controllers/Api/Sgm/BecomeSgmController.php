<?php

namespace App\Http\Controllers\Api\Sgm;

use App\Http\Controllers\Controller;
use App\Models\SupportRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BecomeSgmController extends Controller
{
    /**
     * Get current SGM / store owner application status for authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
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
                'has_applied' => false,
                'request' => null,
            ], 200);
        }

        $sgmRequest = SupportRequest::where('user_id', $user->id)
            ->where('target', 'become-sgm')
            ->latest('updated_at')
            ->first();

        if (! $sgmRequest) {
            return response()->json([
                'has_applied' => false,
                'phone_number' => $user->phone_number ? (string) $user->phone_number : null,
                'request' => null,
            ], 200);
        }

        return response()->json([
            'has_applied' => true,
            'phone_number' => (string) $sgmRequest->contact,
            'request_status' => (string) ($sgmRequest->status ?? 'pending'),
            'request' => [
                'id' => (int) $sgmRequest->id,
                'user_id' => (int) $sgmRequest->user_id,
                'contact' => (string) $sgmRequest->contact,
                'phone_number' => (string) $sgmRequest->contact,
                'type' => (string) ($sgmRequest->type ?? 'phone_number'),
                'target' => (string) ($sgmRequest->target ?? 'become-sgm'),
                'status' => (string) ($sgmRequest->status ?? 'pending'),
                'request_status' => (string) ($sgmRequest->status ?? 'pending'),
                'note' => $sgmRequest->note,
                'created_at' => $sgmRequest->created_at,
                'updated_at' => $sgmRequest->updated_at,
            ],
        ], 200);
    }

    /**
     * Submit a request for authenticated user to become an SGM / open a store.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function submit(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => ['required', 'string', 'min:8', 'max:20'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $phoneNumber = trim($request->input('phone_number'));
        $note = $request->input('note');

        // Resolve authenticated user or find/create user
        $user = $request->user('sanctum') ?? $request->user();

        if (! $user) {
            $userId = $request->input('user_id');
            if ($userId) {
                $user = User::find($userId);
            }
        }

        if (! $user) {
            $user = User::where('phone_number', $phoneNumber)->first();

            if (! $user) {
                $randomStr = Str::lower(Str::random(6));
                $user = User::create([
                    'phone_number' => $phoneNumber,
                    'username' => 'user_' . $randomStr,
                    'full_name' => 'Store Applicant',
                    'email' => 'sgm_' . preg_replace('/[^0-9]/', '', $phoneNumber) . '@djapp.local',
                    'password' => bcrypt(Str::random(16)),
                    'is_active' => true,
                ]);
            }
        }

        // Update phone number on user if missing
        if ($user && ! $user->phone_number) {
            $user->phone_number = $phoneNumber;
            $user->save();
        }

        // Create or update pending SGM support request
        $sgmRequest = SupportRequest::updateOrCreate(
            [
                'user_id' => $user->id,
                'target' => 'become-sgm',
            ],
            [
                'contact' => $phoneNumber,
                'type' => 'phone_number',
                'status' => 'pending',
                'note' => $note,
            ]
        );

        return response()->json([
            'message' => 'Your request to open a store / become an SGM has been submitted successfully! Our team will contact you soon.',
            'has_applied' => true,
            'phone_number' => (string) $sgmRequest->contact,
            'request_status' => (string) ($sgmRequest->status ?? 'pending'),
            'request' => [
                'id' => (int) $sgmRequest->id,
                'user_id' => (int) $user->id,
                'contact' => (string) $sgmRequest->contact,
                'phone_number' => (string) $sgmRequest->contact,
                'status' => (string) ($sgmRequest->status ?? 'pending'),
                'request_status' => (string) ($sgmRequest->status ?? 'pending'),
                'target' => 'become-sgm',
                'created_at' => $sgmRequest->created_at,
            ],
        ], 200);
    }
}
