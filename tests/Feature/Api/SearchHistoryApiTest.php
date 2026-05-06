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
