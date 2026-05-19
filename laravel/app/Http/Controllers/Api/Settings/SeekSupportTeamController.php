<?php

namespace App\Http\Controllers\Api\Settings;

use App\Http\Controllers\Controller;
use App\Models\UserSupportRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SeekSupportTeamController extends Controller
{
    public function listHistory(Request $request): JsonResponse
    {
        $requests = UserSupportRequest::where('user_id', $request->user()->id)
            ->where('target', UserSupportRequest::TARGET_CONTACT_SUPPORT)
            ->latest()
            ->get();

        return response()->json([
            'data' => $requests->map(fn ($r) => $this->formatRequest($r)),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'phone_number' => ['required', 'string'],
            'message' => ['required', 'string'],
        ]);

        $userRequest = UserSupportRequest::create([
            'user_id' => $request->user()->id,
            'contact' => $request->phone_number,
            'type' => UserSupportRequest::TYPE_PHONE_NUMBER,
            'target' => UserSupportRequest::TARGET_CONTACT_SUPPORT,
            'note' => $request->message,
            'status' => UserSupportRequest::STATUS_PENDING,
        ]);

        return response()->json([
            'message' => 'Your message has been sent to our support team.',
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
            'target' => UserSupportRequest::TARGETS[$userRequest->target] ?? 'contact-support',
            'created_at' => $userRequest->created_at?->toISOString(),
            'updated_at' => $userRequest->updated_at?->toISOString(),
        ];
    }
}
