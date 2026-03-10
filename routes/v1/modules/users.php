<?php

use App\Http\Controllers\v1\modules\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['jwt.auth'])
    ->controller(UserController::class)
    ->group(function () {

        // ----- SINGLE USER -----
        Route::prefix('user')->group(function () {
            // TODO: Refactor authorization to use Granular Group/Module Permissions matrix.
            Route::post('/', 'store');
            Route::get('/{user}', 'show');
            Route::put('/{user}', 'update');
            Route::delete('/{user}', 'destroy');
            Route::patch('/change-status/{user}', 'changeUserStatus');
        });

        // ----- BULK USERS -----
        Route::prefix('users')->group(function () {
            Route::get('/', 'index');
            Route::patch('/activate', 'activateUsers');
            Route::patch('/deactivate', 'deactivateUsers');
            Route::delete('/delete', 'deleteUsers');
        });

    });
