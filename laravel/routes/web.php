<?php

use App\Http\Controllers\Admin\Drops\ListDropsController;
use App\Http\Controllers\Admin\Drops\ShowDropController;
use App\Http\Controllers\Admin\Friendships\ActionFriendshipController;
use App\Http\Controllers\Admin\Friendships\ListFriendshipsController;
use App\Http\Controllers\Admin\Labels\ListLabelsController;
use App\Http\Controllers\Admin\Labels\ShowLabelController;
use App\Http\Controllers\Admin\Labels\UpsertKeywordController;
use App\Http\Controllers\Admin\Labels\UpsertLabelController;
use App\Http\Controllers\Admin\Products\ListProductsController;
use App\Http\Controllers\Admin\Products\ShowProductController;
use App\Http\Controllers\Admin\Products\StatsController;
use App\Http\Controllers\Admin\Stores\ListStoresController;
use App\Http\Controllers\Admin\Stores\ShowStoreController;
use App\Http\Controllers\Admin\StoreWallets\ListStoreWalletsController;
use App\Http\Controllers\Admin\StoreWallets\ShowStoreWalletController;
use App\Http\Controllers\Admin\Users\ListUsersController;
use App\Http\Controllers\Admin\Users\ShowUserController;
use App\Http\Controllers\Admin\Wallets\ListWalletsController;
use App\Http\Controllers\Admin\Wallets\ShowWalletController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
    Route::get('admin/drops', ListDropsController::class)->name('admin.drops.index');
    Route::get('admin/drops/{drop}', [ShowDropController::class, 'show'])->name('admin.drops.show');
    Route::put('admin/drops/{drop}', [ShowDropController::class, 'update'])->name('admin.drops.update');

    Route::get('admin/products', ListProductsController::class)->name('admin.products.index');
    Route::get('admin/products/{product}', [ShowProductController::class, 'show'])->name('admin.products.show');
    Route::get('admin/products/{product}/stats', StatsController::class)->name('admin.products.stats');
    Route::put('admin/products/{product}', [ShowProductController::class, 'update'])->name('admin.products.update');

    Route::get('admin/users', ListUsersController::class)->name('admin.users.index');
    Route::get('admin/users/{user}', [ShowUserController::class, 'show'])->name('admin.users.show');
    Route::put('admin/users/{user}', [ShowUserController::class, 'update'])->name('admin.users.update');

    Route::get('admin/stores', ListStoresController::class)->name('admin.stores.index');
    Route::get('admin/stores/{store}', [ShowStoreController::class, 'show'])->name('admin.stores.show');
    Route::put('admin/stores/{store}', [ShowStoreController::class, 'update'])->name('admin.stores.update');

    // Labels Management
    Route::get('admin/labels', ListLabelsController::class)->name('admin.labels.index');
    Route::post('admin/labels', [UpsertLabelController::class, 'store'])->name('admin.labels.store');
    Route::put('admin/labels/{label}', [UpsertLabelController::class, 'update'])->name('admin.labels.update');
    Route::delete('admin/labels/{label}', [UpsertLabelController::class, 'destroy'])->name('admin.labels.destroy');

    // Label Keywords Management
    Route::get('admin/labels/{label}/keywords', ShowLabelController::class)->name('admin.labels.show');
    Route::post('admin/labels/{label}/keywords', [UpsertKeywordController::class, 'store'])->name('admin.keywords.store');
    Route::put('admin/labels/{label}/keywords/{keyword}', [UpsertKeywordController::class, 'update'])->name('admin.keywords.update');
    Route::delete('admin/labels/{label}/keywords/{keyword}', [UpsertKeywordController::class, 'destroy'])->name('admin.keywords.destroy');

    // Wallets Management
    Route::get('admin/wallets', ListWalletsController::class)->name('admin.wallets.index');
    Route::get('admin/wallets/{wallet}', [ShowWalletController::class, 'show'])->name('admin.wallets.show');
    Route::put('admin/wallets/{wallet}', [ShowWalletController::class, 'update'])->name('admin.wallets.update');
    Route::put('admin/wallets/{wallet}/withdrawals/{withdrawalRequest}', [ShowWalletController::class, 'updateWithdrawal'])->name('admin.wallets.update_withdrawal');

    // Store Wallets Management
    Route::get('admin/store-wallets', ListStoreWalletsController::class)->name('admin.store_wallets.index');
    Route::get('admin/store-wallets/{store_wallet}', [ShowStoreWalletController::class, 'show'])->name('admin.store_wallets.show');
    Route::put('admin/store-wallets/{store_wallet}', [ShowStoreWalletController::class, 'update'])->name('admin.store_wallets.update');
    Route::put('admin/store-wallets/{store_wallet}/withdrawals/{storeWithdrawalRequest}', [ShowStoreWalletController::class, 'updateWithdrawal'])->name('admin.store_wallets.update_withdrawal');

    // Friendships Management
    Route::get('admin/friendships', ListFriendshipsController::class)->name('admin.friendships.index');
    Route::get('admin/friendships/{friendship}', [ActionFriendshipController::class, 'show'])->name('admin.friendships.show');
    Route::put('admin/friendships/{friendship}', [ActionFriendshipController::class, 'update'])->name('admin.friendships.update');
    Route::delete('admin/friendships/{friendship}', [ActionFriendshipController::class, 'destroy'])->name('admin.friendships.destroy');

});

require __DIR__.'/settings.php';
