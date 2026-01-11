<?php

namespace App\Domain\Repository;

use App\Domain\Entity\Session;
use App\Domain\ValueObject\SessionId;
use App\Domain\ValueObject\MovieId;
use App\Domain\ValueObject\DateRange;

interface SessionRepositoryInterface
{
    /**
     * Find session by ID
     */
    public function findById(SessionId $id): ?Session;

    /**
     * Get movie external IDs from sessions in date range
     * 
     * @return string[]
     */
    public function getMovieExternalIdsByDateRange(DateRange $dateRange): array;

    /**
     * Get movie IDs from sessions in date range
     * 
     * @return MovieId[]
     */
    public function getMovieIdsByDateRange(DateRange $dateRange): array;

    /**
     * Get sessions for a movie in specific cinemas
     * 
     * @param int[] $cinemaIds
     * @return Session[]
     */
    public function getSessionsForMovieInCinemas(string $movieExternalId, array $cinemaIds): array;
}
