<?php

namespace App\Infrastructure\Persistence\Eloquent\Repository;

use App\Domain\Repository\SessionRepositoryInterface;
use App\Domain\Entity\Session;
use App\Domain\ValueObject\SessionId;
use App\Domain\ValueObject\MovieId;
use App\Domain\ValueObject\DateRange;
use App\Infrastructure\Persistence\Mapper\SessionMapper;
use App\Models\Session as SessionModel;

class EloquentSessionRepository implements SessionRepositoryInterface
{
    public function findById(SessionId $id): ?Session
    {
        $model = SessionModel::find($id->value());
        
        return $model ? SessionMapper::toDomainEntity($model) : null;
    }

    public function getMovieExternalIdsByDateRange(DateRange $dateRange): array
    {
        return SessionModel::whereBetween('startTime', [$dateRange->start(), $dateRange->end()])
            ->join('movies', 'sessions.movieId', '=', 'movies.id')
            ->distinct()
            ->pluck('movies.externalId')
            ->toArray();
    }

    public function getMovieIdsByDateRange(DateRange $dateRange): array
    {
        $ids = SessionModel::whereBetween('startTime', [$dateRange->start(), $dateRange->end()])
            ->distinct()
            ->pluck('movieId')
            ->toArray();

        return array_map(fn($id) => new MovieId($id), $ids);
    }

    public function getSessionsForMovieInCinemas(string $movieExternalId, array $cinemaIds): array
    {
        $models = SessionModel::join('movies', 'sessions.movieId', '=', 'movies.id')
            ->where('movies.externalId', $movieExternalId)
            ->whereIn('sessions.cinemaId', $cinemaIds)
            ->select('sessions.*')
            ->orderBy('sessions.startTime')
            ->get();

        return $models->map(fn($model) => SessionMapper::toDomainEntity($model))->toArray();
    }
}
