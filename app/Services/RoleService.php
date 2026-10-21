<?php

namespace App\Services;

use App\Models\Member;
use App\Models\Role;
use Illuminate\Auth\Access\AuthorizationException;

class RoleService
{
    public function assign(Member $member, Role $role, Member $assigner): void
    {
        if (! $assigner->hasRole('Admin')) {
            throw new AuthorizationException('Only an Admin can assign roles.');
        }

        $member->roles()->syncWithoutDetaching([
            $role->role_id => ['assigned_by' => $assigner->member_id],
        ]);
    }
}

