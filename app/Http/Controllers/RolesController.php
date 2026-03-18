<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Roles;
use Illuminate\Http\Request;

class RolesController extends Controller
{
    public function index(Request $request)
    {
        $query = Roles::query();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'LIKE', "%$search%")
                ->orWhere('description', 'LIKE', "%$search%");
        }

        $allroles = $query->paginate(10)->appends(['search' => $request->search]);

        return view('roles.index', compact('allroles'));
    }
    public function create()
{
    $permissions = Permission::all()->groupBy(function ($perm) {
        return explode(' ', $perm->name)[1] ?? $perm->name;
    });

    return view('roles.create', [
        'allpermission' => $permissions
    ]);
}

    public function store()
    {
        request()->validate([
            "name" => ['required', 'min:3'],
            "description" => ['required']
        ]);
        $data = request()->all();
        $role = Roles::create([
            "name" => $data['name'],
            "description" => $data['description'],
        ]);
        if (!empty($data['permissions'])) {
            $permissionsData = [];

            foreach ($data['permissions'] as  $permissions) {
                foreach ($permissions as $permissionIds) {
                    foreach ($permissionIds as $permissionId) {
                        $permissionsData[] = [
                            "role_id" => $role->id,
                            "permission_id" => $permissionId,
                        ];
                    }
                }
            }
            PermissionRole::insert($permissionsData);
        }


        session()->flash('success', "Role addes success");
        return to_route("roles.index");
    }
   public function edit(Roles $role)
{
    // Group by the `group_by` column (fix typo from groub_by if needed)
    $allpermission = Permission::all()
        ->groupBy('group_by') // make sure your column is named correctly
        ->map(function ($group) {
            return $group->map(function ($perm) {
                return [
                    'id' => $perm->id,
                    'name' => $perm->name,
                ];
            });
        });

    $rolePermissions = $role->permissions()->pluck('permission_id')->toArray();

    return view('roles.edit', compact('role', 'allpermission', 'rolePermissions'));
}

    public function update(Roles $role)
    {
        if ($role->name === 'Super Admin') {
            return redirect()->back()->with('errors', ["Can't update the Super Admin role."]);
        }
    
        request()->validate([
            "name" => ['required', 'min:3'],
            "description" => ['required']
        ]);

        $data = request()->all();
        $role->update([
            "name" => $data['name'],
            "description" => $data['description'],
        ]);

        if (!empty($data['permissions'])) {
            $role->permissions()->detach();
            $permissionsData = [];

            foreach ($data['permissions'] as $values) {
                foreach ($values as $permissionIds) {
                    foreach ($permissionIds as $permissionId) {
                        $permissionsData[] = $permissionId;
                    }
                }
            }
            $role->permissions()->attach($permissionsData);
        }

        session()->flash('success', "We updated role named $role->name");

        return to_route("roles.index");
    }

    public function destroy(Roles $role)
    {
        if ($role->name === 'Super Admin') {
            return redirect()->back()->with('errors', ["Can't delete the Super Admin role."]);
        }
    
        $role->delete();
        session()->flash('success', "Role '{$role->name}' has been deleted.");
    
        return to_route("roles.index");
    }
    
}
