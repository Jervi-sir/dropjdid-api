<?php

use App\Models\CreatorFollower;
use App\Models\Drop;
use App\Models\User;
use App\Models\UserInteraction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('people reposted drops returns list of reposted drops', function () {
    $person = User::factory()->create();
    $creator = User::factory()->create(['username' => 'originalcreator']);
    $viewer = User::factory()->create();

    $drop1 = Drop::create([
        'creator_id' => $creator->id,
        'title' => 'First Reposted Drop',
        'description' => 'Drop 1 description',
        'drop_status' => 'published',
    ]);

    $drop2 = Drop::create([
        'creator_id' => $creator->id,
        'title' => 'Second Reposted Drop',
        'description' => 'Drop 2 description',
        'drop_status' => 'published',
    ]);

    // Person reposts drop1 and drop2
    UserInteraction::create([
        'user_id' => $person->id,
        'type' => UserInteraction::TYPE_REPOST,
        'target_type' => UserInteraction::TARGET_DROP,
        'target_id' => $drop1->id,
        'meta' => ['quote' => 'Love this drop!'],
    ]);

    UserInteraction::create([
        'user_id' => $person->id,
        'type' => UserInteraction::TYPE_REPOST,
        'target_type' => UserInteraction::TARGET_DROP,
        'target_id' => $drop2->id,
    ]);

    // Viewer is following creator and has liked drop1
    CreatorFollower::create([
        'creator_id' => $creator->id,
        'user_id' => $viewer->id,
    ]);

    $drop1->likedUsers()->attach($viewer->id);

    Sanctum::actingAs($viewer);

    $response = $this->getJson("/api/people/{$person->id}/reposted-drops");

    $response->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.id', $drop2->id)
        ->assertJsonPath('data.0.text1', 'Second Reposted Drop')
        ->assertJsonPath('data.0.text2', '@originalcreator')
        ->assertJsonPath('data.0.is_following_creator', true)
        ->assertJsonPath('data.1.id', $drop1->id)
        ->assertJsonPath('data.1.text1', 'First Reposted Drop')
        ->assertJsonPath('data.1.quote', 'Love this drop!')
        ->assertJsonPath('data.1.is_liked', true)
        ->assertJsonPath('data.1.is_following_creator', true);
});

test('people reposted drops returns 404 for non-existing user', function () {
    $response = $this->getJson('/api/people/999999/reposted-drops');
    $response->assertNotFound();
});
