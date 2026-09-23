<?php

use App\Models\User;

test('unauthenticated users cannot fetch or update username', function () {
    $getResponse = $this->getJson('/api/my-account/username');
    $getResponse->assertStatus(401);

    $postResponse = $this->postJson('/api/my-account/change-username', [
        'username' => 'newusername',
    ]);
    $postResponse->assertStatus(401);
});

test('authenticated users can view their current username', function () {
    $user = User::factory()->create([
        'username' => 'testuser123',
    ]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/my-account/username');

    $response->assertOk()
        ->assertJson([
            'id' => $user->id,
            'username' => 'testuser123',
            'formatted_username' => '@testuser123',
        ]);
});

test('authenticated users can update username via post change-username', function () {
    $user = User::factory()->create([
        'username' => 'initial_user',
    ]);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/my-account/change-username', [
        'username' => 'updated_user',
    ]);

    $response->assertOk()
        ->assertJson([
            'message' => 'Username updated successfully.',
            'username' => 'updated_user',
            'formatted_username' => '@updated_user',
        ]);

    expect($user->fresh()->username)->toBe('updated_user');
});

test('authenticated users can update username via put username', function () {
    $user = User::factory()->create([
        'username' => 'initial_put',
    ]);

    $response = $this->actingAs($user, 'sanctum')->putJson('/api/my-account/username', [
        'username' => 'put_updated',
    ]);

    $response->assertOk()
        ->assertJson([
            'message' => 'Username updated successfully.',
            'username' => 'put_updated',
            'formatted_username' => '@put_updated',
        ]);

    expect($user->fresh()->username)->toBe('put_updated');
});

test('strips leading at sign from username', function () {
    $user = User::factory()->create([
        'username' => 'olduser',
    ]);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/my-account/change-username', [
        'username' => '@cool_handle',
    ]);

    $response->assertOk()
        ->assertJson([
            'username' => 'cool_handle',
            'formatted_username' => '@cool_handle',
        ]);

    expect($user->fresh()->username)->toBe('cool_handle');
});

test('fails validation if username contains invalid characters', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/my-account/change-username', [
        'username' => 'invalid username with spaces!',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['username']);
});

test('fails validation if username is already taken by another user', function () {
    User::factory()->create([
        'username' => 'taken_name',
    ]);

    $user = User::factory()->create([
        'username' => 'my_name',
    ]);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/my-account/change-username', [
        'username' => 'taken_name',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['username']);
});

test('user can save without error if keeping same username', function () {
    $user = User::factory()->create([
        'username' => 'same_name',
    ]);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/my-account/change-username', [
        'username' => 'same_name',
    ]);

    $response->assertOk()
        ->assertJson([
            'username' => 'same_name',
        ]);
});
