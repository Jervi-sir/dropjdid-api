<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => ['nullable', 'string'],
            'email' => ['nullable', 'email'],
        ]);

        $email = $validated['email'] ?? null;

        if (! $email && ! empty($validated['username'])) {
            $email = User::query()
                ->where('username', $validated['username'])
                ->value('email');
        }

        if (! $email) {
            throw ValidationException::withMessages([
                'email' => ['An email address is required to send a reset link.'],
            ]);
        }

        $status = Password::sendResetLink(['email' => $email]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json([
            'message' => __($status),
        ]);
    }
}
