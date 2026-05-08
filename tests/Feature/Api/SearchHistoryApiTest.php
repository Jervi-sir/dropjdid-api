<?php

use App\Models\Keyword;
use App\Models\Label;
use App\Models\Role;
use App\Models\SearchHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('authenticated users can fetch paginated search history', function () {
    $role = Role::query()->create(['code' => 'user', 'en' => 'User']);

    $user = User::query()->create([
        'role_id' => $role->id,
        'username' => 'viewer',
        'phone_number' => '0110000001',
        'password' => Hash::make('password123'),
    ]);

    SearchHistory::query()->create(['user_id' => $user->id, 'query' => 'sabata', 'type' => 'general']);
    SearchHistory::query()->create(['user_id' => $user->id, 'query' => 'shirts', 'type' => 'general']);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/search/history?per_page=1')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.query', 'sabata')
        ->assertJsonPath('next_page', 2);
});

test('authenticated users can save a search query to history', function () {
    $role = Role::query()->create(['code' => 'user', 'en' => 'User']);

    $user = User::query()->create([
        'role_id' => $role->id,
        'username' => 'history_saver',
        'phone_number' => '0110000005',
        'password' => Hash::make('password123'),
    ]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/search/history', [
            'query' => 'sabata',
            'type' => 'general',
        ])
        ->assertOk()
        ->assertJsonPath('data.query', 'sabata')
        ->assertJsonPath('data.type', 'general');

    expect(SearchHistory::query()->where('user_id', $user->id)->count())->toBe(1)
        ->and(SearchHistory::query()->where('user_id', $user->id)->first()?->query)->toBe('sabata');
});

test('saving an existing search query refreshes its recency instead of duplicating it', function () {
    $role = Role::query()->create(['code' => 'user', 'en' => 'User']);

    $user = User::query()->create([
        'role_id' => $role->id,
        'username' => 'history_refresh',
        'phone_number' => '0110000006',
        'password' => Hash::make('password123'),
    ]);

    $history = SearchHistory::query()->create([
        'user_id' => $user->id,
        'query' => 'sabata',
        'type' => 'general',
    ]);

    $originalUpdatedAt = $history->updated_at;

    sleep(1);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/search/history', [
            'query' => 'sabata',
            'type' => 'general',
        ])
        ->assertOk();

    $history->refresh();

    expect(SearchHistory::query()->where('user_id', $user->id)->count())->toBe(1)
        ->and($history->updated_at?->greaterThan($originalUpdatedAt))->toBeTrue();
});

test('authenticated users can delete a single search history entry', function () {
    $role = Role::query()->create(['code' => 'user', 'en' => 'User']);

    $user = User::query()->create([
        'role_id' => $role->id,
        'username' => 'history_owner',
        'phone_number' => '0110000003',
        'password' => Hash::make('password123'),
    ]);

    $history = SearchHistory::query()->create([
        'user_id' => $user->id,
        'query' => 'sabata',
        'type' => 'general',
    ]);

    $this->actingAs($user, 'sanctum')
        ->deleteJson('/api/search/history/'.$history->id)
        ->assertOk()
        ->assertJsonPath('message', 'Search history entry deleted successfully.');

    expect(SearchHistory::query()->find($history->id))->toBeNull();
});

test('authenticated users can clear all of their search history', function () {
    $role = Role::query()->create(['code' => 'user', 'en' => 'User']);

    $user = User::query()->create([
        'role_id' => $role->id,
        'username' => 'history_cleaner',
        'phone_number' => '0110000004',
        'password' => Hash::make('password123'),
    ]);

    SearchHistory::query()->create(['user_id' => $user->id, 'query' => 'sabata', 'type' => 'general']);
    SearchHistory::query()->create(['user_id' => $user->id, 'query' => 'shirts', 'type' => 'general']);

    $this->actingAs($user, 'sanctum')
        ->deleteJson('/api/search/history')
        ->assertOk()
        ->assertJsonPath('message', 'Search history cleared successfully.');

    expect(SearchHistory::query()->where('user_id', $user->id)->count())->toBe(0);
});

test('clients can fetch keyword label and profile suggestions', function () {
    $role = Role::query()->create(['code' => 'user', 'en' => 'User']);

    User::query()->create([
        'role_id' => $role->id,
        'username' => 'sabata_girl',
        'phone_number' => '0110000002',
        'password' => Hash::make('password123'),
    ]);

    $label = Label::query()->create([
        'code' => 'sabata',
        'en' => 'Sabata',
        'fr' => 'Sabata',
        'ar' => 'Sabata',
    ]);

    Keyword::query()->create([
        'label_id' => $label->id,
        'code' => 'sabata classic',
    ]);

    $response = $this->getJson('/api/search/suggestions?query=sabata');

    $response
        ->assertOk()
        ->assertJsonPath('data.0.type', 'keyword');

    expect(collect($response->json('data'))->pluck('type')->all())
        ->toContain('keyword')
        ->toContain('label')
        ->toContain('profile');
});
