<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();
        $users = User::with('roles')->get();
        
        return view('system.roles.index', compact('roles', 'permissions', 'users'));
    }

    public function assignRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_name' => 'required|exists:roles,name'
        ]);

        $user = User::findOrFail($request->user_id);
        $user->syncRoles([$request->role_name]);

        return back()->with('success', 'Role berhasil ditetapkan ke pengguna.');
    }
}
