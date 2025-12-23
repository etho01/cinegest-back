<?php

use App\Http\Controllers\Api\App\Auth\Spa\LoginController;
use App\Http\Controllers\Api\App\Entity\Cinema\KeyController;
use App\Http\Controllers\Api\App\Entity\Cinema\MovieController;
use App\Http\Controllers\Api\App\Entity\Cinema\MovieVersionController;
use App\Http\Controllers\Api\App\Entity\Cinema\SessionController;
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
use App\Http\Controllers\Api\App\Entity\Cinema\StorageItemController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (){
    Route::post('login', LoginController::class);

    Route::post('logout', [LoginController::class, 'logout'])->middleware('auth:sanctum');
    
    // Password Reset Routes
    Route::post('forgot-password', [\App\Http\Controllers\Api\Auth\PasswordResetController::class, 'sendResetLinkEmail']);
    Route::post('reset-password', [\App\Http\Controllers\Api\Auth\PasswordResetController::class, 'reset']);
    Route::post('verify-reset-token', [\App\Http\Controllers\Api\Auth\PasswordResetController::class, 'verifyToken']);
});

Route::middleware('auth:sanctum')->get('/me', [LoginController::class, 'me']);
Route::middleware('auth:sanctum')->put('/me', [LoginController::class, 'updateMe']);
Route::middleware('auth:sanctum')->post('/me/password', [LoginController::class, 'updateMyPassword']);

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
            Route::prefix('session')->group(function() {
                Route::get('/', [SessionController::class, 'index'])->middleware(HasRight::class . ':viewCinemaSessions');
                Route::post('/addSessions', [SessionController::class, 'addSessions'])->middleware(HasRight::class . ':editCinemaSessions');
                Route::delete('/{session}', [SessionController::class, 'destroy'])->middleware(HasRight::class . ':editCinemaSessions');
            });

            Route::prefix('movie')->group(function() {
                Route::get('search', [MovieController::class, 'search']);
                Route::get('/', [MovieController::class, 'index'])->middleware(HasRight::class . ':viewCinemaMovies');
                Route::get('all', [MovieController::class, 'all'])->middleware(HasRight::class . ':viewCinemaMovies');
                Route::post('/', [MovieController::class, 'store'])->middleware(HasRight::class . ':editCinemaMovies');
                Route::prefix('version')->group(function() {
                    Route::get('search', [MovieVersionController::class, 'search'])->middleware(HasRight::class . ':viewCinemaMovies');
                });
                Route::get('active/all', [MovieController::class, 'allActive'])->middleware(HasRight::class . ':viewCinemaMovies');
                Route::prefix('{movie}')->group(function() {
                    Route::get('/', [MovieController::class, 'show'])->middleware(HasRight::class . ':viewCinemaMovies');
                    Route::put('/', [MovieController::class, 'update'])->middleware(HasRight::class . ':editCinemaMovies');
                    Route::delete('/', [MovieController::class, 'destroy'])->middleware(HasRight::class . ':editCinemaMovies');
                    Route::put('size', [MovieController::class, 'updateSize'])->middleware(HasRight::class . ':viewCinemaMovies');
                    Route::prefix('version')->group(function() {
                        Route::post('/', [MovieVersionController::class, 'store'])->middleware(HasRight::class . ':editCinemaMovieVersions');
                        
                        Route::put('/{version}', [MovieVersionController::class, 'update'])->middleware(HasRight::class . ':editCinemaMovieVersions');
                        Route::delete('/{version}', [MovieVersionController::class, 'destroy'])->middleware(HasRight::class . ':editCinemaMovieVersions');
                    });
                });
            });

            Route::prefix('key')->group(function() {
                Route::get('/', [KeyController::class, 'index'])->middleware(HasRight::class . ':viewCinemaKey');
                Route::post('/addKeys', [KeyController::class, 'addKeys'])->middleware(HasRight::class . ':editCinemaKey');
                Route::delete('/{key}', [KeyController::class, 'destroy'])->middleware(HasRight::class . ':editCinemaKey');
            });

            Route::prefix('storage-item')->group(function() {
                Route::get('/', [StorageItemController::class, 'index'])->middleware(HasRight::class . ':viewStrorageItems');
                Route::post('/stores', [StorageItemController::class, 'stores'])->middleware(HasRight::class . ':editStorageItems');
                Route::put('/{storageItem}', [StorageItemController::class, 'update'])->middleware(HasRight::class . ':editStorageItems');
                Route::delete('/{storageItem}', [StorageItemController::class, 'destroy'])->middleware(HasRight::class . ':editStorageItems');
            });

            Route::prefix('settings')->group(function() {
                Route::prefix('option-types')->group(function() {
                    Route::get('/', [OptionsTypeController::class, 'index'])->middleware(HasRight::class . ':viewOptionsTypes');
                    Route::get('all', [OptionsTypeController::class, 'all'])->middleware(HasRight::class . ':viewOptionsTypes');
                    Route::post('/', [OptionsTypeController::class, 'store'])->middleware(HasRight::class . ':editOptionsTypes');
                    Route::put('/{optionsType}', [OptionsTypeController::class, 'update'])->middleware(HasRight::class . ':editOptionsTypes');
                    Route::delete('/{optionsType}', [OptionsTypeController::class, 'destroy'])->middleware(HasRight::class . ':editOptionsTypes');
                });
                Route::prefix('options')->group(function() {
                    Route::get('/', [OptionsController::class, 'index'])->middleware(HasRight::class . ':viewOptions');
                    Route::get('all', [OptionsController::class, 'all'])->middleware(HasRight::class . ':viewOptions');
                    Route::post('/', [OptionsController::class, 'store'])->middleware(HasRight::class . ':editOptions');
                    Route::put('/{option}', [OptionsController::class, 'update'])->middleware(HasRight::class . ':editOptions');
                    Route::delete('/{option}', [OptionsController::class, 'destroy'])->middleware(HasRight::class . ':editOptions');
                });
                Route::prefix('storage-type')->group(function() {
                    Route::get('/', [StorageTypeController::class, 'index'])->middleware(HasRight::class . ':viewStorageTypes');
                    Route::get('all', [StorageTypeController::class, 'all'])->middleware(HasRight::class . ':viewStorageTypes');
                    Route::post('/', [StorageTypeController::class, 'store'])->middleware(HasRight::class . ':editStorageTypes');
                    Route::put('/{storageType}', [StorageTypeController::class, 'update'])->middleware(HasRight::class . ':editStorageTypes');
                    Route::delete('/{storageType}', [StorageTypeController::class, 'destroy'])->middleware(HasRight::class . ':editStorageTypes');
                });
                Route::prefix('storage')->group(function() {
                    Route::get('/', [StorageController::class, 'index'])->middleware(HasRight::class . ':viewStorage');
                    Route::get('all', [StorageController::class, 'all'])->middleware(HasRight::class . ':viewStorage');
                    Route::post('/', [StorageController::class, 'store'])->middleware(HasRight::class . ':editStorage');
                    Route::put('/{storageType}', [StorageController::class, 'update'])->middleware(HasRight::class . ':editStorage');
                    Route::delete('/{storageType}', [StorageController::class, 'destroy'])->middleware(HasRight::class . ':editStorage');
                });
                Route::prefix('room')->group(function() {
                    Route::get('/', [RoomController::class, 'index'])->middleware(HasRight::class . ':viewRooms');
                    Route::get('all', [RoomController::class, 'all'])->middleware(HasRight::class . ':viewRooms');
                    Route::post('/', [RoomController::class, 'store'])->middleware(HasRight::class . ':editRooms');
                    Route::put('/{room}', [RoomController::class, 'update'])->middleware(HasRight::class . ':editRooms');
                    Route::delete('/{room}', [RoomController::class, 'destroy'])->middleware(HasRight::class . ':editRooms');
                });
            });
        });
    });

    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->middleware(HasRight::class . ':viewUsers');
        Route::post('/', [RegisterController::class, 'registerUser'])->middleware(HasRight::class . ':addUser');
        Route::prefix('{user}')->group(function() {
            Route::post('roles', [UserController::class, 'setRoles'])->middleware(HasRight::class . ':editUser');
            Route::post('rights', [UserController::class, 'setRights'])->middleware(HasRight::class . ':editUser');
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

    Route::prefix('cinema-api')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\App\Entity\CinemaApiController::class, 'index'])->middleware(HasRight::class . ':viewCinemaApis');
        Route::post('/', [\App\Http\Controllers\Api\App\Entity\CinemaApiController::class, 'store'])->middleware(HasRight::class . ':editCinemaApi');
        Route::put('/{cinemaApi}', [\App\Http\Controllers\Api\App\Entity\CinemaApiController::class, 'update'])->middleware(HasRight::class . ':editCinemaApi');
        Route::delete('/{cinemaApi}', [\App\Http\Controllers\Api\App\Entity\CinemaApiController::class, 'destroy'])->middleware(HasRight::class . ':editCinemaApi');

        Route::prefix('{cinemaApi}')->group(function() {
            Route::get('/', [\App\Http\Controllers\Api\App\Entity\CinemaApiController::class, 'show'])->middleware(HasRight::class . ':viewCinemaApiDetails');
            Route::prefix('price')->group(function() {
                Route::post('/', [\App\Http\Controllers\Api\App\Entity\CinemaApi\PriceController::class, 'store'])->middleware(HasRight::class . ':editCinemaApiPrices');
                Route::put('/{price}', [\App\Http\Controllers\Api\App\Entity\CinemaApi\PriceController::class, 'update'])->middleware(HasRight::class . ':editCinemaApiPrices');
                Route::delete('/{price}', [\App\Http\Controllers\Api\App\Entity\CinemaApi\PriceController::class, 'destroy'])->middleware(HasRight::class . ':editCinemaApiPrices');
            });
        });
    });
});