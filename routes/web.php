<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('test', function() {
    $user = new User();
    $user->lastname = "f";
    $user->firstname = "fer";
    $user->email = "barbeynicolas.basly@gmail.com";
    $user->password = Hash::make('password');
    $user->save();
});