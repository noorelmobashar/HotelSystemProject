<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\ManagerController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware('role:admin')->group(function () {
        Route::get('/managers', [ManagerController::class, 'apiIndex']);
        Route::get('/managers/{manager}', [ManagerController::class, 'apiShow']);
        Route::post('/managers', [ManagerController::class, 'apiStore']);
        Route::put('/managers/{manager}', [ManagerController::class, 'apiUpdate']);
        Route::delete('/managers/{manager}', [ManagerController::class, 'apiDestroy']);
    });
});
