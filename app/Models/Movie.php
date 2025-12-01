<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    protected $fillable = [
        'title',
        'description',
        'releaseDate',
        'externalId',
        'sizeGB',
    ];

    public function versions()
    {
        return $this->hasMany(\App\Models\Movie\MovieVersion::class, 'movieId');
    }
}
