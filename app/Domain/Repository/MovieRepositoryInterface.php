<?php

namespace App\Domain\Repository;

use App\Domain\Entity\Movie;
use App\Domain\ValueObject\MovieId;

interface MovieRepositoryInterface
{
    /**
     * Find movie by ID
     */
    public function findById(MovieId $id): ?Movie;

    /**
     * Find movie by external ID
     */
    public function findByExternalId(string $externalId): ?Movie;

    /**
     * Get active movies
     * 
     * @return Movie[]
     */
    public function getActiveMovies(): array;

    /**
     * Get active movies not in given IDs
     * 
     * @param MovieId[] $excludeIds
     * @return Movie[]
     */
    public function getActiveMoviesExcluding(array $excludeIds): array;
}
