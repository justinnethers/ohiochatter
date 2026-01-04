<?php

use App\Modules\Pickem\Http\Controllers\PickemController;
use App\Modules\Pickem\Http\Controllers\PickemAdminController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->prefix('pickems')->group(function () {
    // Public routes
    Route::get('/', [PickemController::class, 'index'])->name('pickem.index');
    Route::get('/groups/{group:slug}', [PickemController::class, 'group'])->name('pickem.group');

    // Admin routes (must be before wildcard)
    Route::middleware(['auth'])->prefix('admin')->group(function () {
        Route::get('/', [PickemAdminController::class, 'index'])->name('pickem.admin.index');
        Route::get('/groups', [PickemAdminController::class, 'groups'])->name('pickem.admin.groups');
        Route::get('/create', [PickemAdminController::class, 'create'])->name('pickem.admin.create');
        Route::get('/{pickem}/edit', [PickemAdminController::class, 'edit'])->name('pickem.admin.edit');
    });

    // Wildcard route (must be last)
    Route::get('/{pickem:slug}', [PickemController::class, 'show'])->name('pickem.show');
});
