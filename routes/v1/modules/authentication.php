<?php


use App\Http\Controllers\v1\modules\AuthController;
use Illuminate\Support\Facades\Route;


Route::prefix('auth')->controller(AuthController::class)->group( function () {

    Route::post('/login', 'login')->middleware('throttle:5,1');
    Route::post('/refresh', 'refreshToken');
    Route::post('/forgot-password', 'forgotPassword')->middleware('throttle:6,1');
    Route::post('/reset-password', 'resetPassword')->middleware('throttle:6,1');

    Route::middleware(['jwt.auth'])->group(function () {
        Route::post('/logout', 'logout');
    });
});
