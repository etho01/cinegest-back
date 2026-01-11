<?php

namespace App\UseCase\Site\Auth;

use App\Repository\UserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ResetPassword
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Reset user password with token
     * 
     * @return array{success: bool, error?: string}
     */
    public function handle(string $email, string $token, string $newPassword): array
    {
        $tokenData = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$tokenData) {
            return ['success' => false, 'error' => 'INVALID_TOKEN'];
        }

        // Vérifier que le token n'est pas expiré (24 heures)
        if (now()->diffInHours($tokenData->created_at) > 24) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return ['success' => false, 'error' => 'TOKEN_EXPIRED'];
        }

        // Vérifier le token
        if (!Hash::check($token, $tokenData->token)) {
            return ['success' => false, 'error' => 'INVALID_TOKEN'];
        }

        // Mettre à jour le mot de passe
        $user = $this->userRepository->findByEmail($email);
        
        if (!$user) {
            return ['success' => false, 'error' => 'USER_NOT_FOUND'];
        }

        $this->userRepository->update($user, [
            'password' => Hash::make($newPassword)
        ]);

        // Supprimer le token utilisé
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        return ['success' => true];
    }
}
