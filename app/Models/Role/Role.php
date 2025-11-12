<?php

namespace App\Models\Role;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public function syncRights(array $rights)
    {
        RoleRight::where('role_id', $this->id)->delete();
        foreach ($rights as $right) {
            $roleRight = new RoleRight();
            $roleRight->role_id = $this->id;
            $roleRight->right = $right;
            $roleRight->save();
        }
    }

    public function getRights()
    {
        return RoleRight::where('role_id', $this->id)->pluck('right')->toArray();
    }
}
