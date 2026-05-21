<?php

use App\Http\Controllers\Api\Saves\SaveLabelController;
use Illuminate\Support\Facades\Route;

Route::prefix('labels')->middleware('auth:sanctum')->group(function () {
    Route::post('{label_id}/like', SaveLabelController::class);
});
