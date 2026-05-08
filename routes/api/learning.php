<?php

use Illuminate\Support\Facades\Route;

Route::prefix('learning')->middleware('optional-sanctum')->group(function () {});
