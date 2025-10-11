<?php

use App\Http\Controllers\Api\Auth\Spa\LoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    dd(Auth::user());
})->middleware('auth:sanctum');

Route::prefix('auth/spa')->group(function (){
    Route::post('login', LoginController::class)->middleware('guest');
});