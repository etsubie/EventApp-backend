<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CategoryContoller;
use App\Http\Controllers\EventApprovalController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserGrowthControlle;
use Illuminate\Support\Facades\Route;


// Public Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/public', [EventController::class, 'events']);
Route::get('/categories', [CategoryContoller::class, 'index']);
Route::get('/events/{event}', [EventController::class, 'show']);

// Authenticated User Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [UserController::class, 'getAuthenticatedUser']);
    Route::get('/overview', [UserGrowthControlle::class, 'getUserGrowthData'])->middleware('permission:manage users');
    
    // User Management
    Route::middleware('permission:view users')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/{user}', [UserController::class, 'show']);
    });
    Route::middleware('permission:manage users')->group(function () {
        Route::patch('/users/{user}', [UserController::class, 'update']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);
    });
    
    // Event Management
    Route::middleware('permission:view events')->group(function () {
        Route::get('/events', [EventController::class, 'index']);
    });
    Route::middleware('permission:create events')->group(function () {
        Route::post('/events', [EventController::class, 'store']);
        Route::post('/categories', [CategoryContoller::class, 'store']);
    });
    Route::middleware('permission:manage events')->group(function () {
        Route::delete('/events/{event}', [EventController::class, 'destroy']);
        Route::patch('/events/{event}', [EventController::class, 'update']);
    });
    Route::middleware('permission:approve events')->group(function () {
        Route::patch('/events/approve/{event}', [EventApprovalController::class, 'approve']);
        Route::patch('/events/reject/{event}', [EventApprovalController::class, 'reject']);
    });
    
    // Booking
    Route::middleware('permission:book events')->group(function () {
        Route::post('/confirm-booking', [BookingController::class, 'confirmBooking']);
        Route::get('/mybooked', [BookingController::class, 'MyBooked']);
    });
    Route::middleware('permission:view booked events')->group(function () {
        Route::get('/booked', [BookingController::class, 'showBooked'])    ;
    });
    
    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
});
