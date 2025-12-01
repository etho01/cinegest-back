<?php

namespace App\Http\Controllers\Api\App\Entity\Cinema;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\UseCase\MovieApi;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    public function search(Request $request)
    {
        $search = $request->input('search', '');
        $movies = MovieApi::search($search);
        return $movies;
    }

    public function show(Int $entityId, Int $cinemaId, Int $movieId)
    {
        $movie = Movie::with([
            'versions.options'
        ])->findOrFail($movieId);
        return $movie;
    }

    public function index(Int $entityId, Int $cinemaId)
    {
        $search = request()->input('search', '');
        $status = request()->input('status', []);

        $query = Movie::where('title', 'like', '%' . $search . '%')->where('cinema_id', $cinemaId);
        if (count($status) != 0) {
            $query->whereIn('status', $status);
        }
        return $query->paginate(30);
    }

    public function store(Request $request, Int $entityId, Int $cinemaId)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'releaseDate' => 'required|date',
            'externalId' => 'required',
            'sizeGB' => 'nullable|numeric',
        ]);

        $durationMinutes = MovieApi::getDetails($validated['externalId'])['runtime'] ?? 0;
        $validated['durationMinutes'] = $durationMinutes;

        $movie = new Movie($validated);
        $movie->cinema_id = $cinemaId;
        $movie->durationMinutes = $durationMinutes;
        $movie->sizeGB = $request->input('sizeGB', 0);
        $movie->save();

        return response()->json(['message' => 'Movie created successfully'], 201);
    }

    public static function destroy(Int $entityId, Int $cinemaId, Int $movieId)
    {
        $movie = Movie::findOrFail($movieId);
        $movie->delete();

        return response()->json(['message' => 'Movie deleted successfully'], 200);
    }
}
