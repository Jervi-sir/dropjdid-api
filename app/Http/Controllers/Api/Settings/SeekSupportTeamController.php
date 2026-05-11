<?php

namespace App\Http\Controllers\Api\Settings;

use App\Http\Controllers\Controller;
use App\Models\UserSupportRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SeekSupportTeamController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $requests = UserSupportRequest::where('user_id', $request->user()->id)
            ->where('target', 'contact-support')
            ->latest()
            ->get();

        return response()->json([
            'data' => $requests,
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
            'type' => 'phone_number',
            'target' => 'contact-support',
            'note' => $request->message,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Your message has been sent to our support team.',
            'data' => $userRequest,
        ]);
    }
}
