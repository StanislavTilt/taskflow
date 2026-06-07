<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\UsersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Authorization
Route::group([
    'prefix' => 'auth',
    'middleware' => 'throttle:6,1',
], function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

//Users
Route::resource('user', UsersController::class)->except([
    'create', 'store', 'edit', 'index'
])->middleware('auth:sanctum');
