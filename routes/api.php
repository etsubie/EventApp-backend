<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserGrowthController;


Route::get('/user', [UserController::class, 'getAuthenticatedUser'])
    ->middleware('auth:sanctum');


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::get('/users', [UserController::class, 'index'])->middleware(['auth:sanctum', 'permission:view events']);
Route::middleware(['auth:sanctum'])->group(function () {
    Route::patch('/users/{user}', [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
});
Route::get('/users/{user}', [UserController::class, 'show'])->middleware('auth:sanctum');
