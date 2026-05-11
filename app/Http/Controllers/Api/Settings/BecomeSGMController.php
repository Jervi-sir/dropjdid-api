<?php

namespace App\Http\Controllers\Api\Settings;

use App\Http\Controllers\Controller;
use App\Models\UserSupportRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BecomeSGMController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $userRequest = UserSupportRequest::where('user_id', $request->user()->id)
            ->where('target', 'become-sgm')
            ->latest()
            ->first();

        return response()->json([
            'data' => $userRequest,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'phone_number' => ['required', 'string'],
        ]);

        $existingRequest = UserSupportRequest::where('user_id', $request->user()->id)
            ->where('target', 'become-sgm')
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            return response()->json([
                'message' => 'You already have a pending request.',
                'data' => $existingRequest,
            ], 422);
        }

        $userRequest = UserSupportRequest::create([
            'user_id' => $request->user()->id,
            'contact' => $request->phone_number,
            'type' => 'phone_number',
            'target' => 'become-sgm',
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Request submitted successfully.',
            'data' => $userRequest,
        ]);
    }
}
