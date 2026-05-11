<?php

namespace App\Http\Controllers\Api\Settings;

use App\Http\Controllers\Controller;
use App\Models\UserSupportRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BeacomeCreatorController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_if($user === null, 401);

        $creatorRequest = UserSupportRequest::query()
            ->where('user_id', $user->id)
            ->where('target', 'become-creator')
            ->latest('id')
            ->first();

        return response()->json([
            'data' => $creatorRequest === null ? null : $this->formatRequest($creatorRequest),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_if($user === null, 401);

        $validated = $request->validate([
            'phone_number' => ['required', 'string', 'max:255'],
        ]);

        $latestRequest = UserSupportRequest::query()
            ->where('user_id', $user->id)
            ->where('target', 'become-creator')
            ->latest('id')
            ->first();

        if ($latestRequest !== null && in_array($latestRequest->status, ['pending', 'approved'], true)) {
            return response()->json([
                'message' => 'You already have a creator request in progress.',
                'data' => $this->formatRequest($latestRequest),
            ], 422);
        }

        $creatorRequest = UserSupportRequest::query()->create([
            'user_id' => $user->id,
            'contact' => $validated['phone_number'],
            'type' => 'phone_number',
            'target' => 'become-creator',
            'status' => 'pending',
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
            'status' => $creatorRequest->status,
            'note' => $creatorRequest->note,
            'reviewed_at' => $creatorRequest->reviewed_at?->toISOString(),
            'created_at' => $creatorRequest->created_at?->toISOString(),
        ];
    }
}
