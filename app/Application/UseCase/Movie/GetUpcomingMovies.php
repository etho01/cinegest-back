<?php

namespace App\Application\UseCase\Movie;

use App\Domain\Repository\SessionRepositoryInterface;
use App\Domain\Repository\MovieRepositoryInterface;
use App\Domain\Repository\MovieCacheRepositoryInterface;
use App\Domain\ValueObject\DateRange;
use App\Services\CinemaWeekCalculator;
use App\Application\DTO\MovieCacheDTO;
use Illuminate\Support\Facades\Log;

class GetUpcomingMovies
{
    private SessionRepositoryInterface $sessionRepository;
    private MovieRepositoryInterface $movieRepository;
    private MovieCacheRepositoryInterface $movieCacheRepository;

    public function __construct(
        SessionRepositoryInterface $sessionRepository,
        MovieRepositoryInterface $movieRepository,
        MovieCacheRepositoryInterface $movieCacheRepository
    ) {
        $this->sessionRepository = $sessionRepository;
        $this->movieRepository = $movieRepository;
        $this->movieCacheRepository = $movieCacheRepository;
    }

    /**
     * Get upcoming movies (active movies without sessions this week)
     * 
     * @return MovieCacheDTO[]
     */
    public function execute(): array
    {
        $week = CinemaWeekCalculator::getCurrentWeek();
        $dateRange = new DateRange($week['start'], $week['end']);

        // Récupérer les IDs des films qui ont des séances cette semaine
        $moviesWithSessionsThisWeek = $this->sessionRepository->getMovieIdsByDateRange($dateRange);

        // Récupérer les films actifs sans séances cette semaine
        $upcomingMovies = $this->movieRepository->getActiveMoviesExcluding($moviesWithSessionsThisWeek);

        // Récupérer les IDs externes
        $movieExternalIds = array_map(fn($movie) => $movie->externalId(), $upcomingMovies);

        // Récupérer les caches des films correspondants
        $movieCaches = $this->movieCacheRepository->getByExternalIds($movieExternalIds);

        // Pour chaque film sans cache, créer le cache
        $existingExternalIds = array_column($movieCaches, 'externalId');
        $missingExternalIds = array_diff($movieExternalIds, $existingExternalIds);
        
        foreach ($missingExternalIds as $externalId) {
            if ($externalId) {
                try {
                    $movieCache = $this->movieCacheRepository->createIfNotExist($externalId);
                    $movieCaches[] = $movieCache;
                } catch (\Exception $e) {
                    Log::error("Erreur lors de la création du cache pour le film {$externalId}: " . $e->getMessage());
                }
            }
        }

        // Convert to DTOs
        return array_map(
            fn($cache) => new MovieCacheDTO(
                $cache['id'],
                $cache['externalId'],
                $cache['title'],
                $cache['overview'] ?? null,
                $cache['posterUrl'] ?? null,
                $cache['backdropUrl'] ?? null,
                $cache['voteAverage'] ?? null,
                $cache['releaseDate'] ?? null,
                $cache['runtime'] ?? null,
                $cache['director'] ?? null,
                $cache['actors'] ?? [],
                $cache['genres'] ?? []
            ),
            $movieCaches
        );
    }
}
