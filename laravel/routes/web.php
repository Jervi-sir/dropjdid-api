<?php

use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Auth\RegisterController;
use App\Http\Controllers\Admin\Dashboard\AdminDashboardController;
use App\Http\Controllers\Admin\Drops\ListDropsController;
use App\Http\Controllers\Admin\Drops\ShowDropController;
use App\Http\Controllers\Admin\Friendships\ActionFriendshipController;
use App\Http\Controllers\Admin\Friendships\ListFriendshipsController;
use App\Http\Controllers\Admin\Labels\ListLabelsController;
use App\Http\Controllers\Admin\Labels\ShowLabelController;
use App\Http\Controllers\Admin\Labels\UpsertKeywordController;
use App\Http\Controllers\Admin\Labels\UpsertLabelController;
use App\Http\Controllers\Admin\Labels\UpsertLabelCategoryController;
use App\Http\Controllers\Admin\Products\ListProductsController;
use App\Http\Controllers\Admin\Products\ShowProductController;
use App\Http\Controllers\Admin\Products\StatsController;
use App\Http\Controllers\Admin\Stores\ListStoresController;
use App\Http\Controllers\Admin\Stores\ShowStoreController;
use App\Http\Controllers\Admin\StoreWallets\ListStoreWalletsController;
use App\Http\Controllers\Admin\StoreWallets\ShowStoreWalletController;
use App\Http\Controllers\Admin\Users\ListUsersController;
use App\Http\Controllers\Admin\Users\ShowUserController;
use App\Http\Controllers\Admin\UserSupportRequest\BecomeCreatorController;
use App\Http\Controllers\Admin\UserSupportRequest\BecomeSgmController;
use App\Http\Controllers\Admin\Wallets\ListWalletsController;
use App\Http\Controllers\Admin\Wallets\ShowWalletController;
use App\Http\Controllers\Admin\Prize\ListPrizesController;
use App\Http\Controllers\Admin\Prize\UpsertPrizeController;
use App\Http\Controllers\Admin\Prize\ShowPrizeController;
use App\Http\Controllers\Admin\Prize\PickWinnerController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome', [
    'canRegister' => true,
])->name('home');

Route::middleware(['admin.guest'])->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->name('login.store');
    Route::get('register', [RegisterController::class, 'create'])->name('register');
    Route::post('register', [RegisterController::class, 'store'])->name('register.store');
});

Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

Route::middleware(['admin.auth', 'admin.approved'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
    Route::get('admin/dashboard', AdminDashboardController::class)->name('admin.dashboard');
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

    Route::get('admin/creators/list-to-approve', [BecomeCreatorController::class, 'index'])->name('admin.creators.list_to_approve');
    Route::get('admin/creators/become-creator/{supportRequest}', [BecomeCreatorController::class, 'show'])->name('admin.creators.show');
    Route::post('admin/creators/become-creator/{supportRequest}/approve', [BecomeCreatorController::class, 'approve'])->name('admin.creators.approve');
    Route::post('admin/creators/become-creator/{supportRequest}/reject', [BecomeCreatorController::class, 'reject'])->name('admin.creators.reject');

    Route::get('admin/sgms/list-to-approve', [BecomeSgmController::class, 'index'])->name('admin.sgms.list_to_approve');
    Route::get('admin/sgms/become-sgm/{supportRequest}', [BecomeSgmController::class, 'show'])->name('admin.sgms.show');
    Route::post('admin/sgms/become-sgm/{supportRequest}/approve', [BecomeSgmController::class, 'approve'])->name('admin.sgms.approve');
    Route::post('admin/sgms/become-sgm/{supportRequest}/reject', [BecomeSgmController::class, 'reject'])->name('admin.sgms.reject');

    Route::get('admin/stores', ListStoresController::class)->name('admin.stores.index');
    Route::get('admin/stores/{store}', [ShowStoreController::class, 'show'])->name('admin.stores.show');
    Route::put('admin/stores/{store}', [ShowStoreController::class, 'update'])->name('admin.stores.update');

    // Labels Management
    Route::get('admin/labels', ListLabelsController::class)->name('admin.labels.index');
    Route::post('admin/labels', [UpsertLabelController::class, 'store'])->name('admin.labels.store');
    Route::put('admin/labels/{label}', [UpsertLabelController::class, 'update'])->name('admin.labels.update');
    Route::delete('admin/labels/{label}', [UpsertLabelController::class, 'destroy'])->name('admin.labels.destroy');
    Route::post('admin/label-categories', [UpsertLabelCategoryController::class, 'store'])->name('admin.label_categories.store');
    Route::put('admin/label-categories/{label_category}', [UpsertLabelCategoryController::class, 'update'])->name('admin.label_categories.update');
    Route::delete('admin/label-categories/{label_category}', [UpsertLabelCategoryController::class, 'destroy'])->name('admin.label_categories.destroy');

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

    // Prizes Management
    Route::get('admin/prizes', ListPrizesController::class)->name('admin.prizes.index');
    Route::get('admin/prizes/create', [UpsertPrizeController::class, 'create'])->name('admin.prizes.create');
    Route::post('admin/prizes', [UpsertPrizeController::class, 'store'])->name('admin.prizes.store');
    Route::get('admin/prizes/{prize}/edit', [UpsertPrizeController::class, 'edit'])->name('admin.prizes.edit');
    Route::put('admin/prizes/{prize}', [UpsertPrizeController::class, 'update'])->name('admin.prizes.update');
    Route::get('admin/prizes/{prize}', [ShowPrizeController::class, 'show'])->name('admin.prizes.show');
    Route::get('admin/prizes/{prize}/pick-winner', [PickWinnerController::class, 'raffleView'])->name('admin.prizes.pick_winner_view');
    Route::post('admin/prizes/{prize}/pick-winner', [PickWinnerController::class, 'draw'])->name('admin.prizes.pick_winner');
});

require __DIR__ . '/settings.php';
