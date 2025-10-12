<?php

namespace App\Http\Controllers\Api\App\Register;

use App\Http\Controllers\Controller;
use App\Models\Role\RoleUser;
use App\Models\User;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    public static function userExist(string $email, int $originId) : bool
    {
        return User::where('email', $email)->where('origin_id', $originId)->exists();
    }

    public function registerSuperAdmin(Request $request)
    {
        $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
        ]);

        if (static::userExist($request->email, 0)) {
            return response()->json(['message' => 'User already exists'], 409);
        }
    
        $user = User::create([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'password' => '',
            'type' => 'app',
            'origin_id' => 0,
        ]);

        $userRole = new RoleUser();
        $userRole->user_id = $user->id;
        $userRole->role_id = 1; // Assuming 1 is the ID for superAdmin
        $userRole->save();

        return response()->json(['message' => 'Super admin registered successfully'], 201);
    }
}
