<?php

namespace App\Http\Controllers\Api\App\Entity\Cinema;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\App\Entity\Cinema\KeyResource;
use App\Http\Resources\Api\App\Entity\Cinema\MovieVersionResource;
use App\Models\Cinema\Key;
use Illuminate\Http\Request;

class KeyController extends Controller
{
    public function index(Int $entityId, Int $cinemaId)
    {
        $query = Key::
        with([
            'movieVersion' =>[
                'movie',
            ],
            'room',
        ])
        ->where('cinemaId', $cinemaId);
        return KeyResource::collection($query->paginate(30));
    }

    public function addKeys(Request $request, Int $entityId, Int $cinemaId)
    {
        $request->validate([
            'dateStart' => 'date|required',
            'dateEnd' => 'date|required|after_or_equal:dateStart',
            'versions' => 'array|required',
        ]);

        $dateStart = $request->input('dateStart');
        $dateEnd = $request->input('dateEnd');
        $versions = $request->input('versions');

        foreach ($versions as $version) {
            $movieVersionId = $version['movieVersionId'];
            $rooms = $version['rooms'];

            foreach ($rooms as $roomId) {

                // Check if a key already exists for this combination
                $existingKey = Key::where('cinemaId', $cinemaId)
                    ->where('movieVersionId', $movieVersionId)
                    ->where('roomId', $roomId)
                    ->where('dateStart', '<>', $dateStart)
                    ->where('dateEnd', '<>', $dateEnd)
                    ->first();

                if (!$existingKey) {
                    // Create new key
                    Key::create([
                        'cinemaId' => $cinemaId,
                        'movieVersionId' => $movieVersionId,
                        'roomId' => $roomId,
                        'dateStart' => date('Y-m-d H:i:s', strtotime($dateStart)),
                        'dateEnd' => date('Y-m-d H:i:s', strtotime($dateEnd)),
                    ]);
                }
            }
        }

        return[];
    }

    public function destroy(Int $entityId, Int $cinemaId, Int $keyId)
    {
        $key = Key::where('cinemaId', $cinemaId)->findOrFail($keyId);
        $key->delete();

        return response()->json(['message' => 'Key deleted successfully'], 200);
    }
}
