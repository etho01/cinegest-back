<?php

namespace App\Http\Controllers\Api\App\Auth\Spa;

use App\Http\Controllers\Controller;
use App\Models\Entity;
use App\Models\UserToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Testing\Fluent\Concerns\Has;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function me(Request $request)
    {
        $user = Auth::user();
        $user->isSuperAdmin = $user->isSuperAdmin();
        $user->entities = $user->getEntityList();
        $user->load('roles');
        foreach ($user->roles as $role) {
            $role->rights = $role->getRights();
        }
        $user->rights = $user->getRights();

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
            'type' => 'app',
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
}
