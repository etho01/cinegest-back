<?php 

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Site\CinemaController;

Route::get('cinemas', [CinemaController::class, 'index']);

Route::get('movie/upcoming', [\App\Http\Controllers\Api\Site\MovieController::class, 'getUpcomingMovies']);
Route::get('movie/weekly', [\App\Http\Controllers\Api\Site\MovieController::class, 'getWeeklyMovies']);

Route::prefix('movie/{movieCacheId}')->group(function () {
    Route::get('sessions', [\App\Http\Controllers\Api\Site\MovieController::class, 'getMovieWithSessions']);
});

Route::get('prices', [\App\Http\Controllers\Api\Site\PriceController::class, 'index']);

Route::prefix('booking')->group(function() {
    Route::post('payment-intent', [\App\Http\Controllers\Api\Site\BookingController::class, 'paymentIntent']);
});

Route::prefix('auth')->group(function() {
    Route::post('login', \App\Http\Controllers\Api\Site\Auth\Spa\LoginController::class);
    Route::post('register', [\App\Http\Controllers\Api\Site\Auth\Spa\LoginController::class, 'register']);
    Route::post('forgot-password', [\App\Http\Controllers\Api\Site\Auth\Spa\LoginController::class, 'sendResetLinkEmail']);
    Route::post('reset-password', [\App\Http\Controllers\Api\Site\Auth\Spa\LoginController::class, 'reset']);
    Route::middleware('auth:sanctum')->group(function() {
        Route::post('logout', [\App\Http\Controllers\Api\Site\Auth\Spa\LoginController::class, 'logout']);
        Route::get('me', [\App\Http\Controllers\Api\Site\Auth\Spa\LoginController::class, 'me']);
        Route::put('me', [\App\Http\Controllers\Api\Site\Auth\Spa\LoginController::class, 'updateMe']);
        Route::put('me/password', [\App\Http\Controllers\Api\Site\Auth\Spa\LoginController::class, 'updateMyPassword']);
    }); 
});