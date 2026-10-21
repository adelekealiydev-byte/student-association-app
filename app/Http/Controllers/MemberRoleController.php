<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Role;
use App\Services\RoleService;
use Illuminate\Http\Request;

class MemberRoleController extends Controller
{
    public function store(Request $request, Member $member, RoleService $roles)
    {
        $request->validate([
            'role_id' => ['required', 'integer', 'exists:roles,role_id'],
        ]);

        $role = Role::findOrFail($request->role_id);
        $roles->assign($member, $role, $request->user());

        return back()->with('status', 'Role assigned.');
    }
}

