<?php

namespace App\Http\Controllers\Api\App\Entity\Cinema;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\App\Entity\Cinema\StorageItemResource;
use App\Models\Cinema\Settings\Storage;
use App\Models\Cinema\StorageItem;
use App\UseCase\Cinema\Storage\AddStorageItem;
use Illuminate\Http\Request;

class StorageItemController extends Controller
{
    public function index(Request $request)
    {
        $rooms = $request->input('rooms', []);
        $storage = $request->input('storage', []);
        $movie = $request->input('movie', []);
        $query = StorageItem::with(['storage', 'room', 'movieVersion', 'movie', 'origin']);
        if (!empty($rooms)) {
            $query->whereIn('roomId', $rooms);
        }
        if (!empty($storage)) {
            $query->whereIn('storageId', $storage);
        }
        if (!empty($movie)) {
            $query->whereIn('movieId', $movie);
        }
        return  StorageItemResource::collection($query->paginate(30));
    }

    public function stores(Request $request)
    {
        $request->validate([
            'roomId' => 'nullable|integer|exists:rooms,id',
            'storageId' => 'nullable|integer|exists:storages,id',
            'originId' => 'nullable|integer|exists:storages,id',
            'movieVersions' => 'array',
            'movieVersions.*' => 'required|integer|exists:movie_versions,id',
        ]);

       AddStorageItem::handle(
            roomId: $request->input('roomId'),
            storageId: $request->input('storageId'),
            originId: $request->input('originId'),
            movieVersions: $request->input('movieVersions', []),
        );

        return response()->json(['message' => 'Storage items added successfully.'], 201);
    }
}
