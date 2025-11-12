<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Role\Role;
use App\Models\Role\RoleUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'type',
        'origin_id',
        'email',
        'phone',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_users')
            ->withPivot('cinema_id')
            ->withTimestamps();
    }

    public function getRoles() : array
    {
        $roles = $this->roles()->get();
        foreach ($roles as $i => $role) {
            $role->cinemaId = $role->pivot->cinema_id;
            $role->entityId = $role->entityId;
            $role->rights = $role->getRights();
            $roles[$i] = $role;
        }
        return $roles->toArray();
    }

    public function right()
    {
        return $this->hasMany(UserRight::class);
    }

    public function getRights(): array
    {
        return $this->right()->pluck('right')->toArray();
    }

    public function isSuperAdmin(): bool
    {
        return $this->roles()->where('role_id', 1)->exists();
    }

    public function hasRight(string $right, ?int $cinemaId): bool
    {
        if ($this->isSuperAdmin()) {
         //   return true;
        }

        return DB::table('role_rights')
            ->join('role_users', 'role_rights.role_id', '=', 'role_users.role_id')
            ->where('role_users.user_id', $this->id)
            ->where('role_rights.right', $right)
            ->when($cinemaId, function ($query) use ($cinemaId) {
                $query->where('role_users.cinema_id', $cinemaId);
            }, function ($query) {
                $query->whereNull('role_users.cinema_id');
            })
            ->exists();
    }

    public function getEntityList()
    {
        if ($this->isSuperAdmin()) {
            return Entity::all();
        }

        return Entity::where('id', $this->origin_id)->get();
    }
}
