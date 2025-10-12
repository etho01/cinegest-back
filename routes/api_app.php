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
});