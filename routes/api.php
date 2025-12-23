<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckApi;

Route::prefix('app')->group(base_path('routes/api_app.php'));

Route::prefix('site')->middleware(CheckApi::class)->group(base_path('routes/api_site.php'));