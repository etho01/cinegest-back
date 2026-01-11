<?php

namespace App\UseCase\Site\Auth;

use App\Repository\UserRepository;
use Illuminate\Support\Facades\Hash;

class RegisterUser
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Register a new user
     * 
     * @return array{success: bool, user?: \App\Models\User, error?: string}
     */
    public function handle(array $data, int $originId): array
    {
        // Vérifier si l'utilisateur existe déjà
        if ($this->userRepository->websiteUserExists($data['email'], $originId)) {
            return ['success' => false, 'error' => 'EMAIL_ALREADY_TAKEN'];
        }

        $user = $this->userRepository->create([
            'type' => 'website',
            'origin_id' => $originId,
            'firstname' => $data['firstname'],
            'lastname' => $data['lastname'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
        ]);

        return ['success' => true, 'user' => $user];
    }
}
