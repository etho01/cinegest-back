<?php

namespace App\Http\Controllers\Api\App\Entity;

use App\Http\Controllers\Controller;
use App\Models\Role\RoleUser;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request, Int $entityId)
    {
        $search = $request->input('search');
        return User::where(function ($query) use ($search) {
            if ($search) {
                $query->where('firstname', 'like', "%{$search}%")
                      ->orWhere('lastname', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
            }
        })->where('origin_id', $entityId)->paginate(30);
    }

    public function show(Int $entityId, User $user)
    {
        if ($user->origin_id != $entityId) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->roles = $user->getRoles();
        $user->rights = $user->getRights();

        return response()->json($user);
    }

    public function update(Request $request, Int $entityId, User $user)
    {
        if ($user->origin_id != $entityId) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $validated = $request->validate([
            'firstname' => 'sometimes|required|string|max:100',
            'lastname' => 'sometimes|required|string|max:100',
            'email' => 'sometimes|required|email|max:255',
            'phone' => 'sometimes|nullable|string|max:20',
        ]);

        $user->update($validated);
        return response()->json($user);
    }

    public function destroy(Int $entityId, User $user)
    {
        if ($user->origin_id != $entityId) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }

    public function setRoles(Request $request, Int $entityId, User $user)
    {
        if ($user->origin_id != $entityId) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $validated = $request->validate([
            'rolesUser' => 'array',
            'rolesUser.cinemas' => 'array',
            'rolesUser.cinemas.*' => 'integer|exists:cinemas,id',
            'rolesUser.roles' => 'array',
            'rolesUser.roles.*' => 'integer|exists:roles,id',
        ]);

        RoleUser::where('user_id', $user->id)->delete();
        foreach ($request->input('rolesUser', []) as $rolesUser)
        {
            foreach ($rolesUser['roles'] as $roleId) {
                $cinemas = $rolesUser['cinemas'] ?? [];
                if (empty($cinemas)) {
                    // Assign role without specific cinema
                    RoleUser::create([
                        'user_id' => $user->id,
                        'role_id' => $roleId,
                        'cinema_id' => null,
                    ]);
                } else {
                    // Assign role for each specified cinema
                    foreach ($cinemas as $cinemaId) {
                        RoleUser::create([
                            'user_id' => $user->id,
                            'role_id' => $roleId,
                            'cinema_id' => $cinemaId,
                        ]);
                    }
                }
            }
        }
       
        return response()->json(['message' => 'User roles updated successfully']);
    }

    public function setRights(Request $request, Int $entityId, User $user)
    {
        if ($user->origin_id != $entityId) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $validated = $request->validate([
            'rights' => 'array',
            'rights.*' => 'string|max:100',
        ]);

        // Remove existing rights
        $user->right()->delete();

        // Assign new rights
        foreach ($request->input('rights', []) as $right) {
            $userRight = new \App\Models\UserRight();
            $userRight->user_id = $user->id;
            $userRight->right = $right;
            $userRight->save();
        }

        return response()->json(['message' => 'User rights updated successfully']);
    }
}
