<?php

use App\Http\Controllers\EntryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(['prefix' => 'entries', 'as' => 'entries.'], function () {
    Route::get('/', [EntryController::class, 'index'])
        ->name('index');
    Route::get('/{id}', [EntryController::class, 'show'])
        ->where(['id' => '[0-9]+'])
        ->name('show');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::group(['prefix' => 'entries', 'as' => 'entries.'], function () {
        Route::post('/', [EntryController::class, 'store'])
            ->name('store');
        Route::patch('/{id}', [EntryController::class, 'update'])
            ->where(['id' => '[0-9]+'])
            ->name('update');
        Route::delete('/{id}', [EntryController::class, 'delete'])
            ->where(['id' => '[0-9]+'])
            ->name('delete');
    });
});
