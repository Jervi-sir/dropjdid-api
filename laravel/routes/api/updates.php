<?php

use App\Http\Controllers\Api\Updates\CheckUpdateController;
use Illuminate\Support\Facades\Route;

Route::post('check-update', CheckUpdateController::class);
