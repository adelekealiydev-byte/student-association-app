<?php

namespace App\Services;

use App\Enums\InvitationStatus;
use App\Enums\MembershipStatus;
use App\Mail\InvitationMail;
use App\Models\Invitation;
use App\Models\Member;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InvitationService
{
    public function invite(string $email, string $studentNumber): Invitation
    {
        $member = Member::create([
            'email' => $email,
            'student_number' => $studentNumber,
            'membership_status' => MembershipStatus::PendingInvite,
        ]);

        return $this->issueFor($member);
    }

    /**
     * Revokes any open invitation, creates a new one and emails the link.
     */
    public function issueFor(Member $member): Invitation
    {
        $plain = Str::random(64);
        $invitation = DB::transaction(function () use ($member, $plain) {
            $member->invitations()->open()->update(['status' => InvitationStatus::Revoked->value]);

            return $member->invitations()->create([
                'invitation_token' => hash('sha256', $plain),
                'status' => InvitationStatus::Pending,
                'expires_at' => now()->addDays(config('association.invitation_ttl_days', 7)),
            ]);
        });

        // The Google Form link carries the plain token in a pre-filled hidden question.
        Mail::to($member->email)->send(new InvitationMail($member, $plain));
        $invitation->update(['status' => InvitationStatus::Sent]);

        return $invitation;
    }

    /**
     * The only place is_verified is ever set to true.
     */
    public function completeForm(string $plainToken, string $username, ?string $password = null): Member
    {
        return DB::transaction(function () use ($plainToken, $username, $password) {
            $invitation = Invitation::where('invitation_token', hash('sha256', $plainToken))
                ->lockForUpdate()
                ->first();

            if (! $invitation || $invitation->status !== InvitationStatus::Sent || $invitation->expires_at->isPast()) {
                throw new DomainException('This invitation is invalid, used or expired.');
            }

            $invitation->update([
                'status' => InvitationStatus::FormSubmitted,
                'submitted_at' => now(),
            ]);

            $member = $invitation->member;
            $member->forceFill([
                'username' => $username,
                'password_hash' => $password, // hashed by the cast
                'is_verified' => true,
                'membership_status' => MembershipStatus::Active,
            ])->save();

            return $member;
        });
    }

    public function expireStale(): int
    {
        return Invitation::open()
            ->where('expires_at', '<', now())
            ->update(['status' => InvitationStatus::Expired->value]);
    }
}

