<?php

use App\Models\Contact;
use App\Models\Role;
use App\Models\SocialPlatform;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function makeUser(): User
{
    $roleId = Role::query()->firstOrCreate([
        'code' => 'user',
    ], [
        'en' => 'User',
    ])->id;

    return User::query()->create([
        'role_id' => $roleId,
        'full_name' => fake()->name(),
        'username' => fake()->unique()->userName(),
        'phone_number' => fake()->unique()->numerify('05########'),
        'email' => fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
    ]);
}

it('lists authenticated user contacts', function (): void {
    $user = makeUser();
    $facebook = SocialPlatform::query()->create([
        'code' => 'facebook',
        'en' => 'Facebook',
    ]);
    $instagram = SocialPlatform::query()->create([
        'code' => 'instagram',
        'en' => 'Instagram',
    ]);

    Contact::query()->create([
        'user_id' => $user->id,
        'social_platform_id' => $facebook->id,
        'url' => 'https://facebook.com/my-profile',
    ]);

    Contact::query()->create([
        'user_id' => $user->id,
        'social_platform_id' => $instagram->id,
        'url' => 'https://instagram.com/my-profile',
    ]);

    Contact::query()->create([
        'user_id' => makeUser()->id,
        'social_platform_id' => $facebook->id,
        'url' => 'https://facebook.com/other-user',
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/settings/contacts');

    $response
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.social_platform.code', 'facebook')
        ->assertJsonPath('data.1.social_platform.code', 'instagram');
});

it('creates and updates a contact for the authenticated user', function (): void {
    $user = makeUser();
    $facebook = SocialPlatform::query()->create([
        'code' => 'facebook',
        'en' => 'Facebook',
    ]);
    $instagram = SocialPlatform::query()->create([
        'code' => 'instagram',
        'en' => 'Instagram',
    ]);

    $createResponse = $this->actingAs($user, 'sanctum')
        ->postJson('/api/settings/contacts', [
            'social_platform_id' => $facebook->id,
            'url' => ' https://facebook.com/new-contact ',
        ]);

    $createResponse
        ->assertOk()
        ->assertJsonPath('data.social_platform.code', 'facebook')
        ->assertJsonPath('data.url', 'https://facebook.com/new-contact');

    $contactId = $createResponse->json('data.id');

    expect($contactId)->not->toBeNull();

    $updateResponse = $this->actingAs($user, 'sanctum')
        ->postJson('/api/settings/contacts', [
            'id' => $contactId,
            'social_platform_id' => $instagram->id,
            'url' => 'https://instagram.com/updated-contact',
        ]);

    $updateResponse
        ->assertOk()
        ->assertJsonPath('data.id', $contactId)
        ->assertJsonPath('data.social_platform.code', 'instagram')
        ->assertJsonPath('data.url', 'https://instagram.com/updated-contact');

    $this->assertDatabaseHas('contacts', [
        'id' => $contactId,
        'user_id' => $user->id,
        'social_platform_id' => $instagram->id,
        'url' => 'https://instagram.com/updated-contact',
    ]);
});

it('deletes only the authenticated user contact', function (): void {
    $user = makeUser();
    $platform = SocialPlatform::query()->create([
        'code' => 'facebook',
        'en' => 'Facebook',
    ]);

    $contact = Contact::query()->create([
        'user_id' => $user->id,
        'social_platform_id' => $platform->id,
        'url' => 'https://facebook.com/delete-me',
    ]);

    $this->actingAs($user, 'sanctum')
        ->deleteJson('/api/settings/contacts/'.$contact->id)
        ->assertOk();

    $this->assertDatabaseMissing('contacts', [
        'id' => $contact->id,
    ]);
});

it('does not allow updating another users contact', function (): void {
    $user = makeUser();
    $otherUser = makeUser();
    $platform = SocialPlatform::query()->create([
        'code' => 'facebook',
        'en' => 'Facebook',
    ]);

    $contact = Contact::query()->create([
        'user_id' => $otherUser->id,
        'social_platform_id' => $platform->id,
        'url' => 'https://facebook.com/private-contact',
    ]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/settings/contacts', [
            'id' => $contact->id,
            'social_platform_id' => $platform->id,
            'url' => 'https://facebook.com/hacked-contact',
        ])
        ->assertUnprocessable();

    $this->actingAs($user, 'sanctum')
        ->deleteJson('/api/settings/contacts/'.$contact->id)
        ->assertNotFound();
});
