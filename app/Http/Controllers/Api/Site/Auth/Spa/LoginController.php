<?php

namespace App\Http\Controllers\Api\Site\Auth\Spa;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\UseCase\Site\Auth\LoginUser;
use App\UseCase\Site\Auth\RegisterUser;
use App\UseCase\Site\Auth\UpdateUserProfile;
use App\UseCase\Site\Auth\UpdateUserPassword;
use App\UseCase\Site\Auth\SendPasswordResetLink;
use App\UseCase\Site\Auth\ResetPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class LoginController extends Controller
{
    private LoginUser $loginUser;
    private RegisterUser $registerUser;
    private UpdateUserProfile $updateUserProfile;
    private UpdateUserPassword $updateUserPassword;
    private SendPasswordResetLink $sendPasswordResetLink;
    private ResetPassword $resetPassword;

    public function __construct(
        LoginUser $loginUser,
        RegisterUser $registerUser,
        UpdateUserProfile $updateUserProfile,
        UpdateUserPassword $updateUserPassword,
        SendPasswordResetLink $sendPasswordResetLink,
        ResetPassword $resetPassword
    ) {
        $this->loginUser = $loginUser;
        $this->registerUser = $registerUser;
        $this->updateUserProfile = $updateUserProfile;
        $this->updateUserPassword = $updateUserPassword;
        $this->sendPasswordResetLink = $sendPasswordResetLink;
        $this->resetPassword = $resetPassword;
    }

    public function me(Request $request)
    {
        return $request->user();
    }

    public function updateMe(Request $request)
    {
        $validated = $request->validate([
            'firstname' => 'sometimes|required|string|max:100',
            'lastname' => 'sometimes|required|string|max:100',
            'email' => 'sometimes|required|email|max:255',
            'phone' => 'sometimes|nullable|string|max:20',
        ]);

        $user = $this->updateUserProfile->handle(Auth::user(), $validated);
        
        return response()->json($user);
    }

    public function updateMyPassword(Request $request)
    {
        $validated = $request->validate([
            'actualPassword' => 'required|string',
            'newPassword' => 'required|string|min:8',
            'newPasswordConfirmation' => 'required|string|same:newPassword',
        ]);

        $result = $this->updateUserPassword->handle(
            Auth::user(),
            $validated['actualPassword'],
            $validated['newPassword']
        );

        if (!$result['success']) {
            return response()->json(['error' => $result['error']], 422);
        }

        return response()->json(['success' => 'PASSWORD_UPDATED']);
    }

    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if ($validator->fails()) {
            return $validator->errors();
        }

        $result = $this->loginUser->handle(
            $request->email,
            $request->password,
            $request->get('cinemaApi')->id
        );

        if (!$result['success']) {
            return ["error" => $result['error']];
        }

        return ["success" => "LOG_IN", 'token' => $result['token']];
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['success' => 'LOG_OUT']);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => ['required', 'string', 'max:100'],
            'lastname' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8'],
            'passwordConfirmation' => 'required|string|same:password',
        ]);

        if ($validator->fails()) {
            return $validator->errors();
        }

        $result = $this->registerUser->handle(
            $request->only(['firstname', 'lastname', 'email', 'phone', 'password']),
            $request->get('cinemaApi')->id
        );

        if (!$result['success']) {
            $validator->errors()->add('email', 'The email has already been taken.');
            return $validator->errors();
        }

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

        $api = $request->get('cinemaApi');

        $result = $this->sendPasswordResetLink->handle(
            $request->email,
            $api->id,
            $api->websiteUrl
        );

        if (!$result['success']) {
            return response()->json([
                'message' => 'Une erreur est survenue lors de l\'envoi de l\'email.'
            ], 500);
        }

        return response()->json([
            'message' => 'Si cette adresse email existe dans notre système, vous recevrez un lien de réinitialisation.'
        ], 200);
    }

    /**
     * Réinitialise le mot de passe avec le token fourni
     */
    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $result = $this->resetPassword->handle(
            $request->email,
            $request->token,
            $request->password
        );

        if (!$result['success']) {
            $statusCode = match($result['error']) {
                'TOKEN_EXPIRED' => 400,
                'INVALID_TOKEN' => 400,
                'USER_NOT_FOUND' => 404,
                default => 500
            };

            $message = match($result['error']) {
                'TOKEN_EXPIRED' => 'Token invalide ou expiré.',
                'INVALID_TOKEN' => 'Token invalide ou expiré.',
                'USER_NOT_FOUND' => 'Utilisateur non trouvé.',
                default => 'Une erreur est survenue.'
            };

            return response()->json(['message' => $message], $statusCode);
        }

        return response()->json([
            'message' => 'Votre mot de passe a été réinitialisé avec succès.'
        ], 200);
    }
}
