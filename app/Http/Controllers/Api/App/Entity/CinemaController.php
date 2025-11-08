<?php

namespace App\Http\Controllers\Api\App\Entity;

use App\Http\Controllers\Controller;
use App\Models\Entity\Cinema;
use Illuminate\Http\Request;

class CinemaController extends Controller
{
    public function index(Request $request, Int $entityId)
    {
        $search = $request->input('search');
        return Cinema::where(function ($query) use ($search) {
            if ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('address', 'like', "%{$search}%")
                      ->orWhere('address_complement', 'like', "%{$search}%")
                      ->orWhere('postal_code', 'like', "%{$search}%")
                      ->orWhere('city', 'like', "%{$search}%")
                      ->orWhere('country', 'like', "%{$search}%");
            }
        })->where('entity_id', $entityId)->paginate(30);
    }

    public function store(Request $request, Int $entityId)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'address_complement' => 'nullable|string|max:255',
            'postal_code' => 'required|string|max:20',
            'city' => 'required|string|max:100',
            'country' => 'required|string|max:100',
        ]);

        $cinema = new Cinema($validated);
        $cinema->entity_id = $entityId;
        $cinema->save();

        return response()->json($cinema, 201);
    }

    public function show(Int $entityId, Cinema $cinema)
    {
        if ($cinema->entity_id != $entityId) {
            return response()->json(['message' => 'Cinema not found'], 404);
        }

        return response()->json($cinema);
    }

    public function update(Request $request, Int $entityId, Cinema $cinema)
    {
        if ($cinema->entity_id != $entityId) {
            return response()->json(['message' => 'Cinema not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'address' => 'sometimes|required|string|max:255',
            'address_complement' => 'nullable|string|max:255',
            'postal_code' => 'sometimes|required|string|max:20',
            'city' => 'sometimes|required|string|max:100',
            'country' => 'sometimes|required|string|max:100',
        ]);

        $cinema->update($validated);
        return response()->json($cinema);
    }

    public function destroy(Int $entityId, Cinema $cinema)
    {
        if ($cinema->entity_id != $entityId) {
            return response()->json(['message' => 'Cinema not found'], 404);
        }

        $cinema->delete();
        return response()->json(['message' => 'Cinema deleted successfully']);
    }
}   
