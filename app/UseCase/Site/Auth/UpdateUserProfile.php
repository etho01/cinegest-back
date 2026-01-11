<?php

namespace App\UseCase\Site\Auth;

use App\Repository\UserRepository;
use App\Models\User;

class UpdateUserProfile
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Update user profile
     */
    public function handle(User $user, array $data): User
    {
        $this->userRepository->update($user, $data);
        
        return $user->fresh();
    }
}
