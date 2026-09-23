<?php

use App\Models\CreatorFollower;
use App\Models\Drop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('drop show returns creator follower count and following status when authenticated', function () {
    $creator = User::factory()->create();
    $follower1 = User::factory()->create();
    $follower2 = User::factory()->create();
    $authUser = User::factory()->create();

    CreatorFollower::create([
        'creator_id' => $creator->id,
        'user_id' => $follower1->id,
    ]);

    CreatorFollower::create([
        'creator_id' => $creator->id,
        'user_id' => $follower2->id,
    ]);

    CreatorFollower::create([
        'creator_id' => $creator->id,
        'user_id' => $authUser->id,
    ]);

    $drop = Drop::create([
        'creator_id' => $creator->id,
        'title' => 'Sample Drop',
        'description' => 'Drop description',
        'drop_status' => 'published',
    ]);

    Sanctum::actingAs($authUser);

    $response = $this->getJson("/api/drops/{$drop->id}");

    $response->assertOk()
        ->assertJsonPath('data.stats.nb_followers', 3)
        ->assertJsonPath('data.stats.nb_follower', 3)
        ->assertJsonPath('data.nb_followers', 3)
        ->assertJsonPath('data.nb_follower', 3)
        ->assertJsonPath('data.is_following_creator', true);
});

test('drop show returns is_following_creator false when user is not following creator', function () {
    $creator = User::factory()->create();
    $authUser = User::factory()->create();

    $drop = Drop::create([
        'creator_id' => $creator->id,
        'title' => 'Sample Drop',
        'description' => 'Drop description',
        'drop_status' => 'published',
    ]);

    Sanctum::actingAs($authUser);

    $response = $this->getJson("/api/drops/{$drop->id}");

    $response->assertOk()
        ->assertJsonPath('data.stats.nb_followers', 0)
        ->assertJsonPath('data.is_following_creator', false);
});
