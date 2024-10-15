<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryContoller;
use App\Http\Controllers\EventController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;


Route::get('/user', [UserController::class, 'getAuthenticatedUser'])
    ->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::get('/users', [UserController::class, 'index'])->middleware(['auth:sanctum', 'permission:view users']);
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::group(['middleware' => ['permission:manage events']], function () {
        Route::patch('/users/{user}', [UserController::class, 'update']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);
    });
});
Route::get('/users/{user}', [UserController::class, 'show'])->middleware('auth:sanctum');

Route::get('/events', [EventController::Class, 'index'])->middleware(['auth:sanctum','permission:view events']);
Route::post('/events', [EventController::Class, 'store'])->middleware(['auth:sanctum','permission:create events']);
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::group(['middleware' => ['permission:manage events']], function () {
        Route::delete('/events/{event}', [EventController::class, 'destroy']);
        Route::patch('/events/{event}', [EventController::class, 'update']);
    });
});
Route::get('/events/{event}', [EventController::class, 'show'])->middleware('auth:sanctum');

Route::post('/categories', [CategoryContoller::class, 'store'])->middleware(['auth:sanctum','permission:create events']);