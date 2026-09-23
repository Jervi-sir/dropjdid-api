<?php

use App\Http\Controllers\Api\People\CreatorDropsController;
use App\Http\Controllers\Api\People\RepostedDropsController;
use App\Http\Controllers\Api\People\RepostedProductsController;
use App\Http\Controllers\Api\People\ShowController;
use Illuminate\Support\Facades\Route;

Route::prefix('people')->group(function () {
    Route::get('/{id}', [ShowController::class, 'show'])->name('api.people.show');
    Route::get('/{id}/contacts', [ShowController::class, 'contacts'])->name('api.people.contacts');
    Route::get('/{id}/drops', [CreatorDropsController::class, 'index'])->name('api.people.drops');
    Route::get('/{id}/reposted-drops', [RepostedDropsController::class, 'index'])->name('api.people.reposted_drops');
    Route::get('/{id}/reposts', [RepostedDropsController::class, 'index'])->name('api.people.reposts');
    Route::get('/{id}/reposted-products', [RepostedProductsController::class, 'index'])->name('api.people.reposted_products');
    Route::get('/{id}/products/reposted', [RepostedProductsController::class, 'index'])->name('api.people.products.reposted');
});
