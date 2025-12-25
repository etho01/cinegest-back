<?php 

namespace App\UseCase;

use App\Interface\MovieApiInterface;
use Illuminate\Support\Facades\Http;

class MovieApi implements MovieApiInterface
{
    public static function search(string $searchTerm): array
    {
        $data = static::searchWithPage($searchTerm, 1);
        return $data;
    }

    public static function searchWithPage(string $searchTerm, int $page = 0): array
    {
        if ($page > 5) {
            return [];
        }
        
        $data = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('MOVIE_API_KEY'),
            'accept' => 'application/json',
        ])->get('https://api.themoviedb.org/3/search/movie', [
            'query' => $searchTerm,
            'language' => 'fr-FR',
            'page' => $page
        ])->json();

        $list = $data['results'] ?? [];
        if ($data['total_pages'] > $page + 1) {
            $nextPageList = static::searchWithPage($searchTerm, $page + 1);
            $list = array_merge($list, $nextPageList);
        }

        return $list;
    }

    public static function getDetails(string $externalId): ?array
    {
        // Récupérer les détails du film
        $data = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('MOVIE_API_KEY'),
            'accept' => 'application/json',
        ])->get("https://api.themoviedb.org/3/movie/{$externalId}", [
            'language' => 'fr-FR',
        ])->json();

        // Récupérer les crédits (réalisateur et acteurs)
        $credits = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('MOVIE_API_KEY'),
            'accept' => 'application/json',
        ])->get("https://api.themoviedb.org/3/movie/{$externalId}/credits", [
            'language' => 'fr-FR',
        ])->json();

        // Récupérer les vidéos (bande-annonce)
        $videos = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('MOVIE_API_KEY'),
            'accept' => 'application/json',
        ])->get("https://api.themoviedb.org/3/movie/{$externalId}/videos", [
            'language' => 'fr-FR',
        ])->json();

        // Récupérer les certifications (classification par âge)
        $releaseDates = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('MOVIE_API_KEY'),
            'accept' => 'application/json',
        ])->get("https://api.themoviedb.org/3/movie/{$externalId}/release_dates")->json();

        // Extraire le réalisateur
        $director = null;
        if (isset($credits['crew'])) {
            $directors = array_filter($credits['crew'], function ($person) {
                return $person['job'] === 'Director';
            });
            if (!empty($directors)) {
                $director = reset($directors)['name'];
            }
        }

        // Extraire les acteurs principaux (top 5)
        $cast = [];
        if (isset($credits['cast'])) {
            $cast = array_slice(array_map(function ($actor) {
                return $actor['name'];
            }, $credits['cast']), 0, 5);
        }

        // Extraire la bande-annonce
        $trailerUrl = null;
        if (isset($videos['results']) && !empty($videos['results'])) {
            // Chercher une bande-annonce en français
            $trailer = collect($videos['results'])->first(function ($video) {
                return $video['type'] === 'Trailer' && $video['site'] === 'YouTube';
            });
            if ($trailer) {
                $trailerUrl = "https://www.youtube.com/watch?v=" . $trailer['key'];
            }
        }

        // Extraire la classification par âge (France)
        $ageRating = null;
        if (isset($releaseDates['results'])) {
            $franceRelease = collect($releaseDates['results'])->first(function ($release) {
                return $release['iso_3166_1'] === 'FR';
            });
            if ($franceRelease && !empty($franceRelease['release_dates'])) {
                $certification = $franceRelease['release_dates'][0]['certification'] ?? null;
                if ($certification) {
                    $ageRating = $certification;
                }
            }
        }

        // Formater la durée (en heures et minutes)
        $duration = null;
        if (isset($data['runtime']) && $data['runtime']) {
            $hours = floor($data['runtime'] / 60);
            $minutes = $data['runtime'] % 60;
            if ($hours > 0) {
                $duration = $hours . 'h' . ($minutes > 0 ? str_pad($minutes, 2, '0', STR_PAD_LEFT) : '');
            } else {
                $duration = $minutes . 'min';
            }
        }

        // Construire l'URL du poster
        $posterUrl = null;
        if (isset($data['poster_path'])) {
            $posterUrl = "https://image.tmdb.org/t/p/w500" . $data['poster_path'];
        }

        // Construire l'URL du logo si disponible
        $logoUrl = null;
        if (isset($data['belongs_to_collection']['poster_path'])) {
            $logoUrl = "https://image.tmdb.org/t/p/w500" . $data['belongs_to_collection']['poster_path'];
        }

        $returnData = [
            'title' => $data['title'] ?? null,
            'posterUrl' => $posterUrl,
            'releaseDate' => $data['release_date'] ?? null,
            'genres' => array_map(function ($genre) {
                return $genre['name'];
            }, $data['genres'] ?? []),
            'director' => $director,
            'duration' => $duration,
            'ageRating' => $ageRating,
            'description' => $data['overview'] ?? null,
            'logoUrl' => $logoUrl,
            'trailerUrl' => $trailerUrl,
            'rating' => isset($data['vote_average']) ? round($data['vote_average'], 1) : null,
            'ratingCount' => $data['vote_count'] ?? null,
            'cast' => $cast,
        ];

        return $returnData;
    }

    public static function getImagesUrl(String $path, String $size = 'w500')
    {
        return "https://image.tmdb.org/t/p/{$size}{$path}";
    }
}