<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

Route::get('/', function () {
    return view('welcome');
});
