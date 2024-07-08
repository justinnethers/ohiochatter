<?php

use App\Http\Controllers\ForumController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReplyController;
use App\Http\Controllers\ThreadController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('threads');
});

Route::get('/threads', [ThreadController::class, 'index'])->name('thread.index');

Route::get('/forums', [ForumController::class, 'index'])->name('forum.index');
Route::get('/forums/{forum}', [ForumController::class, 'show'])->name('forum.show');
Route::get('/forums/{forum}/{thread}', [ThreadController::class, 'show'])->name('thread.show');

Route::middleware('auth')->group(function () {
    Route::get('/threads/create', [ThreadController::class, 'create']);
    Route::get('/forums/{forum}/threads/create', [ThreadController::class, 'create']);
    Route::post('/threads', [ThreadController::class, 'store']);
    Route::post('/forums/{forum}/{thread}/replies', [ReplyController::class, 'store']);
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
