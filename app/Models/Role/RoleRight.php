<?php

namespace App\Models\Role;

use Illuminate\Database\Eloquent\Model;

class RoleRight extends Model
{
    public static $RIGHTS = [
        'global' => [
            // user management
            'viewUsers',
            'editUser',
            'addUser',
            'deleteUser',

            // manage cinema
            'viewCinemas',
            'addCinema',
            'editCinema',
            'deleteCinema',
        ],
        'cinema' => [
            // manage movies
            'viewMovies',
            'addMovie',
            'editMovie',
            'deleteMovie',
        ],
    ];
}
