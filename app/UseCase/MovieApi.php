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
        $data = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('MOVIE_API_KEY'),
            'accept' => 'application/json',
        ])->get("https://api.themoviedb.org/3/movie/{$externalId}", [
            'language' => 'fr-FR',
        ])->json();

        return $data;
    }
}