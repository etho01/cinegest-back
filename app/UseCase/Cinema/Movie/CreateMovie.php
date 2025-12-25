<?php 

class CreateMovie
{
    public function execute(string $externalId, Int $cinemaId, Int $size): Movie
    {
        $movieCache = MovieCache::createIfNotExist($externalId,);
        $durationMinutes = $movieCache->duration ?? 0;

        $movie = new Movie();
        $movie->title = $movieCache->title;
        $movie->description = $movieCache->description;
        $movie->releaseDate = $movieCache->releaseDate;
        $movie->externalId = $externalId;
        $movie->cinema_id = $cinemaId;
        $movie->durationMinutes = $durationMinutes;
        $movie->size = $size;
        $movie->save();


        return $movieCache;
    }
}