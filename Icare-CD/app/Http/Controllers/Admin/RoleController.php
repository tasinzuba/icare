<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    /**
     * Display a listing of roles
     */
    public function index()
    {
        // SECURITY (C1): the roles routes share the users.* middleware group, which authorizes
        // on ANY one of users.view/create/edit/delete. Enforce a specific permission per action
        // so a view-only admin cannot create/edit/delete roles (and thus self-escalate).
        abort_unless(auth()->user()->hasPermission('users.view'), 403);

        $roles = Role::withCount(['users', 'permissions'])->latest()->get();
        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role
     */
    public function create()
    {
        abort_unless(auth()->user()->hasPermission('users.create'), 403);

        $permissions = Permission::getGroupedByModule();
        return view('admin.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created role
     */
    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('users.create'), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'is_system_role' => false,
        ]);

        if (isset($validated['permissions'])) {
            $role->permissions()->attach($validated['permissions']);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role created successfully!');
    }

    /**
     * Display the specified role
     */
    public function show(Role $role)
    {
        abort_unless(auth()->user()->hasPermission('users.view'), 403);

        $role->load(['permissions', 'users']);
        return view('admin.roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified role
     */
    public function edit(Role $role)
    {
        abort_unless(auth()->user()->hasPermission('users.edit'), 403);

        $permissions = Permission::getGroupedByModule();
        $rolePermissionIds = $role->permissions->pluck('id')->toArray();
        
        return view('admin.roles.edit', compact('role', 'permissions', 'rolePermissionIds'));
    }

    /**
     * Update the specified role
     */
    public function update(Request $request, Role $role)
    {
        abort_unless(auth()->user()->hasPermission('users.edit'), 403);

        // SECURITY (C1): a user must never edit the permissions of the role they hold —
        // that is the direct self-escalation path (grant every permission to your own role).
        if ((int) $role->id === (int) auth()->user()->role_id) {
            abort(403, 'You cannot modify your own role.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
        ]);

        $role->permissions()->sync($validated['permissions'] ?? []);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role updated successfully!');
    }

    /**
     * Remove the specified role
     */
    public function destroy(Role $role)
    {
        abort_unless(auth()->user()->hasPermission('users.delete'), 403);

        if ((int) $role->id === (int) auth()->user()->role_id) {
            abort(403, 'You cannot delete your own role.');
        }

        if ($role->isSystemRole()) {
            return back()->with('error', 'Cannot delete system role!');
        }

        if ($role->users()->count() > 0) {
            return back()->with('error', 'Cannot delete role that has users assigned to it!');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role deleted successfully!');
    }
}
