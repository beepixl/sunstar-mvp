<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes (no authentication required)
Route::prefix('auth')->group(function () {
    // Admin Login
    Route::post('admin/login', [AuthController::class, 'adminLogin']);
    
    // Client Registration & Login
    Route::post('client/register', [AuthController::class, 'clientRegister']);
    Route::post('client/login', [AuthController::class, 'clientLogin']);
    
    // Driver Login
    Route::post('driver/login', [AuthController::class, 'driverLogin']);
});

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Common auth routes
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me', [AuthController::class, 'me']);
    
    // Admin routes
    Route::middleware('ability:admin')->prefix('admin')->group(function () {
        // Admin-specific endpoints will go here
    });
    
    // Client routes
    Route::middleware('ability:client')->prefix('client')->group(function () {
        // Client-specific endpoints will go here
    });
    
    // Driver routes
    Route::middleware('ability:driver')->prefix('driver')->group(function () {
        // Driver-specific endpoints will go here
    });
});

