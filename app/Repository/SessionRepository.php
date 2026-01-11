<?php

namespace App\Repository;

use App\Models\Session;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

class SessionRepository
{
    /**
     * Find session by ID
     */
    public function find(int $id): ?Session
    {
        return Session::find($id);
    }

    /**
     * Find session with relationships
     */
    public function findWithRelations(int $id, array $relations = []): ?Session
    {
        return Session::with($relations)->find($id);
    }

    /**
     * Get sessions between dates
     */
    public function getSessionsBetweenDates(Carbon $startDate, Carbon $endDate): Collection
    {
        return Session::whereBetween('startTime', [$startDate, $endDate])->get();
    }

    /**
     * Get movie external IDs from sessions between dates
     */
    public function getMovieExternalIdsBetweenDates(Carbon $startDate, Carbon $endDate): Collection
    {
        return Session::whereBetween('startTime', [$startDate, $endDate])
            ->join('movies', 'sessions.movieId', '=', 'movies.id')
            ->distinct()
            ->pluck('movies.externalId');
    }

    /**
     * Get movie IDs from sessions between dates
     */
    public function getMovieIdsBetweenDates(Carbon $startDate, Carbon $endDate): Collection
    {
        return Session::whereBetween('startTime', [$startDate, $endDate])
            ->distinct()
            ->pluck('movieId');
    }

    /**
     * Get sessions for a movie in specific cinemas
     */
    public function getSessionsForMovieInCinemas(int $movieId, array $cinemaIds): Collection
    {
        return Session::where('movieId', $movieId)
            ->whereIn('cinemaId', $cinemaIds)
            ->with(['cinema', 'room', 'movieVersion'])
            ->orderBy('startTime')
            ->get();
    }
}
