<?php

use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReplyController;
use App\Http\Controllers\ThreadController;
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

Route::middleware('auth')->group(function () {
    Route::get('threads/create', [ThreadController::class, 'create']);
    Route::get('forums/{forum}/threads/create', [ThreadController::class, 'create']);
    Route::post('threads', [ThreadController::class, 'store']);
    Route::post('forums/{forum}/{thread}/replies', [ReplyController::class, 'store']);
});

Route::get('dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::prefix('archive')->group(function () {
    Route::get('', [ArchiveController::class, 'index']);
    Route::get('{forum}', [ArchiveController::class, 'forum']);
    Route::get('{forum}/{thread}', [ArchiveController::class, 'thread']);
});

//Route::get('search', [\App\Http\Controllers\SearchController::class, 'index'])->name('search.index');
//Route::post('search', [\App\Http\Controllers\SearchController::class, 'show'])->name('search.show');
//Route::get('search?search={search}', [\App\Http\Controllers\SearchController::class, 'show'])->name('search.index');

require __DIR__.'/auth.php';
