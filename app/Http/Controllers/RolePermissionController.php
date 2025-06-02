<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use App\Models\User;

class RolePermissionController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('roles')->select('id', 'first_name', 'last_name');
          if ($request->search) {
            $search = strtolower($request->search);
            $query->where(function($q) use ($search) {
                $q->whereRaw('LOWER(first_name) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(last_name) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw("LOWER(CONCAT(first_name, ' ', last_name)) LIKE ?", ["%{$search}%"])
                  ->orWhereHas('roles', function($q) use ($search) {
                      $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                  });
            });
        }

        return Inertia::render('RolePermission/Index', [
            'roles' => Role::with('permissions')->get(),
            'permissions' => Permission::all(),
            'users' => $query->paginate(10)->withQueryString(),
            'filters' => $request->only(['search'])
        ]);
    }

    public function createRole(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique(Role::class)],
            'permissions' => ['array']
        ]);

        $role = Role::create(['name' => $validated['name'], 'guard_name' => 'web']);

        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return redirect()->back()->with('success', 'Role created successfully.');
    }

    public function createPermission(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique(Permission::class)]
        ]);

        Permission::create(['name' => $validated['name'], 'guard_name' => 'web']);

        return redirect()->back()->with('success', 'Permission created successfully.');
    }

    public function updateRole(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique(Role::class)->ignore($role->id)],
            'permissions' => ['array']
        ]);

        $role->update(['name' => $validated['name']]);
        
        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return redirect()->back()->with('success', 'Role updated successfully.');
    }

    public function deleteRole(Role $role)
    {
        $role->delete();
        return redirect()->back()->with('success', 'Role deleted successfully.');
    }

    public function deletePermission(Permission $permission)
    {
        $permission->delete();
        return redirect()->back()->with('success', 'Permission deleted successfully.');
    }

    public function assignRole(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'role' => ['required', 'exists:roles,name']
        ]);

        $user = User::findOrFail($validated['user_id']);
        $user->assignRole($validated['role']);

        return redirect()->back()->with('success', 'Role assigned successfully.');
    }

    public function unassignRole(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'role' => ['required', 'exists:roles,name']
        ]);

        $user = User::findOrFail($validated['user_id']);
        $user->removeRole($validated['role']);

        return redirect()->back()->with('success', 'Role unassigned successfully.');
    }
}
