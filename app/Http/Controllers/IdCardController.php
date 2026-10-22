<?php

namespace App\Http\Controllers;

use App\Enums\MembershipStatus;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IdCardController extends Controller
{
    public function show(Request $request)
    {
        $member = $request->user();
        $card = $member->idCards()->latest()->first();
        $term = '2026-2027';

        $hasPaidDues = $member->payments()
            ->where('payment_type', PaymentType::Dues)
            ->where('status', PaymentStatus::Completed)
            ->where('academic_term', $term)
            ->exists();

        // Determine lock reason if card is missing or ineligible (Section 7.5)
        $lockReason = null;
        if (! $card || ! $card->is_eligible) {
            if (! $member->is_verified) {
                $lockReason = 'unverified';
            } elseif ($member->membership_status === MembershipStatus::Suspended) {
                $lockReason = 'suspended';
            } elseif (! $hasPaidDues) {
                $lockReason = 'unpaid_dues';
            } else {
                $lockReason = 'pending_activation';
            }
        }

        return Inertia::render('me/Card', [
            'card' => $card ? [
                'id_card_id' => $card->id_card_id,
                'card_number' => $card->card_number,
                'qr_code_data' => $card->qr_code_data,
                'is_eligible' => (bool) $card->is_eligible,
                'created_at' => $card->created_at->format('M d, Y'),
            ] : null,
            'member' => [
                'student_number' => $member->student_number,
                'username' => $member->username,
                'email' => $member->email,
                'membership_status' => is_object($member->membership_status) ? $member->membership_status->value : $member->membership_status,
                'is_verified' => (bool) $member->is_verified,
            ],
            'academicTerm' => $term,
            'lockReason' => $lockReason,
        ]);
    }
}
