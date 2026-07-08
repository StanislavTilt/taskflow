<?php

use App\Http\Controllers\Api\Auth\AuthController;
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

Route::middleware(['auth:sanctum'])->group(function () {
    //Users
    Route::resource('user', \App\Http\Controllers\Api\UsersController::class)->except([
        'create', 'store', 'edit', 'index', 'destroy'
    ]);

    //Projects
    Route::resource('project', \App\Http\Controllers\Api\ProjectController::class)->except([
        'create', 'edit'
    ]);

    //Tasks
    Route::group(['prefix' => 'tasks'], function () {
        Route::post('import/{project}', [\App\Http\Controllers\Api\TaskController::class, 'uploadTasksFromCsv']);
    });
});

