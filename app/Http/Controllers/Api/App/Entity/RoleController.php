<?php

namespace App\Http\Controllers\Api\App\Entity;

use App\Http\Controllers\Controller;
use App\Models\Role\Role;
use App\Models\Role\RoleRight;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(Request $request, Int $entityId)
    {
        $search = $request->query('search');
        $query = Role::where('entity_id', $entityId);

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->paginate(30);
    }

    public function all(Int $entityId)
    {
        return Role::where('entity_id', $entityId)->get();
    }

    public function show(Int $entityId, Int $roleId)
    {
        $role = Role::where('entity_id', $entityId)->where('id', $roleId)->firstOrFail();
        $role->rights = RoleRight::where('role_id', $role->id)->pluck('right')->toArray();
        return $role;
    }

    public function store(Request $request, Int $entityId)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'rights' => 'nullable|array',
            'rights.*' => 'string|max:255',
        ]);

        $role = new Role();
        $role->name = $request->input('name');
        $role->entity_id = $entityId;
        $role->save();

        if ($request->filled('rights')) {
            $role->syncRights($request->input('rights'));
        }

        return $role;
    }

    public function update(Request $request, Int $entityId, $roleId)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'rights' => 'nullable|array',
            'rights.*' => 'string|max:255',
        ]);

        $role = Role::where('entity_id', $entityId)->where('id', $roleId)->firstOrFail();

        if ($request->filled('name')) {
            $role->name = $request->input('name');
        }
        $role->save();

        if ($request->filled('rights')) {
            $role->syncRights($request->input('rights'));
        }

        return $role;
    }

    public function destroy(Int $entityId, $roleId)
    {
        $role = Role::where('entity_id', $entityId)->where('id', $roleId)->firstOrFail();
        RoleRight::where('role_id', $role->id)->delete();
        $role->delete();

        return response()->noContent();
    }
}
