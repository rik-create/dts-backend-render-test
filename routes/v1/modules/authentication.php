<?php


use App\Http\Controllers\v1\modules\AuthController;
use Illuminate\Support\Facades\Route;


Route::prefix('auth')->controller(AuthController::class)->group( function () {

    Route::post('/login', 'login');
    Route::post('/refresh', 'refreshToken');
    Route::middleware(['auth:api'])->group(function () {
        Route::post('/logout', 'logout');
    });
});
