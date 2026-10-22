<?php

namespace App\Http\Controllers;

use App\Enums\InvitationStatus;
use App\Models\Invitation;
use App\Services\InvitationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvitationManagementController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status');

        $query = Invitation::query()->with('member');

        if ($search) {
            $query->whereHas('member', function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                    ->orWhere('student_number', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        $invitations = $query->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn ($i) => [
                'invitation_id' => $i->invitation_id,
                'status' => is_object($i->status) ? $i->status->value : $i->status,
                'expires_at' => $i->expires_at->format('M d, Y H:i'),
                'is_expired' => $i->expires_at->isPast(),
                'submitted_at' => $i->submitted_at?->format('M d, Y H:i'),
                'created_at' => $i->created_at->format('M d, Y H:i'),
                'member' => $i->member ? [
                    'member_id' => $i->member->member_id,
                    'email' => $i->member->email,
                    'student_number' => $i->member->student_number,
                    'username' => $i->member->username,
                    'is_verified' => (bool) $i->member->is_verified,
                ] : null,
            ]);

        return Inertia::render('invitations/Index', [
            'invitations' => $invitations,
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
            'statusOptions' => array_column(InvitationStatus::cases(), 'value'),
        ]);
    }

    public function revoke(Request $request, Invitation $invitation)
    {
        $invitation->update(['status' => InvitationStatus::Revoked]);

        return back()->with('status', 'Invitation has been revoked.');
    }

    public function resend(Request $request, Invitation $invitation, InvitationService $invitationService)
    {
        if ($invitation->member->is_verified) {
            return back()->with('error', 'This member is already verified.');
        }

        $invitationService->issueFor($invitation->member);

        return back()->with('status', 'A new invitation has been sent.');
    }
}

