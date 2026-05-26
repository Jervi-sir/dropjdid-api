<?php

use App\Models\Role;
use App\Models\User;
use App\Models\UserSupportRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected to the login page from creator requests moderation', function () {
    $response = $this->get(route('admin.creators.list_to_approve'));
    $response->assertRedirect(route('login'));
});

test('admin can visit list to approve creator requests page', function () {
    $admin = User::factory()->create();
    $role = Role::query()->create([
        'code' => Role::ADMIN,
        'en' => 'Admin',
    ]);
    $admin->roles()->attach($role->id);

    $user = User::factory()->create();
    $request = UserSupportRequest::query()->create([
        'user_id' => $user->id,
        'contact' => '12345678',
        'type' => UserSupportRequest::TYPE_PHONE_NUMBER,
        'target' => UserSupportRequest::TARGET_BECOME_CREATOR,
        'status' => UserSupportRequest::STATUS_PENDING,
    ]);

    $this->actingAs($admin);

    $response = $this->get(route('admin.creators.list_to_approve'));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/list-to-approve/approve-creators')
        ->has('requests.data', 1)
        ->where('requests.data.0.contact', '12345678')
    );
});

test('admin can view a single request details via XHR', function () {
    $admin = User::factory()->create();
    $role = Role::query()->create([
        'code' => Role::ADMIN,
        'en' => 'Admin',
    ]);
    $admin->roles()->attach($role->id);

    $user = User::factory()->create();
    $request = UserSupportRequest::query()->create([
        'user_id' => $user->id,
        'contact' => '987654321',
        'type' => UserSupportRequest::TYPE_PHONE_NUMBER,
        'target' => UserSupportRequest::TARGET_BECOME_CREATOR,
        'status' => UserSupportRequest::STATUS_PENDING,
    ]);

    $this->actingAs($admin);

    $response = $this->getJson(route('admin.creators.show', $request));
    $response->assertOk();
    $response->assertJsonPath('request.contact', '987654321');
    $response->assertJsonPath('request.user.id', $user->id);
});

test('admin can approve become-creator request and assign creator role', function () {
    $admin = User::factory()->create();
    $adminRole = Role::query()->create([
        'code' => Role::ADMIN,
        'en' => 'Admin',
    ]);
    $admin->roles()->attach($adminRole->id);

    $creatorRole = Role::query()->create([
        'code' => Role::CREATOR,
        'en' => 'Creator',
    ]);

    $user = User::factory()->create();
    $request = UserSupportRequest::query()->create([
        'user_id' => $user->id,
        'contact' => '12345678',
        'type' => UserSupportRequest::TYPE_PHONE_NUMBER,
        'target' => UserSupportRequest::TARGET_BECOME_CREATOR,
        'status' => UserSupportRequest::STATUS_PENDING,
    ]);

    $this->actingAs($admin);

    $response = $this->post(route('admin.creators.approve', $request));
    $response->assertRedirect();

    $freshRequest = $request->fresh();
    expect($freshRequest->status)->toBe(UserSupportRequest::STATUS_APPROVED);
    expect($freshRequest->reviewed_at)->not->toBeNull();

    $freshUser = $user->fresh();
    expect($freshUser->hasRole(Role::CREATOR))->toBeTrue();
});

test('admin can reject become-creator request with note', function () {
    $admin = User::factory()->create();
    $adminRole = Role::query()->create([
        'code' => Role::ADMIN,
        'en' => 'Admin',
    ]);
    $admin->roles()->attach($adminRole->id);

    $user = User::factory()->create();
    $request = UserSupportRequest::query()->create([
        'user_id' => $user->id,
        'contact' => '12345678',
        'type' => UserSupportRequest::TYPE_PHONE_NUMBER,
        'target' => UserSupportRequest::TARGET_BECOME_CREATOR,
        'status' => UserSupportRequest::STATUS_PENDING,
    ]);

    $this->actingAs($admin);

    $response = $this->post(route('admin.creators.reject', $request), [
        'note' => 'Your phone number is invalid or profile is incomplete.',
    ]);
    $response->assertRedirect();

    $freshRequest = $request->fresh();
    expect($freshRequest->status)->toBe(UserSupportRequest::STATUS_REJECTED);
    expect($freshRequest->note)->toBe('Your phone number is invalid or profile is incomplete.');
    expect($freshRequest->reviewed_at)->not->toBeNull();
});
