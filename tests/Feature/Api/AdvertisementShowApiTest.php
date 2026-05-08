<?php

use App\Models\Advertisement;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests can view an active advertisement', function () {
    $advertisement = Advertisement::query()->create([
        'title' => 'Feed ad',
        'description' => 'Full advertisement description',
        'image' => 'ads/1.jpg',
        'url' => 'https://example.com/ad',
        'status' => 'active',
        'sort_order' => 1,
    ]);

    $this->getJson('/api/advertisements/'.$advertisement->id)
        ->assertOk()
        ->assertJsonPath('data.id', $advertisement->id)
        ->assertJsonPath('data.title', 'Feed ad')
        ->assertJsonPath('data.description', 'Full advertisement description')
        ->assertJsonPath('data.image', 'ads/1.jpg')
        ->assertJsonPath('data.url', 'https://example.com/ad');
});

test('inactive advertisements are not shown', function () {
    $advertisement = Advertisement::query()->create([
        'title' => 'Hidden ad',
        'image' => 'ads/hidden.jpg',
        'url' => 'https://example.com/hidden',
        'status' => 'draft',
        'sort_order' => 1,
    ]);

    $this->getJson('/api/advertisements/'.$advertisement->id)
        ->assertNotFound();
});
