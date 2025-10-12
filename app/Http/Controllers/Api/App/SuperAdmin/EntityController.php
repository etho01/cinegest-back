<?php

namespace App\Http\Controllers\Api\App\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Entity;
use Illuminate\Http\Request;

class EntityController extends Controller
{
    public function index()
    {
        return Entity::paginate(30);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $entity = Entity::create([
            'name' => $request->name,
        ]);
        return response()->json($entity, 201);
    }

    public function show($id)
    {
        return response()->json(Entity::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
        ]);

        $entity = Entity::findOrFail($id);
        if ($request->has('name')) {
            $entity->name = $request->name;
        }
        $entity->save();

        return response()->json($entity);
    }

    public function destroy($id)
    {
        $entity = Entity::findOrFail($id);
        $entity->delete();
        return response()->json(null, 204);
    }
}
