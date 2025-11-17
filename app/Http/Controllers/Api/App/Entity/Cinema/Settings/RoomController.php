<?php

namespace App\Http\Controllers\Api\App\Entity\Cinema\Settings;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index(Request $request, Int $entityId, Int $cinemaId)
    {
        $search = $request->input('search', '');
        $query = Room::with('Options', 'Storages')->where('cinema_id', $cinemaId)->where('name', 'like', '%' . $search . '%');

        $options = $request->input('options', []);
        if (count($options) != 0) {
            $query->whereHas('Options', function ($q) use ($options) {
                $q->whereIn('options.id', $options);
            });
        }

        $storages = $request->input('storages', []);
        if (count($storages) != 0) {
            $query->whereHas('Storages', function ($q) use ($storages) {
                $q->whereIn('storages.id', $storages);
            });
        }

        return $query->paginate(30);
    }

    public function all(Int $entityId, Int $cinemaId)
    {
        return Room::where('cinema_id', $cinemaId)->get();
    }

    public function store(Request $request, Int $entityId, Int $cinemaId)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:0',
            'optionsIds' => 'array',
            'optionsIds.*' => 'integer|exists:options,id',
            'storagesIds' => 'array',
            'storagesIds.*' => 'integer|exists:storages,id',
        ]);

        $room = new Room($validated);
        $room->cinema_id = $cinemaId;
        $room->save();

        foreach ($request->input('optionsIds', []) as $optionId) {
            $room->Options()->attach($optionId);
        }

        foreach ($request->input('storagesIds', []) as $storageId) {
            $room->Storages()->attach($storageId);
        }

        return response()->json($room, 200);
    }

    public function update(Request $request, Int $entityId, Int $cinemaId, Int $roomId)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:0',
            'optionsIds' => 'array',
            'optionsIds.*' => 'integer|exists:options,id',
            'storagesIds' => 'array',
            'storagesIds.*' => 'integer|exists:storages,id',
        ]);

        $room = Room::where('cinema_id', $cinemaId)->findOrFail($roomId);
        $room->update($validated);
        $room->save();

        $room->Options()->sync($request->input('optionsIds'));
        $room->Storages()->sync($request->input('storagesIds'));

        return response()->json($room, 200);
    }

    public function destroy(Int $entityId, Int $cinemaId, Int $roomId)
    {
        $room = Room::where('cinema_id', $cinemaId)->findOrFail($roomId);
        $room->delete();

        return response()->json(null, 200);
    }
}
