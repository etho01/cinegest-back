<?php

namespace App\Domain\Repository;

use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\UserId;
use App\Domain\Entity\User;

interface UserRepositoryInterface
{
    /**
     * Find user by ID
     */
    public function findById(UserId $id): ?User;

    /**
     * Find user by email
     */
    public function findByEmail(Email $email): ?User;

    /**
     * Find website user by email and origin
     */
    public function findWebsiteUserByEmailAndOrigin(Email $email, int $originId): ?User;

    /**
     * Check if website user exists
     */
    public function websiteUserExists(Email $email, int $originId): bool;

    /**
     * Save user
     */
    public function save(User $user): User;
}
