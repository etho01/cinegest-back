<?php 

namespace App\UseCase\Site;

use App\Exceptions\Site\CinemaNotAllowed;
use App\Models\CinemaApi;
use App\Models\MovieCache;

class GetMovieWithSessions
{
    public static function handle(string $movieCacheId, CinemaApi $cinemaApi, array $cinemaIds = []): array
    {
        $movieCache = MovieCache::findOrFail($movieCacheId);

        if (!empty($cinemaIds) && !$cinemaApi->cinemaIdsIsValid($cinemaIds)) {
            throw new CinemaNotAllowed();
        }

        if (empty($cinemaIds)) {
            $cinemaIds = $cinemaApi->cinemas()->pluck('cinemas.id')->toArray();
        }

        $sessions = $movieCache->getSessions($cinemaIds);

        return [
            'movie' => $movieCache,
            'sessions' => $sessions,
        ];
    }
}