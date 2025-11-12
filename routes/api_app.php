<?php

use App\Http\Controllers\Api\App\Auth\Spa\LoginController;
use App\Http\Controllers\Api\App\Register\RegisterController;
use App\Http\Middleware\HasRight;
use App\Http\Middleware\IsSuperAdmin;
use App\Models\Role\Role;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (){
    Route::post('login', LoginController::class);

    Route::post('logout', [LoginController::class, 'logout'])->middleware('auth:sanctum');
});

Route::middleware('auth:sanctum')->get('/me', [LoginController::class, 'me']);

Route::prefix('superadmin')->middleware([IsSuperAdmin::class, 'auth:sanctum'])->group(function () {
    Route::prefix('superadmin')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\App\SuperAdmin\SuperAdminController::class, 'index']);
        Route::post('/', [RegisterController::class, 'registerSuperAdmin']);
    });

    Route::prefix('entity')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\App\SuperAdmin\EntityController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Api\App\SuperAdmin\EntityController::class, 'store']);
        Route::get('/{entity}', [\App\Http\Controllers\Api\App\SuperAdmin\EntityController::class, 'show']);
        Route::put('/{entity}', [\App\Http\Controllers\Api\App\SuperAdmin\EntityController::class, 'update']);
        Route::delete('/{entity}', [\App\Http\Controllers\Api\App\SuperAdmin\EntityController::class, 'destroy']);
    });
});

Route::prefix('entity/{entityId}')->middleware('auth:sanctum')->group(function () {
    Route::prefix('cinemas')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\App\Entity\CinemaController::class, 'index'])->middleware(HasRight::class . ':viewCinemas');
        Route::post('/', [\App\Http\Controllers\Api\App\Entity\CinemaController::class, 'store'])->middleware(HasRight::class . ':addCinema');
        Route::get('/all', [\App\Http\Controllers\Api\App\Entity\CinemaController::class, 'all'])->middleware(HasRight::class . ':viewCinemas');
        Route::get('/{cinema}', [\App\Http\Controllers\Api\App\Entity\CinemaController::class, 'show'])->middleware(HasRight::class . ':viewCinemas');
        Route::put('/{cinema}', [\App\Http\Controllers\Api\App\Entity\CinemaController::class, 'update'])->middleware(HasRight::class . ':editCinema');
        Route::delete('/{cinema}', [\App\Http\Controllers\Api\App\Entity\CinemaController::class, 'destroy'])->middleware(HasRight::class . ':deleteCinema');
    });

    Route::prefix('users')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\App\Entity\UserController::class, 'index'])->middleware(HasRight::class . ':viewUsers');
        Route::get('/{user}', [\App\Http\Controllers\Api\App\Entity\UserController::class, 'show'])->middleware(HasRight::class . ':viewUsers');
        Route::post('/', [RegisterController::class, 'registerUser'])->middleware(HasRight::class . ':addUser');
        Route::put('/{user}', [\App\Http\Controllers\Api\App\Entity\UserController::class, 'update'])->middleware(HasRight::class . ':editUser');
        Route::delete('/{user}', [\App\Http\Controllers\Api\App\Entity\UserController::class, 'destroy'])->middleware(HasRight::class . ':deleteUser');
    });

    Route::prefix('roles')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\App\Entity\RoleController::class, 'index'])->middleware(HasRight::class . ':viewRoles');
        Route::get('/all', [\App\Http\Controllers\Api\App\Entity\RoleController::class, 'all'])->middleware(HasRight::class . ':viewRoles');
        Route::get('/{role}', [\App\Http\Controllers\Api\App\Entity\RoleController::class, 'show'])->middleware(HasRight::class . ':viewRoles');
        Route::post('/', [\App\Http\Controllers\Api\App\Entity\RoleController::class, 'store'])->middleware(HasRight::class . ':addRole');
        Route::put('/{role}', [\App\Http\Controllers\Api\App\Entity\RoleController::class, 'update'])->middleware(HasRight::class . ':editRole');
        Route::delete('/{role}', [\App\Http\Controllers\Api\App\Entity\RoleController::class, 'destroy'])->middleware(HasRight::class . ':deleteRole');
    });
});