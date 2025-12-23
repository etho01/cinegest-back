<?php

namespace App\Http\Controllers\Api\App\Entity;

use App\Http\Controllers\Controller;
use App\Models\CinemaApi;
use Illuminate\Http\Request;

class CinemaApiController extends Controller
{
    public function index(Request $request)
    {
        return CinemaApi::with('cinemas')->paginate(30);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'entityId' => 'required|integer',
            'name' => 'required|string',
            'cinemaIds' => 'array',
        ]);

        $cinemaApi = CinemaApi::create([
            'entityId' => $data['entityId'],
            'apiKey' => bin2hex(random_bytes(16)),
            'name' => $data['name'],
        ]);

        if (isset($data['cinemaIds'])) {
            $cinemaApi->cinemas()->sync($data['cinemaIds']);
        }

        return $cinemaApi;
    }

    public function show(Int $entityId, Int $id )
    {
        return CinemaApi::with('cinemas', 'prices')->findOrFail($id);
    }

    public function update(Request $request, Int $entityId, Int $id )
    {
        $data = $request->validate([
            'entityId' => 'required|integer',
            'name' => 'required|string',
            'cinemaIds' => 'array',
        ]);

        $cinemaApi = CinemaApi::findOrFail($id);
        $cinemaApi->update([
            'entityId' => $data['entityId'],
            'name' => $data['name'],
        ]);

        if (isset($data['cinemaIds'])) {
            $cinemaApi->cinemas()->sync($data['cinemaIds']);
        }

        return $cinemaApi;
    }
     
    public function destroy(Int $entityId, Int $id )
    {
        $cinemaApi = CinemaApi::findOrFail($id);
        $cinemaApi->delete();
    }
}
