<?php

namespace App\Http\Controllers\Api\Creators\BecomeCreator;

use App\Http\Controllers\Controller;
use App\Models\UserSupportRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BecomeCreatorController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_if($user === null, 401);

        $creatorRequest = UserSupportRequest::query()
            ->where('user_id', $user->id)
            ->where('target', UserSupportRequest::TARGET_BECOME_CREATOR)
            ->latest('id')
            ->first();

        return response()->json([
            'data' => $creatorRequest === null ? null : $this->formatRequest($creatorRequest),
        ]);
    }

    public function submit(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_if($user === null, 401);

        $validated = $request->validate([
            'phone_number' => ['required', 'string', 'max:255'],
        ]);

        $latestRequest = UserSupportRequest::query()
            ->where('user_id', $user->id)
            ->where('target', UserSupportRequest::TARGET_BECOME_CREATOR)
            ->latest('id')
            ->first();

        if ($latestRequest !== null && in_array((int) $latestRequest->status, [UserSupportRequest::STATUS_PENDING, UserSupportRequest::STATUS_APPROVED], true)) {
            return response()->json([
                'message' => 'You already have a creator request in progress.',
                'data' => $this->formatRequest($latestRequest),
            ], 422);
        }

        $creatorRequest = UserSupportRequest::query()->create([
            'user_id' => $user->id,
            'contact' => $validated['phone_number'],
            'type' => UserSupportRequest::TYPE_PHONE_NUMBER,
            'target' => UserSupportRequest::TARGET_BECOME_CREATOR,
            'status' => UserSupportRequest::STATUS_PENDING,
        ]);

        return response()->json([
            'message' => 'Creator request submitted successfully.',
            'data' => $this->formatRequest($creatorRequest),
        ], 201);
    }

    private function formatRequest(UserSupportRequest $creatorRequest): array
    {
        return [
            'id' => $creatorRequest->id,
            'phone_number' => $creatorRequest->contact,
            'status' => UserSupportRequest::STATUS[$creatorRequest->status] ?? 'pending',
            'note' => $creatorRequest->note,
            'reviewed_at' => $creatorRequest->reviewed_at?->toISOString(),
            'created_at' => $creatorRequest->created_at?->toISOString(),
        ];
    }
}
