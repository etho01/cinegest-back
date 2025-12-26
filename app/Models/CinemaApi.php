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

    public function prices()
    {
        return $this->hasMany(CinemaApi\Price::class, 'cinema_api_id');
    }

    public function cinemaIdsIsValid(array $cinemaIds): bool
    {
        $validCinemaIds = $this->cinemas()->pluck('cinemas.id')->toArray();
        foreach ($cinemaIds as $cinemaId) {
            if (!in_array($cinemaId, $validCinemaIds)) {
                return false;
            }
        }
        return true;
    }
}
