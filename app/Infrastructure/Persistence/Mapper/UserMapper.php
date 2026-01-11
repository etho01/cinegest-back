<?php

namespace App\Infrastructure\Persistence\Mapper;

use App\Domain\Entity\User as UserEntity;
use App\Domain\ValueObject\UserId;
use App\Domain\ValueObject\Email;
use App\Models\User as UserModel;

class UserMapper
{
    /**
     * Map Eloquent Model to Domain Entity
     */
    public static function toDomainEntity(UserModel $model): UserEntity
    {
        return new UserEntity(
            new UserId($model->id),
            new Email($model->email),
            $model->firstname,
            $model->lastname,
            $model->phone,
            $model->type,
            $model->origin_id
        );
    }

    /**
     * Map Domain Entity to Eloquent Model (for persistence)
     */
    public static function toEloquentModel(UserEntity $entity): array
    {
        return [
            'id' => $entity->id()->value(),
            'email' => $entity->email()->value(),
            'firstname' => $entity->firstname(),
            'lastname' => $entity->lastname(),
            'phone' => $entity->phone(),
            'type' => $entity->type(),
            'origin_id' => $entity->originId(),
        ];
    }
}
