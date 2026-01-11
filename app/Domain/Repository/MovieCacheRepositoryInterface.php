<?php

namespace App\Domain\Repository;

interface MovieCacheRepositoryInterface
{
    /**
     * Find movie cache by ID
     */
    public function findById(string $id): ?array;

    /**
     * Get movie caches by external IDs
     * 
     * @param string[] $externalIds
     * @return array[]
     */
    public function getByExternalIds(array $externalIds): array;

    /**
     * Create or get existing movie cache
     */
    public function createIfNotExist(string $externalId): array;
}
