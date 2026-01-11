<?php

namespace App\Infrastructure\Persistence\Eloquent\Repository;

use App\Domain\Repository\MovieRepositoryInterface;
use App\Domain\Entity\Movie;
use App\Domain\ValueObject\MovieId;
use App\Infrastructure\Persistence\Mapper\MovieMapper;
use App\Models\Movie as MovieModel;

class EloquentMovieRepository implements MovieRepositoryInterface
{
    public function findById(MovieId $id): ?Movie
    {
        $model = MovieModel::find($id->value());
        
        return $model ? MovieMapper::toDomainEntity($model) : null;
    }

    public function findByExternalId(string $externalId): ?Movie
    {
        $model = MovieModel::where('externalId', $externalId)->first();
        
        return $model ? MovieMapper::toDomainEntity($model) : null;
    }

    public function getActiveMovies(): array
    {
        $models = MovieModel::where('status', 1)->get();
        
        return $models->map(fn($model) => MovieMapper::toDomainEntity($model))->toArray();
    }

    public function getActiveMoviesExcluding(array $excludeIds): array
    {
        $ids = array_map(fn(MovieId $id) => $id->value(), $excludeIds);
        
        $models = MovieModel::where('status', 1)
            ->whereNotIn('id', $ids)
            ->whereNotNull('externalId')
            ->get();
        
        return $models->map(fn($model) => MovieMapper::toDomainEntity($model))->toArray();
    }
}
