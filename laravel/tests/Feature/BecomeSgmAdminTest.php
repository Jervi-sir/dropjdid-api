<?php

use App\Models\Role;
use App\Models\User;
use App\Models\UserSupportRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected to the login page from SGM requests moderation', function () {
    $response = $this->get(route('admin.sgms.list_to_approve'));
    $response->assertRedirect(route('login'));
});

test('admin can visit list to approve SGM requests page', function () {
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
        'target' => UserSupportRequest::TARGET_BECOME_SGM,
        'status' => UserSupportRequest::STATUS_PENDING,
    ]);

    $this->actingAs($admin);

    $response = $this->get(route('admin.sgms.list_to_approve'));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/list-to-approve/approve-sgm')
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
        'target' => UserSupportRequest::TARGET_BECOME_SGM,
        'status' => UserSupportRequest::STATUS_PENDING,
    ]);

    $this->actingAs($admin);

    $response = $this->getJson(route('admin.sgms.show', $request));
    $response->assertOk();
    $response->assertJsonPath('request.contact', '987654321');
    $response->assertJsonPath('request.user.id', $user->id);
});

test('admin can approve become-sgm request and assign SGM role', function () {
    $admin = User::factory()->create();
    $adminRole = Role::query()->create([
        'code' => Role::ADMIN,
        'en' => 'Admin',
    ]);
    $admin->roles()->attach($adminRole->id);

    $sgmRole = Role::query()->create([
        'code' => Role::SGM,
        'en' => 'sgm',
    ]);

    $user = User::factory()->create();
    $request = UserSupportRequest::query()->create([
        'user_id' => $user->id,
        'contact' => '12345678',
        'type' => UserSupportRequest::TYPE_PHONE_NUMBER,
        'target' => UserSupportRequest::TARGET_BECOME_SGM,
        'status' => UserSupportRequest::STATUS_PENDING,
    ]);

    $this->actingAs($admin);

    $response = $this->post(route('admin.sgms.approve', $request));
    $response->assertRedirect();

    $freshRequest = $request->fresh();
    expect($freshRequest->status)->toBe(UserSupportRequest::STATUS_APPROVED);
    expect($freshRequest->reviewed_at)->not->toBeNull();

    $freshUser = $user->fresh();
    expect($freshUser->hasRole(Role::SGM))->toBeTrue();
});

test('admin can reject become-sgm request with note', function () {
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
        'target' => UserSupportRequest::TARGET_BECOME_SGM,
        'status' => UserSupportRequest::STATUS_PENDING,
    ]);

    $this->actingAs($admin);

    $response = $this->post(route('admin.sgms.reject', $request), [
        'note' => 'Your SGM details are not correct.',
    ]);
    $response->assertRedirect();

    $freshRequest = $request->fresh();
    expect($freshRequest->status)->toBe(UserSupportRequest::STATUS_REJECTED);
    expect($freshRequest->note)->toBe('Your SGM details are not correct.');
    expect($freshRequest->reviewed_at)->not->toBeNull();
});
