<?php

namespace App\Models\Role;

use Illuminate\Database\Eloquent\Model;

class RoleRight extends Model
{
    public static $RIGHTS = [
        'global' => [
            // user management
            'view_users',
            'edit_users',
            'create_users',
            'delete_users',

            // manage cinema
            'add_cinema',
            'remove_cinema',
            'edit_cinema',
        ],
        'cinema' => [
            
        ],
    ];
}
