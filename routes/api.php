<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BookController;

Route::apiResource('books', BookController::class);

Route::get('/ping', function () {
    return response()->json([
        'message' => 'pong',
    ]);
});
