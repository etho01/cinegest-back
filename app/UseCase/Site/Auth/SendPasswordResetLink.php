<?php

namespace App\UseCase\Site\Auth;

use App\Repository\UserRepository;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SendPasswordResetLink
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Send password reset link to user
     * 
     * @return array{success: bool, error?: string}
     */
    public function handle(string $email, int $originId, string $websiteUrl): array
    {
        $user = $this->userRepository->findWebsiteUserByEmailAndOrigin($email, $originId);

        if (!$user) {
            // Pour des raisons de sécurité, on retourne toujours un succès
            return ['success' => true];
        }

        // Générer un token de réinitialisation
        $token = Str::random(64);
        
        // Stocker le token dans la base de données
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'email' => $email,
                'token' => Hash::make($token),
                'created_at' => now()
            ]
        );

        // Envoyer l'email
        try {
            Mail::to($user->email)->send(new ResetPasswordMail($user, $token, $websiteUrl));
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'EMAIL_SEND_FAILED'];
        }
    }
}
