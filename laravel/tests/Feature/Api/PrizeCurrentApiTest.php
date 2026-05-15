<?php

use App\Models\Prize;
use App\Models\PrizeJoining;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function createPrizeFixture(): array
{
    $role = Role::query()->create([
        'code' => 'user',
        'en' => 'User',
    ]);

    $creator = User::query()->create([
        'role_id' => $role->id,
        'username' => 'creator',
        'phone_number' => '0557000001',
        'password' => Hash::make('password123'),
    ]);

    $viewer = User::query()->create([
        'role_id' => $role->id,
        'username' => 'viewer',
        'phone_number' => '0557000002',
        'password' => Hash::make('password123'),
    ]);

    $prize = Prize::query()->create([
        'creator_id' => $creator->id,
        'title' => 'Giveaway on iPhone 17 pro max',
        'image' => 'prizes/iphone.jpg',
        'description' => 'Enter now to win.',
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDays(7),
        'joining_price' => 0,
        'status' => 'active',
    ]);

    PrizeJoining::query()->create([
        'prize_id' => $prize->id,
        'user_id' => $creator->id,
        'amount_paid' => 0,
        'status' => 'joined',
    ]);

    return [
        'creator' => $creator,
        'viewer' => $viewer,
        'prize' => $prize,
    ];
}

test('guests can preview the current active prize', function () {
    $fixture = createPrizeFixture();

    $this->getJson('/api/prizes/current/preview')
        ->assertOk()
        ->assertJsonPath('data.id', $fixture['prize']->id)
        ->assertJsonPath('data.title', 'Giveaway on iPhone 17 pro max')
        ->assertJsonPath('data.joinings_count', 1)
        ->assertJsonPath('data.is_joined', false);
});

test('authenticated users can view the full current prize payload', function () {
    $fixture = createPrizeFixture();

    PrizeJoining::query()->create([
        'prize_id' => $fixture['prize']->id,
        'user_id' => $fixture['viewer']->id,
        'amount_paid' => 0,
        'status' => 'joined',
    ]);

    $this->actingAs($fixture['viewer'], 'sanctum')
        ->getJson('/api/prizes/current')
        ->assertOk()
        ->assertJsonPath('viewer_phone_number', '0557000002')
        ->assertJsonPath('data.id', $fixture['prize']->id)
        ->assertJsonPath('data.is_joined', true)
        ->assertJsonPath('data.current_user_joining.status', 'joined');
});

test('authenticated users can preview joined state for the current prize', function () {
    $fixture = createPrizeFixture();

    PrizeJoining::query()->create([
        'prize_id' => $fixture['prize']->id,
        'user_id' => $fixture['viewer']->id,
        'amount_paid' => 0,
        'status' => 'joined',
    ]);

    $this->actingAs($fixture['viewer'], 'sanctum')
        ->getJson('/api/prizes/current/preview')
        ->assertOk()
        ->assertJsonPath('data.id', $fixture['prize']->id)
        ->assertJsonPath('data.is_joined', true)
        ->assertJsonPath('data.current_user_joining.status', 'joined');
});

test('authenticated users can participate in the current prize', function () {
    $fixture = createPrizeFixture();

    $this->actingAs($fixture['viewer'], 'sanctum')
        ->postJson('/api/prizes/current/participate', [
            'phone_number' => '0661234567',
        ])
        ->assertOk()
        ->assertJsonPath('data.id', $fixture['prize']->id)
        ->assertJsonPath('data.is_joined', true)
        ->assertJsonPath('viewer_phone_number', '0661234567');

    expect(PrizeJoining::query()->where([
        'prize_id' => $fixture['prize']->id,
        'user_id' => $fixture['viewer']->id,
    ])->exists())->toBeTrue()
        ->and($fixture['viewer']->fresh()?->phone_number)->toBe('0661234567');
});
