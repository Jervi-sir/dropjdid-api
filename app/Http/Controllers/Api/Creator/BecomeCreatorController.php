<?php

namespace App\Http\Controllers\Api\Creator;

use App\Http\Controllers\Controller;
use App\Models\CreatorRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BecomeCreatorController extends Controller
{
    /**
     * Get current creator application status for authenticated user.
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

        $creatorRequest = CreatorRequest::where('user_id', $user->id)
            ->latest('updated_at')
            ->first();

        if (! $creatorRequest) {
            return response()->json([
                'has_applied' => false,
                'phone_number' => $user->phone_number ? (string) $user->phone_number : null,
                'request' => null,
            ], 200);
        }

        return response()->json([
            'has_applied' => true,
            'phone_number' => (string) $creatorRequest->phone_number,
            'request_status' => (string) ($creatorRequest->request_status ?? 'pending'),
            'request' => [
                'id' => (int) $creatorRequest->id,
                'user_id' => (int) $creatorRequest->user_id,
                'phone_number' => (string) $creatorRequest->phone_number,
                'request_status' => (string) ($creatorRequest->request_status ?? 'pending'),
                'note' => $creatorRequest->note,
                'created_at' => $creatorRequest->created_at,
                'updated_at' => $creatorRequest->updated_at,
            ],
        ], 200);
    }

    /**
     * Submit a request for authenticated user to become a creator with their phone number.
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

        // Resolve authenticated user or find/create guest user
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
                    'full_name' => 'Creator Applicant',
                    'email' => 'creator_' . preg_replace('/[^0-9]/', '', $phoneNumber) . '@djapp.local',
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

        // Create or update pending creator request
        $creatorRequest = CreatorRequest::updateOrCreate(
            [
                'user_id' => $user->id,
            ],
            [
                'phone_number' => $phoneNumber,
                'request_status' => 'pending',
                'note' => $note,
            ]
        );

        return response()->json([
            'message' => 'Your request to become a creator has been submitted successfully! Our team will contact you soon.',
            'has_applied' => true,
            'phone_number' => (string) $creatorRequest->phone_number,
            'request_status' => (string) ($creatorRequest->request_status ?? 'pending'),
            'request' => [
                'id' => (int) $creatorRequest->id,
                'user_id' => (int) $user->id,
                'phone_number' => (string) $creatorRequest->phone_number,
                'request_status' => (string) ($creatorRequest->request_status ?? 'pending'),
                'created_at' => $creatorRequest->created_at,
            ],
        ], 200);
    }
}
