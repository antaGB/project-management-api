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

Route::apiResource('tasks', TaskController::class);
// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('projects', ProjectController::class);
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus']);
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('users', UserController::class)->middleware('permission:manage-users');
    Route::apiResource('permissions', PermissionController::class)->middleware('permission:manage-permissions');
    
    // Assign Permission to Role
    Route::post('roles/{role}/permissions', [RoleController::class, 'givePermission']);
    
    // Assign Role to User
    Route::post('users/{user}/assign-role', [UserController::class, 'assignRole']);

    
    
});
