<?php

namespace App\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Repository\SessionRepositoryInterface;
use App\Domain\Repository\MovieRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Repository\BookingRepositoryInterface;
use App\Domain\Repository\MovieCacheRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Repository\EloquentSessionRepository;
use App\Infrastructure\Persistence\Eloquent\Repository\EloquentMovieRepository;
use App\Infrastructure\Persistence\Eloquent\Repository\EloquentUserRepository;
use App\Infrastructure\Persistence\Eloquent\Repository\EloquentBookingRepository;
use App\Infrastructure\Persistence\Eloquent\Repository\EloquentMovieCacheRepository;

class DomainServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind Repository Interfaces to Eloquent Implementations
        $this->app->bind(
            SessionRepositoryInterface::class,
            EloquentSessionRepository::class
        );

        $this->app->bind(
            MovieRepositoryInterface::class,
            EloquentMovieRepository::class
        );

        $this->app->bind(
            UserRepositoryInterface::class,
            EloquentUserRepository::class
        );

        $this->app->bind(
            BookingRepositoryInterface::class,
            EloquentBookingRepository::class
        );

        $this->app->bind(
            MovieCacheRepositoryInterface::class,
            EloquentMovieCacheRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function bootstrap(): void
    {
        //
    }
}
