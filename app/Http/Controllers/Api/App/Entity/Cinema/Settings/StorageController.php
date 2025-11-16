<?php

namespace App\Http\Controllers\Api\App\Entity\Cinema\Settings;

use App\Http\Controllers\Controller;
use App\Models\Cinema\Settings\Storage;
use Illuminate\Http\Request;

class StorageController extends Controller
{
    public function index(Request $request, Int $entityId, Int $cinemaId)
    {
        $search = $request->input('search', '');
        $query = Storage::
            with('type')
            ->where('cinema_id', $cinemaId)
            ->where('name', 'like', '%' . $search . '%');

        if (count($request->input('storageTypes', [])) != 0)
        {
            $query->whereIn('storage_type_id', $request->input('storageTypes', []));
        }

        return $query->paginate(30);
    }

    public function all(Int $entityId, Int $cinemaId)
    {
        return Storage::where('cinema_id', $cinemaId)->get();
    }

    public function store(Request $request, Int $entityId, Int $cinemaId)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'storage_type_id' => 'required|integer|exists:storage_types,id',
            'capacity' => 'required|integer|min:0',
        ]);

        $storage = new Storage($validated);
        $storage->storage_type_id = $validated['storage_type_id'];
        $storage->cinema_id = $cinemaId;
        $storage->save();

        return response()->json($storage, 200);
    }

    public function update(Request $request, Int $entityId, Int $cinemaId, Int $storageId)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'storage_type_id' => 'required|integer|exists:storage_types,id',
            'capacity' => 'required|integer|min:0',
        ]);

        $storage = Storage::where('cinema_id', $cinemaId)->findOrFail($storageId);
        $storage->update($validated);
        $storage->storage_type_id = $validated['storage_type_id'];
        $storage->save();

        return response()->json($storage, 200);
    }

    public function destroy(Int $entityId, Int $cinemaId, Int $storageId)
    {
        $storage = Storage::where('cinema_id', $cinemaId)->findOrFail($storageId);
        $storage->delete();

        return response()->json(null, 200);
    }
}
