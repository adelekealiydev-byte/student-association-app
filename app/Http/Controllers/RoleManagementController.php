<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Role;
use App\Services\RoleService;
use DomainException;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoleManagementController extends Controller
{
    public function index(Request $request)
    {
        $roles = Role::all();

        // Get members with roles
        $membersWithRoles = Member::whereHas('roles')
            ->with(['roles'])
            ->get()
            ->map(function ($m) {
                return [
                    'member_id' => $m->member_id,
                    'email' => $m->email,
                    'student_number' => $m->student_number,
                    'username' => $m->username,
                    'roles' => $m->roles->map(fn ($r) => [
                        'role_id' => $r->role_id,
                        'role_name' => $r->role_name,
                        'assigned_by' => $r->pivot->assigned_by,
                        'assigned_at' => $r->pivot->created_at?->format('M d, Y H:i'),
                    ]),
                ];
            });

        // Get list of active verified members who can be assigned roles
        $assignableMembers = Member::where('is_verified', true)
            ->select('member_id', 'student_number', 'email', 'username')
            ->orderBy('student_number')
            ->get();

        return Inertia::render('roles/Index', [
            'roles' => $roles,
            'membersWithRoles' => $membersWithRoles,
            'assignableMembers' => $assignableMembers,
        ]);
    }

    public function store(Request $request, RoleService $roleService)
    {
        $validated = $request->validate([
            'member_id' => ['required', 'integer', 'exists:members,member_id'],
            'role_id' => ['required', 'integer', 'exists:roles,role_id'],
        ]);

        $member = Member::findOrFail($validated['member_id']);
        $role = Role::findOrFail($validated['role_id']);

        $roleService->assign($member, $role, $request->user());

        return back()->with('status', "Role '{$role->role_name}' assigned to {$member->student_number}.");
    }

    public function destroy(Request $request, Member $member, Role $role, RoleService $roleService)
    {
        try {
            $roleService->remove($member, $role, $request->user());
        } catch (DomainException $e) {
            return back()->withErrors(['role' => $e->getMessage()]);
        }

        return back()->with('status', "Role '{$role->role_name}' removed from {$member->student_number}.");
    }
}

