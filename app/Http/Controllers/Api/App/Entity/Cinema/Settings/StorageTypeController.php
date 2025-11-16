<?php

namespace App\Http\Controllers\Api\App\Entity\Cinema\Settings;

use App\Http\Controllers\Controller;
use App\Models\Cinema\Settings\StorageType;
use Illuminate\Http\Request;

class StorageTypeController extends Controller
{
    public function index(Request $request, Int $entityId, Int $cinemaId)
    {
        $search = $request->query('search', '');
        return StorageType::where('cinema_id', $cinemaId)
            ->where('name', 'like', '%' . $search . '%')
            ->paginate(30);
    }

    public function all(Int $entityId, Int $cinemaId)
    {
        return StorageType::where('cinema_id', $cinemaId)->get();
    }
    
    public function store(Request $request, Int $entityId, Int $cinemaId)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $storageType = new StorageType($validated);
        $storageType->cinema_id = $cinemaId;
        $storageType->save();

        return response()->json($storageType, 200);
    }

    public function update(Request $request, Int $entityId, Int $cinemaId, Int $storageTypeId)
    {
        $storageType = StorageType::where('cinema_id', $cinemaId)->findOrFail($storageTypeId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $storageType->update($validated);

        return response()->json($storageType);
    }

    public function destroy(Int $entityId, Int $cinemaId, Int $storageTypeId)
    {
        $storageType = StorageType::where('cinema_id', $cinemaId)->findOrFail($storageTypeId);
        $storageType->delete();

        return response()->json(null, 200);
    }
}
