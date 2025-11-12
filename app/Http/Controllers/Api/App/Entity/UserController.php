<?php

namespace App\Http\Controllers\Api\App\Entity;

use App\Http\Controllers\Controller;
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
}
