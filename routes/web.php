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

Route::redirect('/', '/wiki/search');

Route::group(['prefix' => '/wiki', 'as' => 'wiki.'], function () {
    Route::get('/search', function () {
        return Inertia::render('Wiki/Search');
    })->name('search');

    Route::get('/detail', function () {
        return Inertia::render('Wiki/Detail');
    })->name('detail');

    Route::middleware(['auth'])->group(function () {
        Route::get('/edit', function () {
            return Inertia::render('Wiki/Edit');
        })->name('edit');
    });
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::group(['prefix' => '/profile', 'as' => 'profile.'], function () {
    Route::middleware(['auth'])->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });
});

require __DIR__.'/auth.php';
