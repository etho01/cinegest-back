<?php

use App\Http\Controllers\Api\App\Auth\Spa\LoginController;
use App\Http\Controllers\Api\App\Register\RegisterController;
use App\Http\Middleware\IsSuperAdmin;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (){
    Route::post('login', LoginController::class)->middleware('guest');

    Route::post('logout', [LoginController::class, 'logout'])->middleware('auth');
});

Route::prefix('superAdmin')->middleware(['auth:sanctum', IsSuperAdmin::class])->group(function () {
    Route::post('addSuperAdmin', [RegisterController::class, 'registerSuperAdmin']);

    Route::prefix('entity')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\App\SuperAdmin\EntityController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Api\App\SuperAdmin\EntityController::class, 'store']);
        Route::get('/{entity}', [\App\Http\Controllers\Api\App\SuperAdmin\EntityController::class, 'show']);
        Route::put('/{entity}', [\App\Http\Controllers\Api\App\SuperAdmin\EntityController::class, 'update']);
        Route::delete('/{entity}', [\App\Http\Controllers\Api\App\SuperAdmin\EntityController::class, 'destroy']);
    });
});
