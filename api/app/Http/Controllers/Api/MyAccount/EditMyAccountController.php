<?php

namespace App\Http\Controllers\Api\MyAccount;

use App\Http\Controllers\Controller;
use App\Models\SupportRequest;
use App\Models\User;
use App\Models\UserContact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EditMyAccountController extends Controller
{
    /**
     * Get account details for edit profile screen matching AccountType:
     * - id: number
     * - image_url: string
     * - name: string
     * - nb_contacts: number
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
            $user = User::first() ?? new User([
                'id' => 1,
                'full_name' => 'Demo User',
                'image_url' => '',
            ]);
        }

        $imageUrl = $user->image_url ?? '';
        if ($imageUrl && ! str_starts_with($imageUrl, 'http://') && ! str_starts_with($imageUrl, 'https://')) {
            $imageUrl = url($imageUrl);
        }

        $name = (string) ($user->full_name ?? $user->name ?? $user->username ?? '');

        // Count contacts (UserContact entries, or fallback to phone + email + support requests)
        $userContactsCount = $user->id ? UserContact::where('user_id', $user->id)->count() : 0;
        $nbContacts = $userContactsCount;
        if ($nbContacts === 0) {
            if (! empty($user->phone_number)) {
                $nbContacts++;
            }
            if (! empty($user->email)) {
                $nbContacts++;
            }
            if ($user->id) {
                $additionalContacts = SupportRequest::where('user_id', $user->id)
                    ->whereIn('type', ['phone_number', 'username', 'email', 'social'])
                    ->count();
                $nbContacts += $additionalContacts;
            }
        }

        return response()->json([
            'id' => (int) $user->id,
            'image_url' => (string) $imageUrl,
            'name' => (string) $name,
            'nb_contacts' => (int) $nbContacts,
        ], 200);
    }

    /**
     * Update account details (name, image file, image_url).
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
            // Fallback for development if no user session
            $user = User::first();
        }

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => ['nullable', 'string', 'max:255'],
            'image_url' => ['nullable', 'string'],
            'image' => ['nullable', 'file', 'image', 'max:10240'],
            'avatar' => ['nullable', 'file', 'image', 'max:10240'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($request->has('name') && $request->input('name') !== null) {
            $user->full_name = trim($request->input('name'));
        }

        // 1. Handle multipart file upload (image / avatar)
        if ($request->hasFile('image') || $request->hasFile('avatar')) {
            $file = $request->file('image') ?? $request->file('avatar');
            $path = $file->store('avatars', 'public');
            $user->image_url = '/storage/' . $path;
        } elseif ($request->filled('image_url')) {
            $imageUrl = $request->input('image_url');

            // 2. Handle base64 image data URL
            if (preg_match('/^data:image\/(\w+);base64,/', $imageUrl, $type)) {
                $data = substr($imageUrl, strpos($imageUrl, ',') + 1);
                $ext = strtolower($type[1]);
                $ext = ($ext === 'jpeg') ? 'jpg' : $ext;
                $decoded = base64_decode($data);

                if ($decoded !== false) {
                    $filename = 'avatars/avatar_' . $user->id . '_' . time() . '.' . $ext;
                    \Illuminate\Support\Facades\Storage::disk('public')->put($filename, $decoded);
                    $user->image_url = '/storage/' . $filename;
                }
            } else {
                $user->image_url = $imageUrl;
            }
        }

        $user->save();

        $imageUrl = $user->image_url ?? '';
        if ($imageUrl && ! str_starts_with($imageUrl, 'http://') && ! str_starts_with($imageUrl, 'https://')) {
            $imageUrl = url($imageUrl);
        }

        $name = (string) ($user->full_name ?? $user->name ?? $user->username ?? '');

        $userContactsCount = $user->id ? UserContact::where('user_id', $user->id)->count() : 0;
        $nbContacts = $userContactsCount;
        if ($nbContacts === 0) {
            if (! empty($user->phone_number)) {
                $nbContacts++;
            }
            if (! empty($user->email)) {
                $nbContacts++;
            }
            if ($user->id) {
                $additionalContacts = SupportRequest::where('user_id', $user->id)
                    ->whereIn('type', ['phone_number', 'username', 'email', 'social'])
                    ->count();
                $nbContacts += $additionalContacts;
            }
        }


        return response()->json([
            'message' => 'Account updated successfully',
            'data' => [
                'id' => (int) $user->id,
                'image_url' => (string) $imageUrl,
                'name' => (string) $name,
                'nb_contacts' => (int) $nbContacts,
            ],
        ], 200);
    }
}
