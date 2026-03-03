<?php

use App\Http\Controllers\Web\LandingPageController;

Route::middleware(['setData'])->group(function () {
    Route::get('/', [LandingPageController::class, 'index']);
});