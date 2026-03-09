<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    require __DIR__ . '/v1/modules/authentication.php';
    require __DIR__ . '/v1/modules/users.php';
    require __DIR__ . '/v1/modules/offices.php';
});
