<?php

use App\Http\Controllers\v1\modules\OfficeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['jwt.auth'])->prefix('offices')->controller(OfficeController::class)->group(function () {

    // TODO: Refactor authorization to use Granular Group/Module Permissions matrix.

    // Bulk Operations
    Route::post('/activate', 'activateOffices');
    Route::post('/deactivate', 'deactivateOffices');
    Route::delete('/delete', 'deleteOffices');

    // CRUD Operations
    Route::get('/', 'index');
    Route::post('/', 'store');
    Route::get('/{office}', 'show');
    Route::put('/{office}', 'update');
    Route::patch('/{office}', 'update');
    Route::patch('/change-status/{office}', 'changeOfficeStatus');
    Route::delete('/{office}', 'destroy');

});
