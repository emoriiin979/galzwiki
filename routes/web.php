<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/wiki/search', function () {
    return Inertia::render('Wiki/Search');
})->name('wiki.search');

Route::get('/wiki/detail/{pageId}', function (int $pageId) {
    return Inertia::render('Wiki/Detail', [
        'page_id' => $pageId,
    ]);
})->name('wiki.detail');

Route::middleware('auth')->group(function () {
    Route::get('/wiki/edit/{pageId}', function (int $pageId) {
        return Inertia::render('Wiki/Edit', [
            'pageId' => $pageId,
        ]);
    })->name('wiki.edit');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
