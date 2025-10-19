<?php

namespace App\Http\Controllers\Api\App\Auth\Spa;

use App\Http\Controllers\Controller;
use App\Models\UserToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function me(Request $request)
    {
        return Auth::user();
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
