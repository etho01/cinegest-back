<?php

namespace App\Repository;

use App\Models\MovieCache;
use Illuminate\Database\Eloquent\Collection;

class MovieCacheRepository
{
    /**
     * Find movie cache by ID
     */
    public function find(int $id): ?MovieCache
    {
        return MovieCache::find($id);
    }

    /**
     * Find movie cache by external ID
     */
    public function findByExternalId(string $externalId): ?MovieCache
    {
        return MovieCache::where('externalId', $externalId)->first();
    }

    /**
     * Get movie caches by external IDs
     */
    public function getByExternalIds(Collection $externalIds): Collection
    {
        return MovieCache::whereIn('externalId', $externalIds)->get();
    }

    /**
     * Create or get existing movie cache
     */
    public function createIfNotExist(string $externalId): MovieCache
    {
        return MovieCache::createIfNotExist($externalId);
    }

    /**
     * Create a new movie cache
     */
    public function create(array $data): MovieCache
    {
        return MovieCache::create($data);
    }

    /**
     * Update a movie cache
     */
    public function update(MovieCache $movieCache, array $data): bool
    {
        return $movieCache->update($data);
    }
}
