<?php

namespace App\Http\Controllers\Api\Site;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\MovieCache;
use App\Models\Session;
use App\UseCase\Site\GetMovieWithSessions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MovieController extends Controller
{
    public function getWeeklyMovies()
    {
        // Calculer le début et la fin de la semaine cinématographique (mercredi au mardi)
        $now = Carbon::now();
        
        // Si on est lundi (1) ou mardi (2), on prend le mercredi de la semaine précédente
        // Sinon on prend le mercredi de cette semaine
        if ($now->dayOfWeek < Carbon::WEDNESDAY) {
            $startOfWeek = $now->previous(Carbon::WEDNESDAY)->startOfDay();
        } else {
            $startOfWeek = $now->dayOfWeek == Carbon::WEDNESDAY 
                ? $now->copy()->startOfDay() 
                : $now->previous(Carbon::WEDNESDAY)->startOfDay();
        }
        
        $endOfWeek = $startOfWeek->copy()->addDays(6)->endOfDay(); // Mardi suivant

        // Récupérer les IDs externes des films qui ont des séances cette semaine
        $movieExternalIds = Session::whereBetween('startTime', [$startOfWeek, $endOfWeek])
            ->join('movies', 'sessions.movieId', '=', 'movies.id')
            ->distinct()
            ->pluck('movies.externalId');

        // Récupérer les caches des films correspondants
        $movieCaches = MovieCache::whereIn('externalId', $movieExternalIds)->get();

        // Pour chaque film sans cache, créer le cache
        $missingExternalIds = $movieExternalIds->diff($movieCaches->pluck('externalId'));
        foreach ($missingExternalIds as $externalId) {
            if ($externalId) {
                try {
                    $movieCache = MovieCache::createIfNotExist($externalId);
                    $movieCaches->push($movieCache);
                } catch (\Exception $e) {
                    // Log l'erreur mais continue avec les autres films
                    \Log::error("Erreur lors de la création du cache pour le film {$externalId}: " . $e->getMessage());
                }
            }
        }

        return response()->json(
            $movieCaches,
        );
    }

    public function getUpcomingMovies()
    {
        // Calculer le début et la fin de la semaine cinématographique (mercredi au mardi)
        $now = Carbon::now();
        
        if ($now->dayOfWeek < Carbon::WEDNESDAY) {
            $startOfWeek = $now->previous(Carbon::WEDNESDAY)->startOfDay();
        } else {
            $startOfWeek = $now->dayOfWeek == Carbon::WEDNESDAY 
                ? $now->copy()->startOfDay() 
                : $now->previous(Carbon::WEDNESDAY)->startOfDay();
        }
        
        $endOfWeek = $startOfWeek->copy()->addDays(6)->endOfDay();

        // Récupérer les IDs des films qui ont des séances cette semaine
        $moviesWithSessionsThisWeek = Session::whereBetween('startTime', [$startOfWeek, $endOfWeek])
            ->distinct()
            ->pluck('movieId');

        // Récupérer les films actifs sans séances cette semaine
        $upcomingMovies = Movie::where('status', 1)
            ->whereNotIn('id', $moviesWithSessionsThisWeek)
            ->whereNotNull('externalId')
            ->get();

        // Récupérer les IDs externes
        $movieExternalIds = $upcomingMovies->pluck('externalId');

        // Récupérer les caches des films correspondants
        $movieCaches = MovieCache::whereIn('externalId', $movieExternalIds)->get();

        // Pour chaque film sans cache, créer le cache
        $missingExternalIds = $movieExternalIds->diff($movieCaches->pluck('externalId'));
        foreach ($missingExternalIds as $externalId) {
            if ($externalId) {
                try {
                    $movieCache = MovieCache::createIfNotExist($externalId);
                    $movieCaches->push($movieCache);
                } catch (\Exception $e) {
                    \Log::error("Erreur lors de la création du cache pour le film {$externalId}: " . $e->getMessage());
                }
            }
        }

        return response()->json(
            $movieCaches,
        );
    }

    public function getMovieWithSessions(Request $request, string $movieCacheId)
    {
        $cinemaApi = $request->get('cinemaApi');
        $cinemaIds = $request->input('cinemaIds', []);

        $result = GetMovieWithSessions::handle($movieCacheId, $cinemaApi, $cinemaIds);

        return response()->json($result);
    }
}