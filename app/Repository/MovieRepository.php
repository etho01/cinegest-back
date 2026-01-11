<?php

namespace App\Repository;

use App\Models\Movie;
use Illuminate\Database\Eloquent\Collection;

class MovieRepository
{
    /**
     * Find movie by ID
     */
    public function find(int $id): ?Movie
    {
        return Movie::find($id);
    }

    /**
     * Find movie by external ID
     */
    public function findByExternalId(string $externalId): ?Movie
    {
        return Movie::where('externalId', $externalId)->first();
    }

    /**
     * Get active movies
     */
    public function getActiveMovies(): Collection
    {
        return Movie::where('status', 1)->get();
    }

    /**
     * Get active movies not in given IDs
     */
    public function getActiveMoviesNotIn(array $movieIds): Collection
    {
        return Movie::where('status', 1)
            ->whereNotIn('id', $movieIds)
            ->whereNotNull('externalId')
            ->get();
    }

    /**
     * Create a new movie
     */
    public function create(array $data): Movie
    {
        return Movie::create($data);
    }

    /**
     * Update a movie
     */
    public function update(Movie $movie, array $data): bool
    {
        return $movie->update($data);
    }
}
