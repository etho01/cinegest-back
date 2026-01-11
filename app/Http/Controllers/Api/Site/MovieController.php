<?php

namespace App\Http\Controllers\Api\Site;

use App\Http\Controllers\Controller;
use App\UseCase\Site\GetMovieWithSessions;
use App\Application\UseCase\Movie\GetWeeklyMovies;
use App\Application\UseCase\Movie\GetUpcomingMovies;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MovieController extends Controller
{
    private GetWeeklyMovies $getWeeklyMovies;
    private GetUpcomingMovies $getUpcomingMovies;

    public function __construct(
        GetWeeklyMovies $getWeeklyMovies,
        GetUpcomingMovies $getUpcomingMovies
    ) {
        $this->getWeeklyMovies = $getWeeklyMovies;
        $this->getUpcomingMovies = $getUpcomingMovies;
    }

    public function getWeeklyMovies(): JsonResponse
    {
        $movies = $this->getWeeklyMovies->execute();

        return response()->json(
            array_map(fn($movie) => $movie->toArray(), $movies)
        );
    }

    public function getUpcomingMovies(): JsonResponse
    {
        $movies = $this->getUpcomingMovies->execute();

        return response()->json(
            array_map(fn($movie) => $movie->toArray(), $movies)
        );
    }

    public function getMovieWithSessions(Request $request, string $movieCacheId): JsonResponse
    {
        $cinemaApi = $request->get('cinemaApi');
        $cinemaIds = $request->input('cinemaIds', []);

        $getMovieWithSessions = app(GetMovieWithSessions::class);
        $result = $getMovieWithSessions->handle($movieCacheId, $cinemaApi, $cinemaIds);

        return response()->json($result);
    }
}
