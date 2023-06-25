<?php

use App\Http\Controllers\ForumController;
use App\Http\Controllers\ThreadController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect('threads');
});

Route::get('/threads', [ThreadController::class, 'index'])->name('thread.index');

Route::get('/forums', [ForumController::class, 'index'])->name('forum.index');
Route::get('/forums/{forum}', [ForumController::class, 'show'])->name('forum.show');
Route::get('/forums/{forum}/{thread}', [ThreadController::class, 'show'])->name('thread.show');
