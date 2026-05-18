<?php

use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected to login from admin dashboard', function () {
    $response = $this->get(route('admin.dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can access admin dashboard with complete stats and queues', function () {
    $this->seed(CatalogSeeder::class);

    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('admin.dashboard'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/admin-dashboard')
        ->has('stats')
        ->has('stats.users')
        ->has('stats.stores')
        ->has('stats.products')
        ->has('stats.drops')
        ->has('stats.finances')
        ->has('stats.social')
        ->has('recentPendingStores')
        ->has('recentStoreWithdrawals')
    );
});
