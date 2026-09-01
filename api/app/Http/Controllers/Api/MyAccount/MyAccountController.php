<?php

namespace App\Http\Controllers\Api\MyAccount;

use App\Http\Controllers\Controller;
use App\Models\CreatorFollower;
use App\Models\Friendship;
use App\Models\Product;
use App\Models\Role;
use App\Models\SavedDrop;
use App\Models\SavedLabel;
use App\Models\SavedProduct;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MyAccountController extends Controller
{
    /**
     * Get account details matching ResponseType schema:
     * - profile: { image_url, text1, text2 }
     * - essentials: { nb_friends, nb_followed_creators, nb_saved }
     * - creator_land: { nb_affilite_library }
     * - allowed_sections: string[]
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user('sanctum') ?? $request->user();

        if (! $user) {
            $userId = $request->query('user_id');
            if ($userId) {
                $user = User::with('roles')->find($userId);
            }
        } else {
            $user->loadMissing('roles');
        }

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // 1. Profile information
        $imageUrl = $user->image_url ?? '';
        if ($imageUrl && ! str_starts_with($imageUrl, 'http://') && ! str_starts_with($imageUrl, 'https://')) {
            $imageUrl = url($imageUrl);
        }

        $text1 = (string) ($user->full_name ?? $user->name ?? $user->username ?? 'My Name');
        $text2 = (string) ($user->username ? '@' . ltrim($user->username, '@') : ($user->email ?? ''));

        // 2. Essentials statistics
        $nbFriends = 0;
        $nbFollowedCreators = 0;
        $nbSaved = 0;

        if ($user->id) {
            // Friends count (accepted friendships)
            $nbFriends = Friendship::where('status', Friendship::STATUS_ACCEPTED)
                ->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                        ->orWhere('friend_id', $user->id);
                })
                ->count();

            // Followed creators count
            $nbFollowedCreators = CreatorFollower::where('user_id', $user->id)->count();

            // Saved count (saved products + saved drops + saved labels)
            $savedProductsCount = SavedProduct::where('user_id', $user->id)->count();
            $savedDropsCount = SavedDrop::where('user_id', $user->id)->count();
            $savedLabelsCount = SavedLabel::where('user_id', $user->id)->count();
            $nbSaved = $savedProductsCount + $savedDropsCount + $savedLabelsCount;
        }

        // 3. Creator land statistics
        // Available products for affiliate promotion
        $nbAffiliateLibrary = Product::where(function ($q) {
            $q->where('product_status', 'published')
                ->orWhereNull('product_status');
        })->count();

        // Creator followers count
        $nbFollowers = 0;
        if ($user->id) {
            $nbFollowers = CreatorFollower::where('creator_id', $user->id)->count();
        }

        // 4. Determine allowed sections based on user roles
        $userRoleCodes = $user->relationLoaded('roles')
            ? $user->roles->pluck('code')->map(fn($c) => strtolower((string) $c))->toArray()
            : [];

        $isCreator = in_array(Role::CREATOR, $userRoleCodes, true) || in_array(Role::ADMIN, $userRoleCodes, true);
        $isSgm = in_array(Role::SGM, $userRoleCodes, true) || in_array(Role::SGM, $userRoleCodes, true) || in_array(Role::ADMIN, $userRoleCodes, true);
        $isAdmin = in_array(Role::ADMIN, $userRoleCodes, true);

        // Essentials sections are available to all active accounts
        $allowedSections = [
            'essentials:friends',
            'essentials:followed-creator',
            'essentials:saved',
            'essentials:refund-wallet',
        ];

        // Creator sections
        if ($isCreator) {
            $allowedSections[] = 'creator:followers';
            $allowedSections[] = 'creator:affiliate-library';
            $allowedSections[] = 'creator:my-drops';
            $allowedSections[] = 'creator:balance';
        } else {
            // Standard users can see the option to apply/become a creator
            $allowedSections[] = 'creator:become-creator';
        }

        // Store General Manager (SGM) sections
        if ($isSgm || $isAdmin) {
            $allowedSections[] = 'sgm:stores';
            $allowedSections[] = 'sgm:learning-updates';
        }

        return response()->json([
            'profile' => [
                'image_url' => (string) $imageUrl,
                'text1' => (string) $text1,
                'text2' => (string) $text2,
            ],
            'essentials' => [
                'nb_friends' => (int) $nbFriends,
                'nb_followed_creators' => (int) $nbFollowedCreators,
                'nb_saved' => (int) $nbSaved,
            ],
            'creator_land' => [
                'nb_affilite_library' => (int) $nbAffiliateLibrary,
                'nb_followers' => (int) $nbFollowers,
            ],
            'allowed_sections' => $allowedSections,
        ], 200);
    }

    /**
     * Update user profile (ProfileEditType: { name, image_url }).
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

        $validator = Validator::make($request->all(), [
            'name' => ['nullable', 'string', 'max:255'],
            'image_url' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($request->has('name')) {
            $user->full_name = $request->input('name');
        }

        if ($request->has('image_url')) {
            $user->image_url = $request->input('image_url');
        }

        $user->save();

        $imageUrl = $user->image_url ?? '';
        if ($imageUrl && ! str_starts_with($imageUrl, 'http://') && ! str_starts_with($imageUrl, 'https://')) {
            $imageUrl = url($imageUrl);
        }

        return response()->json([
            'message' => 'Profile updated successfully',
            'profile' => [
                'name' => (string) ($user->full_name ?? $user->name ?? ''),
                'image_url' => (string) $imageUrl,
            ],
        ], 200);
    }
}
