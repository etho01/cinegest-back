<?php

namespace Database\Seeders;

use App\Models\Role\Role;
use App\Models\Role\RoleUser;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Role::where('id', 1)->doesntExist()) {
            Role::create([
                'id' => 1,
                'name' => 'superAdmin',
            ]);
        }

        if (User::where('email', 'barbeynicolas.basly@gmail.com')->doesntExist())
        {
            $user = new User();
            $user->id = 1;
            $user->email = 'barbeynicolas.basly@gmail.com';
            $user->password = Hash::make('password');
            $user->firstname = "Nicolas";
            $user->lastname = "BARBEY";
            $user->type = "app";
            $user->origin_id = 0;
            $user->save();
        }

        if (RoleUser::where('user_id', 1)->where('role_id', 1)->doesntExist())
        {
            $roleUser = new RoleUser();
            $roleUser->user_id = 1;
            $roleUser->role_id = 1;
            $roleUser->cinema_id = null;
            $roleUser->save();
        }
    }
}
