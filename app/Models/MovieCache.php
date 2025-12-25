<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\UseCase\MovieApi;

class MovieCache extends Model
{
    protected $fillable = [
        'externalId',
        'title',
        'posterUrl',
        'releaseDate',
        'genres',
        'director',
        'duration',
        'ageRating',
        'description',
        'logoUrl',
        'trailerUrl',
        'rating',
        'ratingCount',
        'cast',
    ];

    protected $casts = [
        'genres' => 'array',
        'cast' => 'array',
        'releaseDate' => 'date',
        'rating' => 'decimal:1',
        'ratingCount' => 'integer',
    ];

    public static function createIfNotExist(String $externalId): MovieCache
    {
        $movieCache = MovieCache::where('externalId', $externalId)->first();
        
        if (!$movieCache) {
            $data = MovieApi::getDetails($externalId);
            $movieCache = MovieCache::create(array_merge(
                ['externalId' => $externalId],
                $data
            ));
        }
        
        return $movieCache;
    }
}
