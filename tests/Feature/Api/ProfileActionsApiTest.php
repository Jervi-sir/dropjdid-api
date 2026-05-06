<?php

use App\Models\Contact;
use App\Models\CreatorFollower;
use App\Models\Friendship;
use App\Models\Role;
use App\Models\SocialPlatform;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function createProfileActionFixture(): array
{
    $role = Role::query()->create(['code' => 'user', 'en' => 'User']);

    $viewer = User::query()->create([
        'role_id' => $role->id,
        'username' => 'viewer',
        'phone_number' => '0770000011',
        'password' => Hash::make('password123'),
    ]);

    $creator = User::query()->create([
        'role_id' => $role->id,
        'username' => 'creator',
        'phone_number' => '0770000012',
        'password' => Hash::make('password123'),
    ]);

    $socialPlatform = SocialPlatform::query()->create([
        'code' => 'instagram',
        'en' => 'Instagram',
    ]);

    Contact::query()->create([
        'user_id' => $creator->id,
        'social_platform_id' => $socialPlatform->id,
        'url' => 'https://instagram.com/creator',
    ]);

    return compact('viewer', 'creator');
}

test('authenticated users can send, accept, reject, and unfriend requests', function () {
    $fixture = createProfileActionFixture();

    $this->actingAs($fixture['viewer'], 'sanctum')
        ->postJson('/api/friends/request/'.$fixture['creator']->id, ['action' => 'send'])
        ->assertOk()
        ->assertJsonPath('data.friend_status', 'requested')
        ->assertJsonPath('data.friend_request.type', 'outgoing');

    $friendship = Friendship::query()->first();
    expect($friendship)->not->toBeNull();

    $this->actingAs($fixture['creator'], 'sanctum')
        ->postJson('/api/friends/request/'.$fixture['viewer']->id, ['action' => 'accept'])
        ->assertOk()
        ->assertJsonPath('data.friend_status', 'friends')
        ->assertJsonPath('data.friend_request', null);

    $this->actingAs($fixture['viewer'], 'sanctum')
        ->postJson('/api/friends/request/'.$fixture['creator']->id, ['action' => 'unfriend'])
        ->assertOk()
        ->assertJsonPath('data.friend_status', 'none')
        ->assertJsonPath('data.friend_request', null);

    $this->actingAs($fixture['viewer'], 'sanctum')
        ->postJson('/api/friends/request/'.$fixture['creator']->id, ['action' => 'send'])
        ->assertOk();

    $this->actingAs($fixture['creator'], 'sanctum')
        ->postJson('/api/friends/request/'.$fixture['viewer']->id, ['action' => 'reject'])
        ->assertOk()
        ->assertJsonPath('data.friend_status', 'none')
        ->assertJsonPath('data.friend_request', null);
});

test('authenticated users can follow and unfollow creators', function () {
    $fixture = createProfileActionFixture();

    $this->actingAs($fixture['viewer'], 'sanctum')
        ->postJson('/api/creators/follow/'.$fixture['creator']->id)
        ->assertOk()
        ->assertJsonPath('is_following', true);

    expect(CreatorFollower::query()->count())->toBe(1);

    $this->actingAs($fixture['viewer'], 'sanctum')
        ->postJson('/api/creators/follow/'.$fixture['creator']->id)
        ->assertOk()
        ->assertJsonPath('is_following', false);

    expect(CreatorFollower::query()->count())->toBe(0);
});
