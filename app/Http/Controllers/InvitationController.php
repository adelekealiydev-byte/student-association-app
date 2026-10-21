<?php

namespace App\Http\Controllers;

use App\Http\Requests\InviteMemberRequest;
use App\Models\Member;
use App\Services\InvitationService;

class InvitationController extends Controller
{
    public function store(InviteMemberRequest $request, InvitationService $invitations)
    {
        $invitations->invite($request->validated('email'), $request->validated('student_number'));

        return back()->with('status', 'Invitation sent.');
    }

    public function resend(Member $member, InvitationService $invitations)
    {
        abort_if($member->is_verified, 422, 'This member is already verified.');

        $invitations->issueFor($member);

        return back()->with('status', 'Invitation re-sent.');
    }
}

