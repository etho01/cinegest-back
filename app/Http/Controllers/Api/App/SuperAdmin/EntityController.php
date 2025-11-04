<?php

namespace App\Http\Controllers\Api\App\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Entity;
use Illuminate\Http\Request;

class EntityController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        return Entity::where('name', 'like', "%{$search}%")->paginate(30);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $entity = Entity::create([
            'name' => $request->name,
        ]);
        return $entity;
    }

    public function show($id)
    {
        return Entity::findOrFail($id);
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

        return $entity;
    }

    public function destroy($id)
    {
        $entity = Entity::findOrFail($id);
        $entity->delete();
    }
}
