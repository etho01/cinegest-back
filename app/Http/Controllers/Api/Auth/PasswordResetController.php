<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Mail\ResetPasswordMail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /**
     * Envoie un lien de réinitialisation de mot de passe
     */
    public function sendResetLinkEmail(ForgotPasswordRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Si cette adresse email existe dans notre système, vous recevrez un lien de réinitialisation.'
            ], 200);
        }

        // Générer un token de réinitialisation
        $token = Str::random(64);
        
        // Stocker le token dans la base de données
        \DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'email' => $request->email,
                'token' => Hash::make($token),
                'created_at' => now()
            ]
        );

        // Envoyer l'email
        try {
            Mail::to($user->email)->send(new ResetPasswordMail($user, $token, env('APP_FRONTEND_URL')));
            
            return response()->json([
                'message' => 'Si cette adresse email existe dans notre système, vous recevrez un lien de réinitialisation.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Une erreur est survenue lors de l\'envoi de l\'email.'
            ], 500);
        }
    }

    /**
     * Réinitialise le mot de passe avec le token fourni
     */
    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $tokenData = \DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$tokenData) {
            return response()->json([
                'message' => 'Token invalide ou expiré.'
            ], 400);
        }

        // Vérifier que le token n'est pas expiré (24 heures)
        if (now()->diffInHours($tokenData->created_at) > 24) {
            \DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return response()->json([
                'message' => 'Token expiré. Veuillez faire une nouvelle demande de réinitialisation.'
            ], 400);
        }

        // Vérifier le token
        if (!Hash::check($request->token, $tokenData->token)) {
            return response()->json([
                'message' => 'Token invalide.'
            ], 400);
        }

        // Trouver l'utilisateur et mettre à jour son mot de passe
        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return response()->json([
                'message' => 'Utilisateur non trouvé.'
            ], 404);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        // Supprimer le token utilisé
        \DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json([
            'message' => 'Mot de passe réinitialisé avec succès.'
        ], 200);
    }

    /**
     * Vérifie la validité d'un token de réinitialisation
     */
    public function verifyToken(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email'
        ]);

        $tokenData = \DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$tokenData) {
            return response()->json([
                'valid' => false,
                'message' => 'Token invalide.'
            ], 400);
        }

        // Vérifier que le token n'est pas expiré
        if (now()->diffInHours($tokenData->created_at) > 24) {
            \DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return response()->json([
                'valid' => false,
                'message' => 'Token expiré.'
            ], 400);
        }

        // Vérifier le token
        if (!Hash::check($request->token, $tokenData->token)) {
            return response()->json([
                'valid' => false,
                'message' => 'Token invalide.'
            ], 400);
        }

        return response()->json([
            'valid' => true,
            'message' => 'Token valide.'
        ], 200);
    }
}
