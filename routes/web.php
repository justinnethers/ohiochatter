<?php

use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\CountyController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\ReplyController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ThreadController;
use App\Modules\Messages\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Route;

Route::get('', function () {
    return redirect('threads');
});

Route::get('threads', [ThreadController::class, 'index'])->name('thread.index');

Route::get('forums', function () {
    return redirect('threads');
})->name('forum.index');

Route::get('forums/{forum}', [ForumController::class, 'show'])->name('forum.show');
Route::get('forums/{forum}/{thread}', [ThreadController::class, 'show'])->name('thread.show');

Route::prefix('ohio')->group(function () {
    // Location Routes
    Route::get('/', [RegionController::class, 'index'])->name('ohio.index');

    // Guide Routes
    Route::prefix('guide')->group(function () {
        // Main Guide Pages
        Route::get('/', [ContentController::class, 'index'])->name('guide.index');
        Route::get('/categories', [ContentController::class, 'categories'])->name('guide.categories');
        Route::get('/category/{category:slug}', [ContentController::class, 'category'])->name('guide.category');


        // Individual Guide Pages
        Route::get('/article/{content}', [ContentController::class, 'show'])->name('guide.show');

        // Region-based Guides
        Route::get('/{region}', [ContentController::class, 'region'])->name('guide.region');
        Route::get('/{region}/category/{category:slug}', [ContentController::class, 'regionCategory'])
            ->name('guide.region.category');

        // County-based Guides
        Route::get('/{region}/{county}', [ContentController::class, 'county'])->name('guide.county');
        Route::get('/{region}/{county}/category/{category:slug}', [ContentController::class, 'countyCategory'])
            ->name('guide.county.category');

        // City-based Guides
        Route::get('/{region}/{county}/{city}', [ContentController::class, 'city'])->name('guide.city');
        Route::get('/{region}/{county}/{city}/category/{category:slug}', [ContentController::class, 'cityCategory'])
            ->name('guide.city.category');
    });

    Route::get('/{region}', [RegionController::class, 'show'])->name('region.show');
    Route::get('/{region}/{county}', [CountyController::class, 'show'])->name('county.show');
    Route::get('/{region}/{county}/{city}', [CityController::class, 'show'])->name('city.show');


    // Alternative "Best of" Routes (These could redirect to the guide routes)
    Route::get('/{region}/best/{category:slug}', [ContentController::class, 'regionCategory'])
        ->name('region.best');
    Route::get('/{region}/{county}/best/{category:slug}', [ContentController::class, 'countyCategory'])
        ->name('county.best');
    Route::get('/{region}/{county}/{city}/best/{category:slug}', [ContentController::class, 'cityCategory'])
        ->name('city.best');
});

Route::middleware('auth')->group(function () {
    Route::get('threads/create', [ThreadController::class, 'create']);
    Route::get('forums/{forum}/threads/create', [ThreadController::class, 'create']);
    Route::post('threads', [ThreadController::class, 'store']);
    Route::post('forums/{forum}/{thread}/replies', [ReplyController::class, 'store']);

    Route::post('upload-image', function (Request $request) {
        $upload = request()->file('image');
        $path = $upload->store('images', 'public');

        $response = [
            'success' => true,
            'url' => url('/storage').'/'.$path
        ];

        return response()->json($response);
    });
});

Route::get('dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('profile/{user}', [ProfileController::class, 'show'])->name('profile.show');

Route::middleware('auth')->group(function () {
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::prefix('archive')->group(function () {
    Route::get('', [ArchiveController::class, 'index'])->name('archive.index');
    Route::get('/forum/{forum}', [ArchiveController::class, 'forum']);
    Route::get('/thread/{thread}', [ArchiveController::class, 'thread']);
});

Route::get('search', [SearchController::class, 'show'])->name('search.show');

//Route::get('search', [\App\Http\Controllers\SearchController::class, 'index'])->name('search.index');
//Route::post('search', [\App\Http\Controllers\SearchController::class, 'show'])->name('search.show');
//Route::get('search?search={search}', [\App\Http\Controllers\SearchController::class, 'show'])->name('search.index');

require __DIR__.'/auth.php';

Route::middleware('auth')->group(function () {
    Route::get('messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('messages/create', [MessageController::class, 'create'])->name('messages.create');
    Route::post('messages', [MessageController::class, 'store'])->name('messages.store');
    Route::get('messages/{thread}', [MessageController::class, 'show'])->name('messages.show');
    Route::post('messages/{thread}/reply', [MessageController::class, 'addMessage'])->name('messages.add_message');
});


Route::middleware('auth')->group(function () {
    Route::post('upload-image', function (Request $request) {
        $upload = request()->file('image');
        $path = $upload->store('images', 'public');

        $response = [
            'success' => true,
            'url' => url('/storage').'/'.$path
        ];

        return response()->json($response);
    });
});

Route::get('/privacy', function () {
    return view('privacy');
})->name('privacy');

Route::get('forum/showthread', function () {
    // Grab the entire query string after the "?"
    // For example, if the URL is:
    //    https://ohiochatter.com/forum/showthread.php?48553-2017-OC-Mock-NFL-Draft-Round-1
    // then $queryString will be:
    //    "48553-2017-OC-Mock-NFL-Draft-Round-1"
    $queryString = request()->getQueryString();

    // If no query string is present, just redirect to some default
    if (empty($queryString)) {
        return redirect('/archive', 301);
    }

    // Split at the first dash to separate the thread ID from the rest
    // parts[0] = 48553
    // parts[1] = 2017-OC-Mock-NFL-Draft-Round-1
    $parts = explode('-', $queryString, 2);

    $threadId = $parts[0];
    $titlePart = $parts[1] ?? '';

    // Replace dashes with spaces for a more readable title
    $title = str_replace('-', ' ', $titlePart);

    // Build the target URL, for example:
    //   /archive/48553?title=2017 OC Mock NFL Draft Round 1
    return redirect()->to(
        "/archive/thread/{$threadId}?title=" . urlencode($title),
        301
    );
});

Route::permanentRedirect('/forum', '/forums');
Route::permanentRedirect('/forum/{any}', '/forums/{any}')
    ->where('any', '.*');
