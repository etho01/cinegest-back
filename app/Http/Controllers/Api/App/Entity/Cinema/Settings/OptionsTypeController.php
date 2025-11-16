<?php

namespace App\Http\Controllers\Api\App\Entity\Cinema\Settings;

use App\Http\Controllers\Controller;
use App\Models\Cinema\Settings\OptionsType;
use Illuminate\Http\Request;

class OptionsTypeController extends Controller
{
    public function index(Int $entityId, Int $cinemaId)
    {
        return OptionsType::
            where('cinema_id', $cinemaId)
            ->where('name', 'like', '%' . request()->query('search', '') . '%')
            ->paginate(30);
    }

    public function all(Int $entityId, Int $cinemaId)
    {
        return OptionsType::
            where('cinema_id', $cinemaId)
            ->get();
    }

    public function store(Request $request, Int $entityId, Int $cinemaId)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $optionsType = new OptionsType($validated);
        $optionsType->cinema_id = $cinemaId;
        $optionsType->save();

        return response()->json($optionsType, 200);
    }

    public function update(Request $request, Int $entityId, Int $cinemaId, Int $optionsTypeId)
    {
        $optionsType = OptionsType::where('cinema_id', $cinemaId)->findOrFail($optionsTypeId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $optionsType->update($validated);

        return response()->json($optionsType);
    }

    public function destroy(Int $entityId, Int $cinemaId, Int $optionsTypeId)
    {
        $optionsType = OptionsType::where('cinema_id', $cinemaId)->findOrFail($optionsTypeId);
        $optionsType->delete();

        return response()->json(null, 200);
    }
}
