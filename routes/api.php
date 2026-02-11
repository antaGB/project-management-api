<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\PermissionController;

// Public Route
Route::post('/login', [AuthController::class, 'login']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('projects', ProjectController::class);
    Route::apiResource('tasks', TaskController::class);
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus']);
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('users', UserController::class);
    Route::apiResource('permissions', PermissionController::class);
    
    // Assign Role to User
    Route::patch('users/{user}/assign-role', [UserController::class, 'assignRole']);

    
    
});
