<?php

use Database\Seeders\LookupTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('lookup table seeder populates the requested catalog data', function () {
    Artisan::call('db:seed', ['--class' => LookupTableSeeder::class, '--no-interaction' => true]);

    expect(DB::table('roles')->orderBy('id')->pluck('code')->all())
        ->toBe(['user-mini', 'user-pro', 'user-pro-max', 'user-air']);

    expect(DB::table('wilayas')->count())->toBe(68)
        ->and(DB::table('wilayas')->where('number', '68')->value('code'))->toBe('ksar_el_boukhari');

    expect(DB::table('payment_methods')->orderBy('id')->pluck('code')->all())
        ->toBe(['cod', 'online'])
        ->and(DB::table('genders')->orderBy('id')->pluck('code')->all())
        ->toBe(['male', 'female', 'kid', 'unisex'])
        ->and(DB::table('qualities')->orderBy('id')->pluck('code')->all())
        ->toBe(['original', 'copy', 'premium_copy'])
        ->and(DB::table('notification_types')->orderBy('id')->pluck('code')->all())
        ->toBe(['sales', 'withdraw', 'tracking_order', 'friend_request', 'followers']);

    $categoryIds = DB::table('categories')->pluck('id', 'code');

    expect(DB::table('categories')->orderBy('id')->pluck('code')->all())
        ->toBe(['wears_in_head', 'upper_body', 'bottom_body', 'wears_in_feet', 'bags', 'outfits', 'accessories'])
        ->and(DB::table('social_platforms')->orderBy('id')->pluck('code')->all())
        ->toBe(['facebook', 'instagram', 'tiktok', 'youtube', 'snapchat', 'x', 'linkedin', 'pinterest', 'telegram', 'whatsapp'])
        ->and(DB::table('sizes')->where('category_id', $categoryIds['wears_in_feet'])->orderBy('id')->pluck('code')->all())
        ->toBe(['36', '37', '38', '39', '40', '41', '42', '43', '44', '45', '46'])
        ->and(DB::table('sizes')->where('category_id', $categoryIds['bags'])->orderBy('id')->pluck('code')->all())
        ->toBe(['ONE_SIZE']);
});
