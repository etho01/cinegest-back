<?php

namespace App\Infrastructure\Persistence\Mapper;

use App\Domain\Entity\Session as SessionEntity;
use App\Domain\ValueObject\SessionId;
use App\Domain\ValueObject\MovieId;
use App\Models\Session as SessionModel;

class SessionMapper
{
    /**
     * Map Eloquent Model to Domain Entity
     */
    public static function toDomainEntity(SessionModel $model): SessionEntity
    {
        return new SessionEntity(
            new SessionId($model->id),
            new MovieId($model->movieId),
            $model->cinemaId,
            $model->roomId,
            $model->startTime
        );
    }

    /**
     * Map Domain Entity to Eloquent Model attributes
     */
    public static function toEloquentAttributes(SessionEntity $entity): array
    {
        return [
            'id' => $entity->id()->value(),
            'movieId' => $entity->movieId()->value(),
            'cinemaId' => $entity->cinemaId(),
            'roomId' => $entity->roomId(),
            'startTime' => $entity->startTime(),
        ];
    }
}
