<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public Route
Route::post('/login', [AuthController::class, 'login']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user-profile', function (Request $request) {
        return $request->user();
    });
    
});
