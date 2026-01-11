<?php

use App\Modules\BuckEYE\Http\Controllers\BuckEyeGameController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    // Main game routes
    Route::get('/buckEYE', [BuckEyeGameController::class, 'index'])
        ->name('buckeye.index');

    // Guest play route (no authentication required)
    Route::get('/buckEYE/play', [BuckEyeGameController::class, 'guestPlay'])
        ->name('buckeye.guest');

    // Stats page (authenticated users only)
    Route::middleware(['auth'])->group(function () {
        Route::get('/buckEYE/stats', [BuckEyeGameController::class, 'stats'])
            ->name('buckeye.stats');
    });

    // Social image route
    Route::get('/buckEYE/social-image/{date}.jpg', [BuckEyeGameController::class, 'socialImage'])
        ->name('buckeye.social-image');

    // Redirects for lowercase URLs
    Route::redirect('buckeye', '/buckEYE');
    Route::redirect('buckeye/stats', '/buckEYE/stats');
    Route::redirect('buckeye/play', '/buckEYE/play');
});
