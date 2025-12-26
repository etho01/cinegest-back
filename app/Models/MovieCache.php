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
        'durationMinutes',
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

    public function getSessions(array $cinemaIds = []): \Illuminate\Database\Eloquent\Collection
    {
        $sessions = Session::with([
            "movieVersion" => [
                "options"
            ],
        ])
            ->whereHas('movie', function ($query) {
                $query->where('externalId', $this->externalId);
            })
            ->when(!empty($cinemaIds), function ($query) use ($cinemaIds) {
                $query->whereIn('cinemaId', $cinemaIds);
            })
            ->get();

        foreach ($sessions as $session) {
            $session->options = $session->movieVersion->options;
        }

        return $sessions;
    }
}
