<?php

use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    // Game routes will be added when controller is created
    // Route::get('/ohiowordle', [OhioWordleController::class, 'index'])->name('ohiowordle.index');
    // Route::get('/ohiowordle/play', [OhioWordleController::class, 'guestPlay'])->name('ohiowordle.guest');

    // Route::middleware('auth')->group(function () {
    //     Route::get('/ohiowordle/stats', [OhioWordleController::class, 'stats'])->name('ohiowordle.stats');
    // });
});
