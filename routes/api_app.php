<?php

use App\Http\Controllers\Api\App\Auth\Spa\LoginController;
use App\Http\Controllers\Api\App\Register\RegisterController;
use App\Http\Middleware\HasRight;
use App\Http\Middleware\IsSuperAdmin;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (){
    Route::post('login', LoginController::class);

    Route::post('logout', [LoginController::class, 'logout'])->middleware('auth:sanctum');
});

Route::middleware('auth:sanctum')->get('/me', [LoginController::class, 'me']);

Route::prefix('superadmin')->middleware([IsSuperAdmin::class, 'auth:sanctum'])->group(function () {
    Route::prefix('superadmin')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\App\SuperAdmin\SuperAdminController::class, 'index']);
    });

    Route::prefix('entity')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\App\SuperAdmin\EntityController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Api\App\SuperAdmin\EntityController::class, 'store']);
        Route::get('/{entity}', [\App\Http\Controllers\Api\App\SuperAdmin\EntityController::class, 'show']);
        Route::put('/{entity}', [\App\Http\Controllers\Api\App\SuperAdmin\EntityController::class, 'update']);
        Route::delete('/{entity}', [\App\Http\Controllers\Api\App\SuperAdmin\EntityController::class, 'destroy']);
    });
});

Route::prefix('entity/{entity}')->middleware('auth:sanctum')->group(function () {
    Route::prefix('users')->group(function () {
        Route::post('register', [RegisterController::class, 'registerUser'])->middleware(HasRight::class . ':addUser');
    });
});