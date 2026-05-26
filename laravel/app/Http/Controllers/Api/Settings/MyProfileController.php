<?php

namespace App\Http\Controllers\Api\Settings;

use App\Http\Controllers\Controller;
use App\Models\Friendship;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Role;
use App\Models\SavedDrop;
use App\Models\SavedProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MyProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->load('roles');

        $isUser = $user->roles->contains('code', Role::USER);
        $isCreator = $user->roles->contains('code', Role::CREATOR);
        $isSgm = $user->roles->contains('code', Role::SGM);

        $friendsCount = $user->sentFriendships()
            ->where('status', Friendship::STATUS_ACCEPTED)
            ->count()
            + $user->receivedFriendships()
            ->where('status', Friendship::STATUS_ACCEPTED)
            ->count();

        $followedCreatorsCount = $user->followedCreators()->count();

        $savedCount = SavedProduct::query()
            ->where('user_id', $user->id)
            ->count()
            + SavedDrop::query()
            ->where('user_id', $user->id)
            ->count();

        $followersCount = $user->followers()->count();
        $storesCount = $user->stores()->count();

        $affiliateLibraryCount = Product::query()
            ->where('status', Product::STATUS_PUBLISHED)
            ->whereHas('paymentMethod', function ($query): void {
                $query->where('code', PaymentMethod::ONLINE);
            })
            ->count();

        $sections = [
            [
                'code' => 'essentials',
                'title' => 'Essentials',
                'items' => [
                    ['code' => 'friends', 'count' => $friendsCount],
                    ['code' => 'followed-creators', 'count' => $followedCreatorsCount],
                    ['code' => 'saved', 'count' => $savedCount],
                    ['code' => 'refund-wallet', 'count' => null],
                ],
            ],
        ];

        $sections[] = [
            'code' => 'creator-land',
            'title' => 'Creator land',
            'items' => $isCreator
                ? [
                    ['code' => 'followers', 'count' => $followersCount],
                    ['code' => 'affiliate-library', 'count' => $affiliateLibraryCount],
                    ['code' => 'my-drops', 'count' => null],
                    ['code' => 'balance', 'count' => null],
                ]
                : [
                    ['code' => 'become-creator', 'count' => null],
                ],
        ];

        if ($isSgm) {
            $sections[] = [
                'code' => 'sgm',
                'title' => 'Store General Manager (SGM)',
                'items' => [
                    ['code' => 'stores', 'count' => $storesCount],
                    ['code' => 'learning-updates', 'count' => null],
                ],
            ];
        }

        return response()->json([
            'data' => [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'username' => $user->username,
                'image' => $user->image,
                'phone_number' => $user->phone_number,
                'email' => $user->email,

                'roles' => $user->roles->map(fn($role) => [
                    'id' => $role->id,
                    'code' => $role->code,
                    'name' => $role->en,
                ])->values(),

                'flags' => [
                    'is_user' => $isUser,
                    'is_creator' => $isCreator,
                    'is_sgm' => $isSgm,
                ],

                'stats' => [
                    'friends' => $friendsCount,
                    'followed_creators' => $followedCreatorsCount,
                    'saved' => $savedCount,
                    'followers' => $followersCount,
                    'stores' => $storesCount,
                ],

                'sections' => $sections,
            ],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($request->has('username')) {
            $request->merge([
                'username' => strtolower(trim((string) $request->input('username'))),
            ]);
        }

        if ($request->has('phone_number')) {
            $request->merge([
                'phone_number' => trim((string) $request->input('phone_number')),
            ]);
        }

        if ($request->has('full_name')) {
            $request->merge([
                'full_name' => trim((string) $request->input('full_name')),
            ]);
        }

        $validated = $request->validate([
            'full_name' => [
                'sometimes',
                'string',
                'max:255',
            ],

            'username' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($user->id),
            ],

            'phone_number' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('users', 'phone_number')->ignore($user->id),
            ],

            'image' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $user->forceFill($validated)->save();

        return response()->json([
            'message' => 'Profile updated successfully.',
            'data' => [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'username' => $user->username,
                'phone_number' => $user->phone_number,
                'image' => $user->image,
            ],
        ]);
    }
}
