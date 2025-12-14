<?php

namespace App\Models;

use App\Models\Entity\Cinema;
use Illuminate\Database\Eloquent\Model;

class CinemaApi extends Model
{
    protected $table = 'cinema_apis';

    protected $fillable = [
        'entityId',
        'apiKey',
        'name',
    ];

    public function cinemas()
    {
        return $this->belongsToMany(Cinema::class, 'cinema_apis_cinema', 'cinemaApiId', 'cinemaId');
    }
}
