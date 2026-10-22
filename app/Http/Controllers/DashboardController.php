<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Models\Invitation;
use App\Models\Member;
use App\Models\Payment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $member = $request->user();
        $term = '2026-2027';

        // Member's own dues and ID card
        $completedDues = $member->payments()
            ->where('payment_type', PaymentType::Dues)
            ->where('status', PaymentStatus::Completed)
            ->where('academic_term', $term)
            ->first();

        $latestCard = $member->idCards()->latest()->first();

        // Role-based widgets (from view_configurations)
        $widgets = method_exists($member, 'dashboardWidgets')
            ? $member->dashboardWidgets('dashboard')
            : [];

        // Officer statistics if user has admin/secretary/treasurer roles (Section 7.1)
        $officerStats = null;
        if ($member->hasRole('Admin', 'Secretary', 'Treasurer')) {
            $officerStats = [
                'total_members' => Member::count(),
                'verified_members' => Member::where('is_verified', true)->count(),
                'open_invitations' => Invitation::open()->count(),
                'term_income' => (float) Payment::where('status', PaymentStatus::Completed)
                    ->where('academic_term', $term)
                    ->sum('amount'),
            ];
        }

        return Inertia::render('Dashboard', [
            'member' => [
                'member_id' => $member->member_id,
                'email' => $member->email,
                'student_number' => $member->student_number,
                'username' => $member->username,
                'membership_status' => is_object($member->membership_status) ? $member->membership_status->value : $member->membership_status,
                'is_verified' => (bool) $member->is_verified,
                'roles' => $member->roles()->pluck('role_name')->all(),
            ],
            'currentTerm' => $term,
            'duesPaid' => $completedDues !== null,
            'duesAmount' => config('association.dues_amount', '25.00'),
            'card' => $latestCard ? [
                'id_card_id' => $latestCard->id_card_id,
                'card_number' => $latestCard->card_number,
                'qr_code_data' => $latestCard->qr_code_data,
                'is_eligible' => (bool) $latestCard->is_eligible,
            ] : null,
            'widgets' => $widgets,
            'officerStats' => $officerStats,
        ]);
    }
}
