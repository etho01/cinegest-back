<?php

namespace App\Http\Controllers\Api\App\Entity\Cinema\Settings;

use App\Http\Controllers\Controller;
use App\Models\Cinema\Settings\Option;
use Illuminate\Http\Request;

class OptionsController extends Controller
{
    public function index(Request $request, Int $entityId, Int $cinemaId)
    {
        $search = $request->input('search', '');
        $query = Option::
            with('type')
            ->where('cinema_id', $cinemaId)
            ->where('name', 'like', '%' . $search . '%');

        if (count($request->input('optionTypes', [])) != 0)
        {
            $query->whereIn('options_type_id', $request->input('optionTypes', []));
        }

        return $query->paginate(30);
    }

    public function all(Int $entityId, Int $cinemaId)
    {
        return Option::
            where('cinema_id', $cinemaId)
            ->get();
    }

    public function store(Request $request, Int $entityId, Int $cinemaId)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'option_type_id' => 'required|integer|exists:options_types,id',
        ]);

        $option = new Option($validated);
        $option->options_type_id = $validated['option_type_id'];
        $option->cinema_id = $cinemaId;
        $option->save();

        return response()->json($option, 200);
    }

    public function update(Request $request, Int $entityId, Int $cinemaId, Int $optionId)
    {
        $option = Option::where('cinema_id', $cinemaId)->findOrFail($optionId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'option_type_id' => 'required|integer|exists:options_types,id',
        ]);

        $option->update($validated);
        $option->options_type_id = $validated['option_type_id'];
        $option->save();

        return response()->json($option);
    }

    public function destroy(Int $entityId, Int $cinemaId, Int $optionId)
    {
        $option = Option::where('cinema_id', $cinemaId)->findOrFail($optionId);
        $option->delete();

        return response()->json(null, 200);
    }
}
