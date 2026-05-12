<?php

use App\Http\Controllers\Api\Media\UploadMediaController;
use Illuminate\Support\Facades\Route;

Route::prefix('media')->group(function () {
    Route::post('upload', UploadMediaController::class);
});
