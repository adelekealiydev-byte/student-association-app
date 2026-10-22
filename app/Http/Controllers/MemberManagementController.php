<?php

namespace App\Http\Controllers;

use App\Enums\MembershipStatus;
use App\Models\Member;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MemberManagementController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status');

        $query = Member::query()->with('roles');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                    ->orWhere('student_number', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('membership_status', $status);
        }

        $members = $query->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn ($m) => [
                'member_id' => $m->member_id,
                'email' => $m->email,
                'student_number' => $m->student_number,
                'username' => $m->username,
                'membership_status' => is_object($m->membership_status) ? $m->membership_status->value : $m->membership_status,
                'is_verified' => (bool) $m->is_verified,
                'roles' => $m->roles->pluck('role_name')->all(),
                'created_at' => $m->created_at->format('M d, Y'),
            ]);

        return Inertia::render('members/Index', [
            'members' => $members,
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
            'statusOptions' => array_column(MembershipStatus::cases(), 'value'),
        ]);
    }

    public function create()
    {
        return Inertia::render('members/Invite');
    }

    public function store(\App\Http\Requests\InviteMemberRequest $request, \App\Services\InvitationService $invitations)
    {
        $invitations->invite($request->validated('email'), $request->validated('student_number'));

        return redirect()->route('members.index')->with('status', 'Invitation sent successfully.');
    }

    public function show(Member $member)
    {
        $member->load(['roles', 'invitations' => fn ($q) => $q->latest(), 'payments' => fn ($q) => $q->latest(), 'idCards' => fn ($q) => $q->latest()]);

        return Inertia::render('members/Show', [
            'member' => [
                'member_id' => $member->member_id,
                'email' => $member->email,
                'student_number' => $member->student_number,
                'username' => $member->username,
                'membership_status' => is_object($member->membership_status) ? $member->membership_status->value : $member->membership_status,
                'is_verified' => (bool) $member->is_verified,
                'roles' => $member->roles->pluck('role_name')->all(),
                'created_at' => $member->created_at->format('M d, Y H:i'),
            ],
            'invitations' => $member->invitations->map(fn ($i) => [
                'invitation_id' => $i->invitation_id,
                'status' => is_object($i->status) ? $i->status->value : $i->status,
                'expires_at' => $i->expires_at->format('M d, Y H:i'),
                'submitted_at' => $i->submitted_at?->format('M d, Y H:i'),
                'created_at' => $i->created_at->format('M d, Y H:i'),
            ]),
            'payments' => $member->payments->map(fn ($p) => [
                'payment_id' => $p->payment_id,
                'payment_type' => is_object($p->payment_type) ? $p->payment_type->value : $p->payment_type,
                'status' => is_object($p->status) ? $p->status->value : $p->status,
                'amount' => number_format((float) $p->amount, 2),
                'academic_term' => $p->academic_term,
                'provider_reference' => $p->provider_reference,
                'created_at' => $p->created_at->format('M d, Y H:i'),
            ]),
            'latestCard' => $member->idCards->first() ? [
                'id_card_id' => $member->idCards->first()->id_card_id,
                'card_number' => $member->idCards->first()->card_number,
                'is_eligible' => (bool) $member->idCards->first()->is_eligible,
            ] : null,
        ]);
    }

    public function suspend(Request $request, Member $member)
    {
        abort_unless($request->user()->hasRole('Admin'), 403);

        $member->update([
            'membership_status' => MembershipStatus::Suspended,
        ]);

        return back()->with('status', "Member {$member->student_number} has been suspended.");
    }

    public function activate(Request $request, Member $member)
    {
        abort_unless($request->user()->hasRole('Admin'), 403);

        $member->update([
            'membership_status' => MembershipStatus::Active,
        ]);

        return back()->with('status', "Member {$member->student_number} has been activated.");
    }
}
