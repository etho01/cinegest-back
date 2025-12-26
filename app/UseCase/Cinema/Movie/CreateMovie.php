<?php 

namespace App\UseCase\Cinema\Movie;

use App\Models\Movie;
use App\Models\MovieCache;

class CreateMovie
{
    public static function handle(string $externalId, Int $cinemaId, Int $size): Movie
    {
        $movieCache = MovieCache::createIfNotExist($externalId);

        $movie = new Movie();
        $movie->title = $movieCache->title;
        $movie->description = $movieCache->description;
        $movie->releaseDate = $movieCache->releaseDate;
        $movie->durationMinutes = $movieCache->durationMinutes;
        $movie->externalId = $externalId;
        $movie->cinema_id = $cinemaId;
        $movie->size = $size;
        $movie->save();


        return $movie;
    }
}