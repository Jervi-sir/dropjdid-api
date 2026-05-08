<?php

namespace App\Http\Controllers\Api\Settings;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ContactsController extends Controller
{
    public function getMyContacts(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_if($user === null, 401);

        $contacts = $user->contacts()
            ->with('socialPlatform')
            ->orderBy('id')
            ->get();

        return response()->json([
            'data' => $contacts->map(fn (Contact $contact): array => $this->formatContact($contact))->values(),
        ]);
    }

    public function upsertContact(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_if($user === null, 401);

        $validated = $request->validate([
            'id' => [
                'nullable',
                'integer',
                Rule::exists('contacts', 'id')->where(fn ($query) => $query->where('user_id', $user->id)),
            ],
            'social_platform_id' => ['required', 'integer', 'exists:social_platforms,id'],
            'url' => ['required', 'string', 'max:255'],
        ]);

        $contact = Contact::query()->firstOrNew([
            'id' => $validated['id'] ?? null,
            'user_id' => $user->id,
        ]);

        $contact->forceFill([
            'user_id' => $user->id,
            'social_platform_id' => $validated['social_platform_id'],
            'url' => trim($validated['url']),
        ])->save();

        $contact->load('socialPlatform');

        return response()->json([
            'message' => 'Contact saved successfully.',
            'data' => $this->formatContact($contact),
        ]);
    }

    public function deleteContact(Request $request, Contact $contact): JsonResponse
    {
        $user = $request->user();

        abort_if($user === null, 401);
        abort_if($contact->user_id !== $user->id, 404);

        $contact->delete();

        return response()->json([
            'message' => 'Contact deleted successfully.',
        ]);
    }

    /**
     * @return array{id:int, url:string, social_platform: array{id:int, code:string, en:mixed, fr:mixed, ar:mixed}}
     */
    private function formatContact(Contact $contact): array
    {
        $socialPlatform = $contact->socialPlatform;

        return [
            'id' => $contact->id,
            'url' => $contact->url,
            'social_platform' => [
                'id' => $socialPlatform?->id,
                'code' => $socialPlatform?->code,
                'en' => $socialPlatform?->en,
                'fr' => $socialPlatform?->fr,
                'ar' => $socialPlatform?->ar,
            ],
        ];
    }
}
