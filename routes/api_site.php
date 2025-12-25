<?php 

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Site\CinemaController;

Route::get('cinemas', [CinemaController::class, 'index']);

Route::get('movie/upcoming', [\App\Http\Controllers\Api\Site\MovieController::class, 'getUpcomingMovies']);
Route::get('movie/weekly', [\App\Http\Controllers\Api\Site\MovieController::class, 'getWeeklyMovies']);