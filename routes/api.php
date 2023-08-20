<?php

use App\Http\Controllers\BriefController;
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

Route::group(['prefix' => 'briefs', 'as' => 'briefs.'], function () {
    Route::get('/', [BriefController::class, 'index'])
        ->name('index');
    Route::get('/{id}', [BriefController::class, 'show'])
        ->name('show');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::group(['prefix' => 'briefs', 'as' => 'briefs.'], function () {
        Route::post('/', [BriefController::class, 'store'])
            ->name('store');
        Route::put('/{id}', [BriefController::class, 'update'])
            ->name('update');
        Route::delete('/{id}', [BriefController::class, 'delete'])
            ->name('delete');
    });
});
