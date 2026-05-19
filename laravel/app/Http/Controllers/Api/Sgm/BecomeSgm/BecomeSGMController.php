<?php

namespace App\Http\Controllers\Api\Sgm\BecomeSgm;

use App\Http\Controllers\Controller;
use App\Models\UserSupportRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BecomeSGMController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $userRequest = UserSupportRequest::where('user_id', $request->user()->id)
            ->where('target', UserSupportRequest::TARGET_BECOME_SGM)
            ->latest()
            ->first();

        return response()->json([
            'data' => $userRequest ? $this->formatRequest($userRequest) : null,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'phone_number' => ['required', 'string'],
        ]);

        $existingRequest = UserSupportRequest::where('user_id', $request->user()->id)
            ->where('target', UserSupportRequest::TARGET_BECOME_SGM)
            ->where('status', UserSupportRequest::STATUS_PENDING)
            ->first();

        if ($existingRequest) {
            return response()->json([
                'message' => 'You already have a pending request.',
                'data' => $this->formatRequest($existingRequest),
            ], 422);
        }

        $userRequest = UserSupportRequest::create([
            'user_id' => $request->user()->id,
            'contact' => $request->phone_number,
            'type' => UserSupportRequest::TYPE_PHONE_NUMBER,
            'target' => UserSupportRequest::TARGET_BECOME_SGM,
            'status' => UserSupportRequest::STATUS_PENDING,
        ]);

        return response()->json([
            'message' => 'Request submitted successfully.',
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
            'target' => UserSupportRequest::TARGETS[$userRequest->target] ?? 'become-sgm',
            'created_at' => $userRequest->created_at?->toISOString(),
            'updated_at' => $userRequest->updated_at?->toISOString(),
        ];
    }
}
