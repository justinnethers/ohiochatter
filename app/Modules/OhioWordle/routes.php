<?php

use App\Modules\OhioWordle\Http\Controllers\OhioWordleController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/wordio', [OhioWordleController::class, 'index'])->name('ohiowordle.index');
    Route::get('/wordio/play', [OhioWordleController::class, 'guestPlay'])->name('ohiowordle.guest');

    Route::middleware('auth')->group(function () {
        Route::get('/wordio/stats', [OhioWordleController::class, 'stats'])->name('ohiowordle.stats');
    });
});
