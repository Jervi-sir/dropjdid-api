<?php

use App\Models\Drop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected to the login page from drops management', function () {
    $response = $this->get(route('admin.drops.index'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the drops management page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('admin.drops.index'));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('admin/drops/list'));
});

test('authenticated users can filter drops by search and status', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    // Create custom drops
    $draftDrop = Drop::factory()->create([
        'title' => 'Vintage Denim Collection',
        'status' => Drop::STATUS_DRAFT,
        'creator_id' => $user->id,
    ]);

    $publishedDrop = Drop::factory()->create([
        'title' => 'Summer Casual Tees',
        'status' => Drop::STATUS_PUBLISHED,
        'creator_id' => $user->id,
    ]);

    // Test status filter
    $response = $this->get(route('admin.drops.index', ['status' => 'draft']));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('drops.data', 1)
        ->where('drops.data.0.id', $draftDrop->id)
    );

    // Test search filter
    $response = $this->get(route('admin.drops.index', ['search' => 'Summer']));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('drops.data', 1)
        ->where('drops.data.0.id', $publishedDrop->id)
    );
});
