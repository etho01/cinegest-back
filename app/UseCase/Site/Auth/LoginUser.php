<?php

namespace App\UseCase\Site\Auth;

use App\Repository\UserRepository;
use Illuminate\Support\Facades\Auth;

class LoginUser
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Authenticate a user
     * 
     * @return array{success: bool, token?: string, error?: string}
     */
    public function handle(string $email, string $password, int $originId): array
    {
        if (Auth::check()) {
            return ['success' => false, 'error' => 'USER_ALREADY_LOG'];
        }

        $credentials = [
            'type' => 'website',
            'origin_id' => $originId,
            'email' => $email,
            'password' => $password
        ];

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('auth-token');

            return ['success' => true, 'token' => $token->plainTextToken];
        }

        return ['success' => false, 'error' => 'BAD_CREDENTIAL'];
    }
}
