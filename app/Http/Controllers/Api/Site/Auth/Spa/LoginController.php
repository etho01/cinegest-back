<?php

namespace App\Http\Controllers\Api\Site\Auth\Spa;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Mail\ResetPasswordMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\FacadesDB;
use Illuminate\Support\Facades\Mail;

class LoginController extends Controller
{
    public function me(Request $request)
    {
        $user = $request->user();
        return $user;
    }

    public function updateMe(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'firstname' => 'sometimes|required|string|max:100',
            'lastname' => 'sometimes|required|string|max:100',
            'email' => 'sometimes|required|email|max:255',
            'phone' => 'sometimes|nullable|string|max:20',
        ]);

        $user->update($validated);
        return response()->json($user);
    }

    public function updateMyPassword(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'actualPassword' => 'required|string',
            'newPassword' => 'required|string|min:8',
            'newPasswordConfirmation' => 'required|string|same:newPassword',
        ]);

        if (!Hash::check($validated['actualPassword'], $user->password)) {
            return response()->json(['error' => 'CURRENT_PASSWORD_INCORRECT'], 422);
        }

        $user->password = Hash::make($validated['newPassword']);
        $user->save();

        return response()->json(['success' => 'PASSWORD_UPDATED']);
    }

    public function __invoke(Request $request)
    {
        if (Auth::check())
        {
            return ["error" => 'USER_ALREADY_LOG'];
        }
        
        $validator = Validator::make($request->all(),[
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if ($validator->fails())
        {
            return $validator->errors();
        }

        $credentials = [
            'type' => 'website',
            'origin_id' => $request->get('cinemaApi')->id,
            'email' => $request->email,
            'password' => $request->password
        ];

        if (Auth::attempt($credentials)) {

            $token = $request->user()->createToken('auth-token');

            return ["success" => "LOG_IN", 'token' => $token->plainTextToken];
        }

        return ["error" => "BAD_CREDENTIAL"];
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['success' => 'LOG_OUT']);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'firstname' => ['required', 'string', 'max:100'],
            'lastname' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8'],
            'passwordConfirmation' => 'required|string|same:password',
        ]);

        if (User::where('type', 'website')->where('origin_id', $request->get('cinemaApi')->id)->where('email', $request->email)->exists()) {
            $validator->errors()->add('email', 'The email has already been taken.');
        }

        if ($validator->fails())
        {
            return $validator->errors();
        }

        $user = \App\Models\User::create([
            'type' => 'website',
            'origin_id' => $request->get('cinemaApi')->id,
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['success' => 'USER_REGISTERED']);
    }

    /**
     * Envoie un lien de réinitialisation de mot de passe
     */
    public function sendResetLinkEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email'
        ]);
        $user = User::where('email', $request->email)
            ->where('type', 'website')
            ->where('origin_id', $request->get('cinemaApi')->id)
            ->first();

        if (!$user) {
            return response()->json([
                'message' => 'Si cette adresse email existe dans notre système, vous recevrez un lien de réinitialisation.'
            ], 200);
        }

        // Générer un token de réinitialisation
        $token = Str::random(64);
        
        // Stocker le token dans la base de données
        DB::table('password_reset_tokens')->updateOrInsert(
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
        $tokenData = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$tokenData) {
            return response()->json([
                'message' => 'Token invalide ou expiré.'
            ], 400);
        }

        // Vérifier que le token n'est pas expiré (24 heures)
        if (now()->diffInHours($tokenData->created_at) > 24) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
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
        $user = User::where('email', $request->email)
            ->where('type', 'website')
            ->where('origin_id', $request->get('cinemaApi')->id)
            ->first();
        
        if (!$user) {
            return response()->json([
                'message' => 'Utilisateur non trouvé.'
            ], 404);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        // Supprimer le token utilisé
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json([
            'message' => 'Mot de passe réinitialisé avec succès.'
        ], 200);
    }

    public function verifyToken(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email'
        ]);

        $tokenData = DB::table('password_reset_tokens')
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
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
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
