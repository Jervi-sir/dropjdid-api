<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSupportRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            $contact = $request->query('email') ?? $request->query('phone_number') ?? $request->query('username') ?? $request->query('contact');
            if ($contact) {
                $user = User::where('email', $contact)
                    ->orWhere('phone_number', $contact)
                    ->orWhere('username', $contact)
                    ->first();
            }
        }

        $userRequest = $user ? UserSupportRequest::where('user_id', $user->id)
            ->where('target', 'forgot-password')
            ->latest()
            ->first() : null;

        return response()->json([
            'data' => $userRequest,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => ['nullable', 'string'],
            'phone_number' => ['nullable', 'string'],
            'email' => ['nullable', 'email'],
        ]);

        $user = null;
        $type = null;
        $contact = null;

        if (! empty($validated['email'])) {
            $contact = $validated['email'];
            $type = 'email';
            $user = User::where('email', $contact)->first();
        } elseif (! empty($validated['phone_number'])) {
            $contact = $validated['phone_number'];
            $user = User::where('phone_number', $contact)->first();
            if ($user) {
                $type = 'phone_number';
            } else {
                $user = User::where('username', $contact)->first();
                if ($user) {
                    $type = 'username';
                }
            }
        } elseif (! empty($validated['username'])) {
            $contact = $validated['username'];
            $type = 'username';
            $user = User::where('username', $contact)->first();
        }

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ['We could not find a user with the provided information.'],
            ]);
        }

        $existingRequest = UserSupportRequest::where('user_id', $user->id)
            ->where('target', 'forgot-password')
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            return response()->json([
                'message' => 'You already have a pending password reset request.',
                'data' => $existingRequest,
            ], 422);
        }

        $userRequest = UserSupportRequest::create([
            'user_id' => $user->id,
            'contact' => $contact,
            'type' => $type,
            'target' => 'forgot-password',
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Your password reset request has been sent to our support team for review.',
            'data' => $userRequest,
        ]);
    }
}
