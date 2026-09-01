<?php

namespace App\Http\Controllers\Api\People;

use App\Http\Controllers\Controller;
use App\Models\CreatorFollower;
use App\Models\Friendship;
use App\Models\SupportRequest;
use App\Models\User;
use App\Models\UserContact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class ShowController extends Controller
{
    /**
     * Get profile details for a user/creator matching ProfileType.
     *
     * @param Request $request
     * @param int|string $id
     * @return JsonResponse
     */
    public function show(Request $request, int|string $id): JsonResponse
    {
        $currentUserId = $request->user('sanctum')?->id ?? $request->user()?->id;

        $targetUser = User::query()
            ->with(['roles'])
            ->where('id', $id)
            ->first();

        if (! $targetUser) {
            return response()->json([
                'message' => 'User not found.',
            ], 404);
        }

        $isSelf = $currentUserId && (int) $currentUserId === (int) $targetUser->id;

        // Determine profile_type: 'mine' | 'creator' | 'sgm' | 'user'
        $roleCodes = $targetUser->roles->pluck('code')->all();
        if ($isSelf) {
            $profileType = 'mine';
        } elseif (in_array('creator', $roleCodes, true)) {
            $profileType = 'creator';
        } elseif (in_array('sgm', $roleCodes, true)) {
            $profileType = 'sgm';
        } else {
            $profileType = 'user';
        }

        // Image URL
        $imageUrl = $targetUser->image_url;
        if ($imageUrl && ! str_starts_with($imageUrl, 'http://') && ! str_starts_with($imageUrl, 'https://')) {
            $imageUrl = url($imageUrl);
        }

        // Names & Handles
        $fullName = (string) ($targetUser->full_name ?? $targetUser->name ?? $targetUser->username ?? ('User #' . $targetUser->id));
        $username = (string) ($targetUser->username ?? '');
        $text2 = $username !== '' ? ('@' . ltrim($username, '@')) : '';

        // Friend & Creator Follow Status
        $friendStatus = null;
        $creatorFollowStatus = null;
        $incomingFriendRequest = null;
        $canMessage = false;

        if ($currentUserId && ! $isSelf) {
            // Find existing friendship relation
            $friendship = Friendship::where(function ($q) use ($currentUserId, $targetUser) {
                $q->where('user_id', $currentUserId)->where('friend_id', $targetUser->id);
            })->orWhere(function ($q) use ($currentUserId, $targetUser) {
                $q->where('user_id', $targetUser->id)->where('friend_id', $currentUserId);
            })->first();

            if ($friendship) {
                $friendStatus = $friendship->status; // 'pending', 'accepted', 'declined', 'blocked'
            }

            // Check if target user is followed by current user
            $isFollowing = CreatorFollower::where('user_id', $currentUserId)
                ->where('creator_id', $targetUser->id)
                ->exists();
            $creatorFollowStatus = $isFollowing ? 'followed' : null;

            // Check if there is an incoming pending friend request from targetUser to currentUser
            $incomingFriendRequest = Friendship::where('user_id', $targetUser->id)
                ->where('friend_id', $currentUserId)
                ->where('status', Friendship::STATUS_PENDING)
                ->first();

            // Messaging capability: allowed only if users are friends
            $canMessage = $friendStatus === Friendship::STATUS_ACCEPTED;
        } elseif ($isSelf) {
            $canMessage = false;
        }

        $friendRequestData = null;
        if ($incomingFriendRequest) {
            $friendRequestData = [
                'id' => (int) $incomingFriendRequest->id,
                'user' => [
                    'id' => (int) $targetUser->id,
                    'text1' => $fullName,
                    'text2' => $text2,
                ],
            ];
        }

        // Get contact details for this profile
        $contacts = $this->getContactsForUser($targetUser);
        $hasContacts = count($contacts) > 0;

        $data = [
            'id' => (int) $targetUser->id,
            'profile_type' => $profileType,
            'text1' => $fullName,
            'text2' => $text2,
            'image_url' => $imageUrl ?: null,
            'friend_status' => $friendStatus,
            'creator_follow_status' => $creatorFollowStatus,
            'can_message' => (bool) $canMessage,
            'has_contacts' => (bool) $hasContacts,
            'nb_contacts' => count($contacts),
            'contacts' => $contacts,
            'friend_request' => $friendRequestData,
        ];

        return response()->json([
            'data' => $data,
            ...$data,
        ], 200);
    }

    /**
     * Get contacts list for a user/creator profile.
     *
     * @param Request $request
     * @param int|string $id
     * @return JsonResponse
     */
    public function contacts(Request $request, int|string $id): JsonResponse
    {
        $targetUser = User::query()
            ->where('id', $id)
            ->first();

        if (! $targetUser) {
            return response()->json([
                'message' => 'User not found.',
            ], 404);
        }

        $contacts = $this->getContactsForUser($targetUser);

        return response()->json([
            'data' => $contacts,
            'total' => count($contacts),
        ], 200);
    }

    /**
     * Build structured contacts list for a user.
     *
     * @param User $user
     * @return array<int, array<string, mixed>>
     */
     protected function getContactsForUser(User $user): array
     {
         $contacts = [];

         if ($user->id) {
             $userContacts = UserContact::where('user_id', $user->id)
                 ->orderBy('id', 'asc')
                 ->get();

             if ($userContacts->isNotEmpty()) {
                 return $userContacts->map(fn (UserContact $c) => [
                     'id' => (string) $c->id,
                     'platform' => (string) $c->platform,
                     'type' => (string) ($c->type ?? 'social'),
                     'value' => (string) $c->value,
                     'url' => (string) ($c->url ?: $c->value),
                     'image_url' => null,
                 ])->all();
             }
         }

         // Fallback legacy behavior
         // 1. Phone number
         if (! empty($user->phone_number)) {
             $rawPhone = preg_replace('/[^\d+]/', '', $user->phone_number);
             $contacts[] = [
                 'id' => 'phone',
                 'platform' => 'Phone',
                 'type' => 'phone',
                 'value' => (string) $user->phone_number,
                 'url' => 'tel:' . $rawPhone,
                 'image_url' => null,
             ];

             // WhatsApp link
             $waNumber = ltrim((string) $rawPhone, '+');
             $contacts[] = [
                 'id' => 'whatsapp',
                 'platform' => 'WhatsApp',
                 'type' => 'whatsapp',
                 'value' => (string) $user->phone_number,
                 'url' => 'https://wa.me/' . $waNumber,
                 'image_url' => null,
             ];
         }

         // 2. Email
         if (! empty($user->email)) {
             $contacts[] = [
                 'id' => 'email',
                 'platform' => 'Email',
                 'type' => 'email',
                 'value' => (string) $user->email,
                 'url' => 'mailto:' . $user->email,
                 'image_url' => null,
             ];
         }

         // 3. Username / Social profile handle
         if (! empty($user->username)) {
             $cleanUsername = ltrim((string) $user->username, '@');
             $contacts[] = [
                 'id' => 'instagram',
                 'platform' => 'Instagram',
                 'type' => 'instagram',
                 'value' => '@' . $cleanUsername,
                 'url' => 'https://instagram.com/' . $cleanUsername,
                 'image_url' => null,
             ];
         }

         // 4. Additional Support / Social links
         if ($user->id) {
             $additional = SupportRequest::where('user_id', $user->id)
                 ->whereIn('type', ['phone_number', 'username', 'email', 'social', 'whatsapp', 'instagram', 'facebook', 'tiktok'])
                 ->get();

             foreach ($additional as $item) {
                 $platformLabel = ucfirst((string) ($item->target ?? $item->type ?? 'Contact'));
                 $contactVal = (string) $item->contact;
                 $url = $contactVal;

                 if ($item->type === 'email' && ! str_starts_with($contactVal, 'mailto:')) {
                     $url = 'mailto:' . $contactVal;
                 } elseif ($item->type === 'phone_number' && ! str_starts_with($contactVal, 'tel:')) {
                     $url = 'tel:' . preg_replace('/[^\d+]/', '', $contactVal);
                 }

                 $contacts[] = [
                     'id' => (string) $item->id,
                     'platform' => $platformLabel,
                     'type' => (string) ($item->type ?? 'contact'),
                     'value' => $contactVal,
                     'url' => $url,
                     'image_url' => null,
                 ];
             }
         }

         return $contacts;
     }

}
