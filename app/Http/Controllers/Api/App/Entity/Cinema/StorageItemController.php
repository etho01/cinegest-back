<?php

namespace App\Http\Controllers\Api\App\Entity\Cinema;

use App\Http\Controllers\Controller;
use App\Models\Cinema\Settings\Storage;
use App\Models\Cinema\StorageItem;
use Illuminate\Http\Request;

class StorageItemController extends Controller
{
    public function index(Request $request)
    {
        $rooms = $request->input('rooms', []);
        $storage = $request->input('storage', []);
        $movie = $request->input('movie', []);
        $query = StorageItem::query();
        if (!empty($rooms)) {
            $query->whereIn('roomId', $rooms);
        }
        if (!empty($storage)) {
            $query->whereIn('storageId', $storage);
        }
        if (!empty($movie)) {
            $query->whereIn('movieId', $movie);
        }
        return $query->paginate(30);
    }

    public function stores(Request $request)
    {
        $request->validate([
            'roomId' => 'nullable|integer|exists:rooms,id',
            'storageId' => 'nullable|integer|exists:storages,id',
            'originId' => 'nullable|integer|exists:storages,id',
            'versions' => 'required|array',
            'versions.*.id' => 'required|integer|exists:movie_versions,id',
        ]);
    }
}
