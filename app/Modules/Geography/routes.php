<?php

use App\Models\Content;
use App\Modules\Geography\Http\Controllers\ContentController;
use App\Modules\Geography\Http\Controllers\LocationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Geography Module Routes
|--------------------------------------------------------------------------
|
| These routes handle all geographic content functionality including
| regions, counties, cities, and guide content.
|
| IMPORTANT: Route order matters! More specific routes (those with literal
| segments like "category" or "best") must be registered BEFORE generic
| routes (those with only parameter segments like {region}/{county}).
|
*/

Route::middleware('web')->prefix('ohio')->group(function () {
    // Location Routes - Unified LocationController
    Route::get('/', [LocationController::class, 'index'])->name('ohio.index');

    // Guide Routes
    Route::prefix('guide')->group(function () {
        // Main Guide Pages (static routes first)
        Route::get('/', [ContentController::class, 'index'])->name('guide.index');
        Route::get('/categories', [ContentController::class, 'categories'])->name('guide.categories');
        Route::get('/category/{category:slug}', [ContentController::class, 'category'])->name('guide.category');
        Route::get('/article/{content}', [ContentController::class, 'show'])->name('guide.show');

        // User Guide Creation & Editing (requires auth)
        Route::middleware(['auth', 'admin'])->group(function () {
            Route::get('/create', fn() => view('guides.create'))->name('guide.create');
            Route::get('/my-guides', fn() => view('guides.my-guides'))->name('guide.my-guides');
            Route::get('/drafts', fn() => redirect()->route('guide.my-guides')); // Redirect old URL
            Route::get('/edit/{draft}', fn(int $draft) => view('guides.create', ['draft' => $draft]))->name('guide.edit');

            // Edit published content (author or admin only)
            Route::get('/article/{content}/edit', fn(Content $content) => view('guides.edit-content', ['content' => $content]))
                ->name('guide.edit-content')
                ->middleware('can:update,content');
        });

        // Category routes - MUST come BEFORE generic location routes
        // Disable scoped bindings since categories are not related to locations
        Route::scopeBindings(false)->group(function () {
            Route::get('/{region}/category/{category:slug}', [ContentController::class, 'regionCategory'])
                ->name('guide.region.category');
            Route::get('/{region}/{county}/category/{category:slug}', [ContentController::class, 'countyCategory'])
                ->name('guide.county.category');
            Route::get('/{region}/{county}/{city}/category/{category:slug}', [ContentController::class, 'cityCategory'])
                ->name('guide.city.category');
        });

        // Generic location routes - MUST come AFTER category routes
        Route::get('/{region}', [ContentController::class, 'region'])->name('guide.region');
        Route::get('/{region}/{county}', [ContentController::class, 'county'])->name('guide.county');
        Route::get('/{region}/{county}/{city}', [ContentController::class, 'city'])->name('guide.city');
    });

    // "Best of" Routes - MUST come BEFORE generic location routes
    // Disable scoped bindings since categories are not related to locations
    Route::scopeBindings(false)->group(function () {
        Route::get('/{region}/best/{category:slug}', [ContentController::class, 'regionCategory'])
            ->name('region.best');
        Route::get('/{region}/{county}/best/{category:slug}', [ContentController::class, 'countyCategory'])
            ->name('county.best');
        Route::get('/{region}/{county}/{city}/best/{category:slug}', [ContentController::class, 'cityCategory'])
            ->name('city.best');
    });

    // Generic Location Routes - MUST come AFTER specific routes
    Route::get('/{region}', [LocationController::class, 'showRegion'])->name('region.show');
    Route::get('/{region}/{county}', [LocationController::class, 'showCounty'])->name('county.show');
    Route::get('/{region}/{county}/{city}', [LocationController::class, 'showCity'])->name('city.show');
});
