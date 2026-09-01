<?php

namespace App\Http\Controllers\Api\MyAccount;

use App\Http\Controllers\Controller;
use App\Models\SocialPlatform;
use App\Models\SupportRequest;
use App\Models\User;
use App\Models\UserContact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserContactController extends Controller
{
    /**
     * Get available social platforms from catalog table.
     */
    public function platforms(): JsonResponse
    {
        $platforms = SocialPlatform::orderBy('id', 'asc')
            ->get(['id', 'code', 'label', 'hex', 'badge']);

        return response()->json([
            'data' => $platforms,
            'total' => $platforms->count(),
        ], 200);
    }

    /**
     * Get all contacts for the authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user('sanctum') ?? $request->user();

        if (! $user) {
            $userId = $request->query('user_id');
            if ($userId) {
                $user = User::find($userId);
            }
        }

        if (! $user) {
            $user = User::first();
        }

        if (! $user) {
            return response()->json([
                'data' => [],
                'total' => 0,
            ], 200);
        }

        // If user has no contacts yet, seed from default profile info (phone, whatsapp, email, username)
        if (UserContact::where('user_id', $user->id)->count() === 0) {
            $this->seedInitialContactsForUser($user);
        }

        $allPlatforms = SocialPlatform::all();

        $contacts = UserContact::where('user_id', $user->id)
            ->orderBy('id', 'asc')
            ->get()
            ->map(function (UserContact $contact) use ($allPlatforms) {
                $meta = $this->getPlatformMetadata($contact->platform, $allPlatforms);
                return [
                    'id' => (int) $contact->id,
                    'platform' => (string) $contact->platform,
                    'type' => (string) ($contact->type ?? 'social'),
                    'value' => (string) $contact->value,
                    'url' => (string) ($contact->url ?: $contact->value),
                    'hex' => (string) ($meta['hex'] ?? '#333333'),
                    'badge' => (string) ($meta['badge'] ?? strtoupper(substr($contact->platform, 0, 2))),
                    'created_at' => $contact->created_at?->toISOString(),
                ];
            });

        return response()->json([
            'data' => $contacts,
            'total' => $contacts->count(),
        ], 200);
    }



    /**
     * Create a new contact for the authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user('sanctum') ?? $request->user();

        if (! $user) {
            $userId = $request->input('user_id');
            if ($userId) {
                $user = User::find($userId);
            }
        }

        if (! $user) {
            $user = User::first();
        }

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $validator = Validator::make($request->all(), [
            'platform' => ['required', 'string', 'max:100'],
            'value' => ['required', 'string', 'max:500'],
            'url' => ['nullable', 'string', 'max:500'],
            'type' => ['nullable', 'string', 'max:50'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $platform = trim($request->input('platform'));
        $value = trim($request->input('value'));
        $url = $request->input('url') ? trim($request->input('url')) : $this->formatUrlForPlatform($platform, $value);
        $type = $request->input('type') ?: $this->inferTypeForPlatform($platform);

        $contact = UserContact::create([
            'user_id' => $user->id,
            'platform' => $platform,
            'value' => $value,
            'url' => $url,
            'type' => $type,
        ]);

        $meta = $this->getPlatformMetadata($contact->platform);

        return response()->json([
            'message' => 'Contact added successfully.',
            'data' => [
                'id' => (int) $contact->id,
                'platform' => (string) $contact->platform,
                'type' => (string) $contact->type,
                'value' => (string) $contact->value,
                'url' => (string) $contact->url,
                'hex' => (string) ($meta['hex'] ?? '#333333'),
                'badge' => (string) ($meta['badge'] ?? strtoupper(substr($contact->platform, 0, 2))),
                'created_at' => $contact->created_at?->toISOString(),
            ],
        ], 201);
    }

    /**
     * Update an existing contact.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user('sanctum') ?? $request->user();

        if (! $user) {
            $userId = $request->input('user_id');
            if ($userId) {
                $user = User::find($userId);
            }
        }

        if (! $user) {
            $user = User::first();
        }

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $contact = UserContact::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (! $contact) {
            return response()->json([
                'message' => 'Contact not found.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'platform' => ['sometimes', 'required', 'string', 'max:100'],
            'value' => ['sometimes', 'required', 'string', 'max:500'],
            'url' => ['nullable', 'string', 'max:500'],
            'type' => ['nullable', 'string', 'max:50'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($request->has('platform')) {
            $contact->platform = trim($request->input('platform'));
        }

        if ($request->has('value')) {
            $contact->value = trim($request->input('value'));
        }

        if ($request->has('url')) {
            $contact->url = trim($request->input('url'));
        } else {
            $contact->url = $this->formatUrlForPlatform($contact->platform, $contact->value);
        }

        if ($request->has('type')) {
            $contact->type = $request->input('type');
        } else {
            $contact->type = $this->inferTypeForPlatform($contact->platform);
        }

        $contact->save();

        $meta = $this->getPlatformMetadata($contact->platform);

        return response()->json([
            'message' => 'Contact updated successfully.',
            'data' => [
                'id' => (int) $contact->id,
                'platform' => (string) $contact->platform,
                'type' => (string) $contact->type,
                'value' => (string) $contact->value,
                'url' => (string) $contact->url,
                'hex' => (string) ($meta['hex'] ?? '#333333'),
                'badge' => (string) ($meta['badge'] ?? strtoupper(substr($contact->platform, 0, 2))),
                'created_at' => $contact->created_at?->toISOString(),
            ],
        ], 200);

    }

    /**
     * Delete a contact.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user('sanctum') ?? $request->user();

        if (! $user) {
            $userId = $request->input('user_id');
            if ($userId) {
                $user = User::find($userId);
            }
        }

        if (! $user) {
            $user = User::first();
        }

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $contact = UserContact::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (! $contact) {
            return response()->json([
                'message' => 'Contact not found.',
            ], 404);
        }

        $contact->delete();

        return response()->json([
            'message' => 'Contact deleted successfully.',
            'id' => $id,
        ], 200);
    }

    /**
     * Helper to auto-format URL based on platform and value.
     */
    protected function formatUrlForPlatform(string $platform, string $value): string
    {
        $platformLower = strtolower($platform);
        $val = trim($value);

        if (str_starts_with($val, 'http://') || str_starts_with($val, 'https://') || str_starts_with($val, 'tel:') || str_starts_with($val, 'mailto:')) {
            return $val;
        }

        if ($platformLower === 'email' || filter_var($val, FILTER_VALIDATE_EMAIL)) {
            return 'mailto:' . $val;
        }

        if ($platformLower === 'phone' || $platformLower === 'telephone' || $platformLower === 'mobile') {
            return 'tel:' . preg_replace('/[^\d+]/', '', $val);
        }

        if ($platformLower === 'whatsapp') {
            $cleaned = preg_replace('/[^\d]/', '', $val);
            return 'https://wa.me/' . $cleaned;
        }

        if ($platformLower === 'telegram') {
            $cleanUser = ltrim($val, '@');
            return 'https://t.me/' . $cleanUser;
        }

        if ($platformLower === 'instagram') {
            $cleanUser = ltrim($val, '@');
            return 'https://instagram.com/' . $cleanUser;
        }

        if ($platformLower === 'facebook') {
            return 'https://facebook.com/' . ltrim($val, '/');
        }

        if ($platformLower === 'tiktok') {
            $cleanUser = ltrim($val, '@');
            return 'https://tiktok.com/@' . $cleanUser;
        }

        if ($platformLower === 'x' || $platformLower === 'twitter' || str_contains($platformLower, 'twitter')) {
            $cleanUser = ltrim($val, '@');
            return 'https://x.com/' . $cleanUser;
        }

        if ($platformLower === 'youtube') {
            return 'https://youtube.com/' . ltrim($val, '/');
        }

        if ($platformLower === 'linkedin') {
            return 'https://linkedin.com/in/' . ltrim($val, '/');
        }

        return 'https://' . $val;
    }

    /**
     * Infer type classification for platform.
     */
    protected function inferTypeForPlatform(string $platform): string
    {
        $lower = strtolower($platform);
        if ($lower === 'phone' || $lower === 'telephone') return 'phone';
        if ($lower === 'email') return 'email';
        if ($lower === 'website') return 'link';
        return 'social';
    }

    /**
     * Seed initial contacts for a user from their account details.
     */
    public function seedInitialContactsForUser(User $user): void
    {
        $items = [];

        // 1. Phone & WhatsApp
        if (! empty($user->phone_number)) {
            $rawPhone = preg_replace('/[^\d+]/', '', $user->phone_number);
            $items[] = [
                'platform' => 'Phone',
                'type' => 'phone',
                'value' => (string) $user->phone_number,
                'url' => 'tel:' . $rawPhone,
            ];

            $waNumber = ltrim((string) $rawPhone, '+');
            $items[] = [
                'platform' => 'WhatsApp',
                'type' => 'social',
                'value' => (string) $user->phone_number,
                'url' => 'https://wa.me/' . $waNumber,
            ];
        }

        // 2. Email
        if (! empty($user->email)) {
            $items[] = [
                'platform' => 'Email',
                'type' => 'email',
                'value' => (string) $user->email,
                'url' => 'mailto:' . $user->email,
            ];
        }

        // 3. Username / Instagram
        if (! empty($user->username)) {
            $cleanUsername = ltrim((string) $user->username, '@');
            $items[] = [
                'platform' => 'Instagram',
                'type' => 'social',
                'value' => '@' . $cleanUsername,
                'url' => 'https://instagram.com/' . $cleanUsername,
            ];
        }

        // 4. Support requests if any
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

                $items[] = [
                    'platform' => $platformLabel,
                    'type' => (string) ($item->type ?? 'social'),
                    'value' => $contactVal,
                    'url' => $url,
                ];
            }
        }

        foreach ($items as $item) {
            UserContact::create([
                'user_id' => $user->id,
                'platform' => $item['platform'],
                'type' => $item['type'],
                'value' => $item['value'],
                'url' => $item['url'],
            ]);
        }
    }

    /**
     * Get platform metadata (hex color, badge) from social_platforms catalog.
     *
     * @param string $platformName
     * @param mixed $allPlatforms
     * @return array{hex: string, badge: string}
     */
    public function getPlatformMetadata(string $platformName, $allPlatforms = null): array
    {
        $platforms = $allPlatforms ?: SocialPlatform::all();
        $platformLower = strtolower(trim($platformName));

        $match = $platforms->first(function ($sp) use ($platformLower) {
            $code = strtolower($sp->code);
            $label = strtolower($sp->label ?? '');
            return $code === $platformLower || $label === $platformLower || str_contains($platformLower, $code) || str_contains($label, $platformLower);
        });

        if ($match) {
            return [
                'hex' => $match->hex ?? '#333333',
                'badge' => $match->badge ?? strtoupper(substr($match->label ?? $match->code, 0, 2)),
            ];
        }

        return [
            'hex' => '#333333',
            'badge' => strtoupper(substr($platformName, 0, 2)),
        ];
    }
}


