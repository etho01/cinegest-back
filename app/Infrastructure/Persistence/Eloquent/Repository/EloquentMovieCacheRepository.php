<?php

namespace App\Infrastructure\Persistence\Eloquent\Repository;

use App\Domain\Repository\MovieCacheRepositoryInterface;
use App\Models\MovieCache;

class EloquentMovieCacheRepository implements MovieCacheRepositoryInterface
{
    public function findById(string $id): ?array
    {
        $model = MovieCache::find($id);
        
        return $model ? $model->toArray() : null;
    }

    public function getByExternalIds(array $externalIds): array
    {
        $models = MovieCache::whereIn('externalId', $externalIds)->get();
        
        return $models->map(fn($model) => $model->toArray())->toArray();
    }

    public function createIfNotExist(string $externalId): array
    {
        $model = MovieCache::createIfNotExist($externalId);
        
        return $model->toArray();
    }
}
