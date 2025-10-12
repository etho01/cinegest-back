<?php

use App\Http\Controllers\Api\App\Auth\Spa\LoginController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (){
    Route::post('login', LoginController::class)->middleware('guest');
});