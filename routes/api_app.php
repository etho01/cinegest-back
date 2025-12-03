<?php

use App\Http\Controllers\Api\App\Auth\Spa\LoginController;
use App\Http\Controllers\Api\App\Entity\Cinema\KeyController;
use App\Http\Controllers\Api\App\Entity\Cinema\MovieController;
use App\Http\Controllers\Api\App\Entity\Cinema\MovieVersionController;
use App\Http\Controllers\Api\App\Register\RegisterController;
use App\Http\Middleware\HasRight;
use App\Http\Middleware\IsSuperAdmin;
use App\Http\Controllers\Api\App\Entity\UserController;
use App\Http\Controllers\Api\App\Entity\RoleController;
use App\Http\Controllers\Api\App\SuperAdmin\SuperAdminController;
use App\Http\Controllers\Api\App\SuperAdmin\EntityController;
use App\Http\Controllers\Api\App\Entity\CinemaController;
use App\Http\Controllers\Api\App\Entity\Cinema\Settings\OptionsTypeController;
use App\Http\Controllers\Api\App\Entity\Cinema\Settings\OptionsController;
use App\Http\Controllers\Api\App\Entity\Cinema\Settings\RoomController;
use App\Http\Controllers\Api\App\Entity\Cinema\Settings\StorageTypeController;
use App\Http\Controllers\Api\App\Entity\Cinema\Settings\StorageController;
use App\Models\Role\Role;
use Illuminate\Support\Facades\Route;
use Termwind\Components\Raw;

Route::prefix('auth')->group(function (){
    Route::post('login', LoginController::class);

    Route::post('logout', [LoginController::class, 'logout'])->middleware('auth:sanctum');
});

Route::middleware('auth:sanctum')->get('/me', [LoginController::class, 'me']);

Route::prefix('superadmin')->middleware([IsSuperAdmin::class, 'auth:sanctum'])->group(function () {
    Route::prefix('superadmin')->group(function () {
        Route::get('/', [SuperAdminController::class, 'index']);
        Route::post('/', [RegisterController::class, 'registerSuperAdmin']);
    });

    Route::prefix('entity')->group(function () {
        Route::get('/', [EntityController::class, 'index']);
        Route::post('/', [EntityController::class, 'store']);
        Route::get('/{entity}', [EntityController::class, 'show']);
        Route::put('/{entity}', [EntityController::class, 'update']);
        Route::delete('/{entity}', [EntityController::class, 'destroy']);
    });
});

Route::prefix('entity/{entity}')->middleware('auth:sanctum')->group(function () {
    Route::prefix('cinemas')->group(function () {
        Route::get('/', [CinemaController::class, 'index'])->middleware(HasRight::class . ':viewCinemas');
        Route::post('/', [CinemaController::class, 'store'])->middleware(HasRight::class . ':addCinema');
        Route::get('/all', [CinemaController::class, 'all'])->middleware(HasRight::class . ':viewCinemas');
        Route::get('/{cinema}', [CinemaController::class, 'show'])->middleware(HasRight::class . ':viewCinemas');
        Route::put('/{cinema}', [CinemaController::class, 'update'])->middleware(HasRight::class . ':editCinema');
        Route::delete('/{cinema}', [CinemaController::class, 'destroy'])->middleware(HasRight::class . ':deleteCinema');
        Route::prefix('{cinema}')->group(function() {
            Route::prefix('movie')->group(function() {
                Route::get('search', [MovieController::class, 'search']);
                Route::get('/', [MovieController::class, 'index'])->middleware(HasRight::class . ':viewCinemaMovies');
                Route::get('all', [MovieController::class, 'all'])->middleware(HasRight::class . ':viewCinemaMovies');
                Route::post('/', [MovieController::class, 'store'])->middleware(HasRight::class . ':editCinemaMovies');
                Route::get('active/all', [MovieController::class, 'allActive'])->middleware(HasRight::class . ':viewCinemaMovies');
                Route::prefix('{movie}')->group(function() {
                    Route::get('/', [MovieController::class, 'show'])->middleware(HasRight::class . ':viewCinemaMovies');
                    Route::put('/', [MovieController::class, 'update'])->middleware(HasRight::class . ':editCinemaMovies');
                    Route::delete('/', [MovieController::class, 'destroy'])->middleware(HasRight::class . ':editCinemaMovies');
                    Route::prefix('version')->group(function() {
                        Route::post('/', [MovieVersionController::class, 'store'])->middleware(HasRight::class . ':editCinemaMovies');
                        Route::put('/{version}', [MovieVersionController::class, 'update'])->middleware(HasRight::class . ':editCinemaMovies');
                        Route::delete('/{version}', [MovieVersionController::class, 'destroy'])->middleware(HasRight::class . ':editCinemaMovies');
                    });
                });
            });
            Route::prefix('key')->group(function() {
                Route::get('/', [KeyController::class, 'index'])->middleware(HasRight::class . ':viewCinemaKey');
            });

            Route::prefix('settings')->group(function() {
                Route::prefix('option-types')->group(function() {
                    Route::get('/', [OptionsTypeController::class, 'index'])->middleware(HasRight::class . ':viewCinemaSettings');
                    Route::get('all', [OptionsTypeController::class, 'all'])->middleware(HasRight::class . ':viewCinemaSettings');
                    Route::post('/', [OptionsTypeController::class, 'store'])->middleware(HasRight::class . ':editCinemaSettings');
                    Route::put('/{optionsType}', [OptionsTypeController::class, 'update'])->middleware(HasRight::class . ':editCinemaSettings');
                    Route::delete('/{optionsType}', [OptionsTypeController::class, 'destroy'])->middleware(HasRight::class . ':editCinemaSettings');
                });
                Route::prefix('options')->group(function() {
                    Route::get('/', [OptionsController::class, 'index'])->middleware(HasRight::class . ':viewCinemaSettings');
                    Route::get('all', [OptionsController::class, 'all'])->middleware(HasRight::class . ':viewCinemaSettings');
                    Route::post('/', [OptionsController::class, 'store'])->middleware(HasRight::class . ':editCinemaSettings');
                    Route::put('/{option}', [OptionsController::class, 'update'])->middleware(HasRight::class . ':editCinemaSettings');
                    Route::delete('/{option}', [OptionsController::class, 'destroy'])->middleware(HasRight::class . ':editCinemaSettings');
                });
                Route::prefix('storage-type')->group(function() {
                    Route::get('/', [StorageTypeController::class, 'index'])->middleware(HasRight::class . ':viewCinemaSettings');
                    Route::get('all', [StorageTypeController::class, 'all'])->middleware(HasRight::class . ':viewCinemaSettings');
                    Route::post('/', [StorageTypeController::class, 'store'])->middleware(HasRight::class . ':editCinemaSettings');
                    Route::put('/{storageType}', [StorageTypeController::class, 'update'])->middleware(HasRight::class . ':editCinemaSettings');
                    Route::delete('/{storageType}', [StorageTypeController::class, 'destroy'])->middleware(HasRight::class . ':editCinemaSettings');
                });
                Route::prefix('storage')->group(function() {
                    Route::get('/', [StorageController::class, 'index'])->middleware(HasRight::class . ':viewCinemaSettings');
                    Route::get('all', [StorageController::class, 'all'])->middleware(HasRight::class . ':viewCinemaSettings');
                    Route::post('/', [StorageController::class, 'store'])->middleware(HasRight::class . ':editCinemaSettings');
                    Route::put('/{storageType}', [StorageController::class, 'update'])->middleware(HasRight::class . ':editCinemaSettings');
                    Route::delete('/{storageType}', [StorageController::class, 'destroy'])->middleware(HasRight::class . ':editCinemaSettings');
                });
                Route::prefix('room')->group(function() {
                    Route::get('/', [RoomController::class, 'index'])->middleware(HasRight::class . ':viewCinemaSettings');
                    Route::get('all', [RoomController::class, 'all'])->middleware(HasRight::class . ':viewCinemaSettings');
                    Route::post('/', [RoomController::class, 'store'])->middleware(HasRight::class . ':editCinemaSettings');
                    Route::put('/{room}', [RoomController::class, 'update'])->middleware(HasRight::class . ':editCinemaSettings');
                    Route::delete('/{room}', [RoomController::class, 'destroy'])->middleware(HasRight::class . ':editCinemaSettings');
                });
            });
        });
    });

    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->middleware(HasRight::class . ':viewUsers');
        Route::post('/', [RegisterController::class, 'registerUser'])->middleware(HasRight::class . ':addUser');
        Route::prefix('{user}')->group(function() {
            Route::post('roles', [UserController::class, 'setRoles'])->middleware(HasRight::class . ':editUserRoles');
            Route::post('rights', [UserController::class, 'setRights'])->middleware(HasRight::class . ':editUserRights');
            Route::get('/', [UserController::class, 'show'])->middleware(HasRight::class . ':viewUsers');
            Route::put('/', [UserController::class, 'update'])->middleware(HasRight::class . ':editUser');
            Route::delete('/', [UserController::class, 'destroy'])->middleware(HasRight::class . ':deleteUser');
        });
    });

    Route::prefix('roles')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->middleware(HasRight::class . ':viewRoles');
        Route::get('/all', [RoleController::class, 'all'])->middleware(HasRight::class . ':viewRoles');
        Route::get('/{role}', [RoleController::class, 'show'])->middleware(HasRight::class . ':viewRoles');
        Route::post('/', [RoleController::class, 'store'])->middleware(HasRight::class . ':addRole');
        Route::put('/{role}', [RoleController::class, 'update'])->middleware(HasRight::class . ':editRole');
        Route::delete('/{role}', [RoleController::class, 'destroy'])->middleware(HasRight::class . ':deleteRole');
    });
});