<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

abstract class Controller
{
    // [Standardize] API JSON responses to maintain a consistent structure across the application.

    protected function handleResponse(
        bool $success,
        string $message,
        $data = null,
        array $errors = [],
        int $code = 200
    ): JsonResponse {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data'    => $data,
            'errors'  => empty($errors) ? [] : $errors, // Ensures it's always an array in JSON, even if an empty collection is passed
        ], $code);
    }
}
