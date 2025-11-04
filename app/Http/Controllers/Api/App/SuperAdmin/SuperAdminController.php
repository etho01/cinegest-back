<?php

namespace App\Http\Controllers\Api\App\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class SuperAdminController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        return User::
            where('type', 'app')
            ->where('origin_id', 0)
            ->where(function ($query) use ($search) {
                $query->where('firstname', 'like', "%{$search}%")
                      ->orWhere('lastname', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
            })
            ->paginate(30);
    }
}
