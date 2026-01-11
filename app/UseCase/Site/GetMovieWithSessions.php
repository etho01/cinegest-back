<?php 

namespace App\UseCase\Site;

use App\Exceptions\Site\CinemaNotAllowed;
use App\Repository\MovieCacheRepository;
use App\Models\CinemaApi;

class GetMovieWithSessions
{
    private MovieCacheRepository $movieCacheRepository;

    public function __construct(MovieCacheRepository $movieCacheRepository)
    {
        $this->movieCacheRepository = $movieCacheRepository;
    }

    public function handle(string $movieCacheId, CinemaApi $cinemaApi, array $cinemaIds = []): array
    {
        $movieCache = $this->movieCacheRepository->find($movieCacheId);

        if (!$movieCache) {
            throw new \Exception('Movie cache not found');
        }

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
