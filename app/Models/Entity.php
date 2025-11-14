<?php

namespace App\Models;

use App\Models\Entity\Cinema;
use Illuminate\Database\Eloquent\Model;

class Entity extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
    ];

    public function cinemas()
    {
        return $this->hasMany(Cinema::class, 'entity_id');
    }
}
