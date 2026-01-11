<?php

namespace App\Infrastructure\Persistence\Mapper;

use App\Domain\Entity\Movie as MovieEntity;
use App\Domain\ValueObject\MovieId;
use App\Models\Movie as MovieModel;

class MovieMapper
{
    /**
     * Map Eloquent Model to Domain Entity
     */
    public static function toDomainEntity(MovieModel $model): MovieEntity
    {
        return new MovieEntity(
            new MovieId($model->id),
            $model->externalId,
            $model->title,
            $model->status
        );
    }

    /**
     * Map Domain Entity to Eloquent Model attributes
     */
    public static function toEloquentAttributes(MovieEntity $entity): array
    {
        return [
            'id' => $entity->id()->value(),
            'externalId' => $entity->externalId(),
            'title' => $entity->title(),
            'status' => $entity->status(),
        ];
    }
}
