<?php

use App\Models\Advertisement;
use App\Models\Conversation;
use App\Models\Drop;
use App\Models\Label;
use App\Models\Order;
use App\Models\Prize;
use App\Models\Product;
use App\Models\SavedDrop;
use App\Models\SavedProduct;
use App\Models\SearchHistory;
use App\Models\Store;
use App\Models\User;
use App\Models\Wallet;
use Database\Seeders\DemoDataSeeder;
use Database\Seeders\LookupTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('demo data seeder populates related application data', function () {
    $this->seed(LookupTableSeeder::class);
    $this->seed(DemoDataSeeder::class);

    expect(User::query()->count())->toBe(200)
        ->and(Store::query()->count())->toBeGreaterThan(0)
        ->and(Label::query()->count())->toBeGreaterThan(0)
        ->and(Product::query()->count())->toBe(400)
        ->and(Wallet::query()->count())->toBeGreaterThan(0)
        ->and(Drop::query()->count())->toBe(200)
        ->and(SavedProduct::query()->count())->toBeGreaterThan(0)
        ->and(SavedDrop::query()->count())->toBeGreaterThan(0)
        ->and(Prize::query()->count())->toBeGreaterThan(0)
        ->and(Order::query()->count())->toBeGreaterThan(0)
        ->and(Advertisement::query()->count())->toBeGreaterThan(0)
        ->and(Conversation::query()->count())->toBeGreaterThan(0)
        ->and(SearchHistory::query()->count())->toBeGreaterThan(0);

    expect(Drop::query()->whereHas('products')->count())->toBeGreaterThan(0)
        ->and(Product::query()->whereHas('images')->count())->toBeGreaterThan(0)
        ->and(Store::query()->whereHas('products')->count())->toBeGreaterThan(0);
});
