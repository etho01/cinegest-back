<?php 

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Site\CinemaController;

Route::get('cinemas', [CinemaController::class, 'index']);