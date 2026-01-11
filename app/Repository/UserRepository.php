<?php

namespace App\Repository;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserRepository
{
    /**
     * Find user by ID
     */
    public function find(int $id): ?User
    {
        return User::find($id);
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Find website user by email and origin
     */
    public function findWebsiteUserByEmailAndOrigin(string $email, int $originId): ?User
    {
        return User::where('email', $email)
            ->where('type', 'website')
            ->where('origin_id', $originId)
            ->first();
    }

    /**
     * Check if website user exists by email and origin
     */
    public function websiteUserExists(string $email, int $originId): bool
    {
        return User::where('type', 'website')
            ->where('origin_id', $originId)
            ->where('email', $email)
            ->exists();
    }

    /**
     * Create a new user
     */
    public function create(array $data): User
    {
        return User::create($data);
    }

    /**
     * Update a user
     */
    public function update(User $user, array $data): bool
    {
        return $user->update($data);
    }

    /**
     * Delete a user
     */
    public function delete(User $user): bool
    {
        return $user->delete();
    }
}
