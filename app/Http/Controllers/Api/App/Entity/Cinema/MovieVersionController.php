<?php

namespace App\Http\Controllers\Api\App\Entity\Cinema;

use App\Http\Controllers\Controller;
use App\Models\Movie\MovieVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MovieVersionController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'movieId' => 'required|exists:movies,id',
            'versionName' => 'required|string|max:255',
            'size' => 'nullable|numeric',
            'options' => 'nullable|array',
            'options.*.id' => 'exists:options,id',
        ]);

        $movieVersion = MovieVersion::create($validated);
        if (isset($validated['options'])) {
            $movieVersion->options()->sync(collect($validated['options'])->pluck('id')->toArray());
        }

        return response()->json($movieVersion, 201);
    }

    public function search(Request $request)
    {
        $search = $request->input('search', '');
        $movieVersions = MovieVersion::
        with('movie')
        ->select('movie_versions.*')
        ->where('movies.status', 1)
        ->join('movies', 'movies.id', '=', 'movie_versions.movieId')
        ->where(DB::raw('CONCAT(movies.title, " ", movie_versions.versionName)'), 'like', '%' . $search . '%')->get();
        return $movieVersions;
    }

    public function update(Request $request, Int $entityId, Int $cinemaId, Int $movieId, Int $versionId)
    {
        $movieVersion = MovieVersion::findOrFail($versionId);

        $validated = $request->validate([
            'versionName' => 'sometimes|required|string|max:255',
            'size' => 'sometimes|nullable|numeric',
            'options' => 'sometimes|nullable|array',
            'options.*.id' => 'exists:options,id',
        ]);

        $movieVersion->update($validated);
        if (isset($validated['options'])) {
            $movieVersion->options()->sync(collect($validated['options'])->pluck('id')->toArray());
        }

        return response()->json($movieVersion, 200);
    }

    public function destroy(Int $entityId, Int $cinemaId, Int $movieId, Int $versionId)
    {
        $movieVersion = MovieVersion::findOrFail($versionId);
        $movieVersion->delete();

        return response()->json(['message' => 'Movie version deleted successfully'], 200);
    }
}
