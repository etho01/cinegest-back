<?php

namespace App\Infrastructure\Persistence\Mapper;

use App\Application\DTO\MovieCacheDTO;
use App\Models\MovieCache as MovieCacheModel;

class MovieCacheMapper
{
    /**
     * Map Eloquent Model to DTO
     */
    public static function toDTO(MovieCacheModel $model): MovieCacheDTO
    {
        return new MovieCacheDTO(
            (string) $model->id,
            $model->externalId,
            $model->title,
            $model->overview,
            $model->posterUrl,
            $model->backdropUrl,
            $model->voteAverage,
            $model->releaseDate,
            $model->runtime,
            $model->director,
            $model->actors ?? [],
            $model->genres ?? []
        );
    }

    /**
     * Map array of models to DTOs
     * 
     * @param MovieCacheModel[] $models
     * @return MovieCacheDTO[]
     */
    public static function toDTOs(array $models): array
    {
        return array_map([self::class, 'toDTO'], $models);
    }
}
