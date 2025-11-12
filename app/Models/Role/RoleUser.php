<?php

namespace App\Models\Role;

use Illuminate\Database\Eloquent\Model;

class RoleUser extends Model
{
    protected $fillable = [
        'user_id',
        'role_id',
        'cinema_id',
    ];
}
