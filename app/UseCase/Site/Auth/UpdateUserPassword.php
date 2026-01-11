<?php

namespace App\UseCase\Site\Auth;

use App\Repository\UserRepository;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UpdateUserPassword
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Update user password
     * 
     * @return array{success: bool, error?: string}
     */
    public function handle(User $user, string $actualPassword, string $newPassword): array
    {
        if (!Hash::check($actualPassword, $user->password)) {
            return ['success' => false, 'error' => 'CURRENT_PASSWORD_INCORRECT'];
        }

        $this->userRepository->update($user, [
            'password' => Hash::make($newPassword)
        ]);

        return ['success' => true];
    }
}
