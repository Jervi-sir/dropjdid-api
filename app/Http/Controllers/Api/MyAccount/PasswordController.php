<?php

namespace App\Http\Controllers\Api\MyAccount;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Change / update current user's password.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user('sanctum') ?? $request->user();

        if (! $user) {
            $userId = $request->input('user_id');
            if ($userId) {
                $user = User::find($userId);
            }
        }

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Normalize input keys: old_password/current_password and new_password/password
        $currentPassword = $request->input('old_password') ?? $request->input('current_password');
        $newPassword = $request->input('new_password') ?? $request->input('password');
        $newPasswordConfirmation = $request->input('new_password_confirmation') ?? $request->input('password_confirmation');

        $dataToValidate = [
            'current_password' => $currentPassword,
            'new_password' => $newPassword,
        ];

        if ($newPasswordConfirmation !== null) {
            $dataToValidate['new_password_confirmation'] = $newPasswordConfirmation;
        }

        $rules = [
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:6'],
        ];

        if ($newPasswordConfirmation !== null) {
            $rules['new_password'][] = 'confirmed';
        }

        $validator = Validator::make($dataToValidate, $rules, [
            'current_password.required' => 'The current password is required.',
            'new_password.required' => 'The new password is required.',
            'new_password.min' => 'The new password must be at least 6 characters.',
            'new_password.confirmed' => 'The new password confirmation does not match.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        // Verify that current password is correct
        if (! Hash::check($currentPassword, $user->password)) {
            return response()->json([
                'message' => 'The provided current password does not match our records.',
                'errors' => [
                    'old_password' => ['The provided current password does not match our records.'],
                    'current_password' => ['The provided current password does not match our records.'],
                ],
            ], 422);
        }

        // Update password
        $user->password = Hash::make($newPassword);
        $user->save();

        return response()->json([
            'message' => 'Password updated successfully.',
        ], 200);
    }
}
