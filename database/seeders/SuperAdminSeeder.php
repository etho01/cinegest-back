<?php

namespace Database\Seeders;

use App\Models\Role\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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
    }
}
