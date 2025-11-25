<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\TestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Test route
Route::get('/test', [TestController::class, 'index']);

// Public routes (no authentication required)
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected routes (authentication required)
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
    
    // Attendance routes (for employees)
    Route::prefix('attendance')->group(function () {
        Route::post('/check-zone', [AttendanceController::class, 'checkZone']);
        Route::post('/check-in', [AttendanceController::class, 'checkIn']);
        Route::post('/check-out', [AttendanceController::class, 'checkOut']);
        Route::post('/break-start', [AttendanceController::class, 'breakStart']);
        Route::post('/break-end', [AttendanceController::class, 'breakEnd']);
        Route::get('/today', [AttendanceController::class, 'todayStatus']);
        Route::get('/history', [AttendanceController::class, 'history']);
    });
    
    // Admin routes (admin access only)
    Route::middleware('admin')->prefix('admin')->group(function () {
        
        // Dashboard
        Route::get('/dashboard', [AdminController::class, 'dashboard']);
        
        // Employee management
        Route::prefix('employees')->group(function () {
            Route::get('/', [AdminController::class, 'employees']);
            Route::post('/', [AdminController::class, 'createEmployee']);
            Route::put('/{id}', [AdminController::class, 'updateEmployee']);
            Route::delete('/{id}', [AdminController::class, 'deleteEmployee']);
        });
        
        // Attendance reports
        Route::get('/attendance-reports', [AdminController::class, 'attendanceReports']);
        
        // Office zones management
        Route::prefix('office-zones')->group(function () {
            Route::get('/', [AdminController::class, 'getOfficeZones']);
            Route::post('/', [AdminController::class, 'createOfficeZone']);
            Route::put('/{id}', [AdminController::class, 'updateOfficeZone']);
            Route::delete('/{id}', [AdminController::class, 'deleteOfficeZone']);
        });
        
        // Settings
        Route::prefix('settings')->group(function () {
            Route::get('/', [AdminController::class, 'getSettings']);
            Route::put('/', [AdminController::class, 'updateSettings']);
        });
        
        // Employee registration (admin can register new employees)
        Route::post('/register-employee', [AuthController::class, 'register']);
        
        // Admin profile creation
        Route::post('/create-admin', [AuthController::class, 'createAdmin']);
    });
    
    // Get office zones (for all authenticated users)
    Route::get('/office-zones', [AdminController::class, 'getOfficeZones']);
});