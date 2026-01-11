<?php

namespace App\Infrastructure\Persistence\Eloquent\Repository;

use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Entity\User;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\UserId;
use App\Infrastructure\Persistence\Mapper\UserMapper;
use App\Models\User as UserModel;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function findById(UserId $id): ?User
    {
        $model = UserModel::find($id->value());
        
        return $model ? UserMapper::toDomainEntity($model) : null;
    }

    public function findByEmail(Email $email): ?User
    {
        $model = UserModel::where('email', $email->value())->first();
        
        return $model ? UserMapper::toDomainEntity($model) : null;
    }

    public function findWebsiteUserByEmailAndOrigin(Email $email, int $originId): ?User
    {
        $model = UserModel::where('email', $email->value())
            ->where('type', 'website')
            ->where('origin_id', $originId)
            ->first();
        
        return $model ? UserMapper::toDomainEntity($model) : null;
    }

    public function websiteUserExists(Email $email, int $originId): bool
    {
        return UserModel::where('type', 'website')
            ->where('origin_id', $originId)
            ->where('email', $email->value())
            ->exists();
    }

    public function save(User $user): User
    {
        $attributes = UserMapper::toEloquentModel($user);
        
        $model = UserModel::updateOrCreate(
            ['id' => $attributes['id']],
            $attributes
        );
        
        return UserMapper::toDomainEntity($model);
    }
}
