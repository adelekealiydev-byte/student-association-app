<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class MemberProfileController extends Controller
{
    public function show(Request $request)
    {
        $member = $request->user();
        $invitation = $member->invitations()->latest()->first();

        return Inertia::render('me/Membership', [
            'member' => [
                'member_id' => $member->member_id,
                'email' => $member->email,
                'student_number' => $member->student_number,
                'username' => $member->username,
                'membership_status' => is_object($member->membership_status) ? $member->membership_status->value : $member->membership_status,
                'is_verified' => (bool) $member->is_verified,
                'roles' => $member->roles()->pluck('role_name')->all(),
                'joined_at' => $member->created_at->format('M d, Y'),
            ],
            'invitation' => $invitation ? [
                'status' => is_object($invitation->status) ? $invitation->status->value : $invitation->status,
                'submitted_at' => $invitation->submitted_at?->format('M d, Y H:i'),
            ] : null,
        ]);
    }

    public function update(Request $request)
    {
        $member = $request->user();

        $validated = $request->validate([
            'username' => ['required', 'string', 'max:50', 'unique:members,username,'.$member->member_id.',member_id'],
        ]);

        $member->update($validated);

        return back()->with('status', 'Profile updated successfully.');
    }
}

