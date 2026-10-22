<?php

namespace App\Http\Controllers;

use App\Enums\MembershipStatus;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Models\Member;
use App\Models\Payment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinanceController extends Controller
{
    protected string $defaultTerm = '2026-2027';

    public function index(Request $request)
    {
        $term = $request->query('academic_term', $this->defaultTerm);

        $availableTerms = Payment::select('academic_term')
            ->distinct()
            ->pluck('academic_term')
            ->filter()
            ->push($this->defaultTerm)
            ->unique()
            ->values();

        // Income grouped by payment type for the term
        $typeBreakdown = Payment::where('status', PaymentStatus::Completed)
            ->where('academic_term', $term)
            ->selectRaw('payment_type, count(*) as count, sum(amount) as total')
            ->groupBy('payment_type')
            ->get()
            ->map(fn ($row) => [
                'type' => is_object($row->payment_type) ? $row->payment_type->value : $row->payment_type,
                'count' => $row->count,
                'total' => number_format((float) $row->total, 2),
                'raw_total' => (float) $row->total,
            ]);

        $totalRevenue = Payment::where('status', PaymentStatus::Completed)
            ->where('academic_term', $term)
            ->sum('amount');

        $totalCompleted = Payment::where('status', PaymentStatus::Completed)
            ->where('academic_term', $term)
            ->count();

        // Dues completion vs unpaid
        $duesPaidCount = Member::whereHas('payments', function ($q) use ($term) {
            $q->where('payment_type', PaymentType::Dues)
                ->where('status', PaymentStatus::Completed)
                ->where('academic_term', $term);
        })->where('is_verified', true)->count();

        $verifiedMembersCount = Member::where('is_verified', true)->count();
        $unpaidDuesCount = max(0, $verifiedMembersCount - $duesPaidCount);

        // Recent 10 payments
        $recentPayments = Payment::with('member')
            ->latest()
            ->take(10)
            ->get()
            ->map(fn ($p) => [
                'payment_id' => $p->payment_id,
                'student_number' => $p->member?->student_number,
                'email' => $p->member?->email,
                'payment_type' => is_object($p->payment_type) ? $p->payment_type->value : $p->payment_type,
                'status' => is_object($p->status) ? $p->status->value : $p->status,
                'amount' => number_format((float) $p->amount, 2),
                'academic_term' => $p->academic_term,
                'provider_reference' => $p->provider_reference,
                'created_at' => $p->created_at->format('M d, Y H:i'),
            ]);

        return Inertia::render('finance/Index', [
            'selectedTerm' => $term,
            'availableTerms' => $availableTerms,
            'summary' => [
                'total_revenue' => number_format((float) $totalRevenue, 2),
                'total_completed' => $totalCompleted,
                'dues_paid_count' => $duesPaidCount,
                'unpaid_dues_count' => $unpaidDuesCount,
                'type_breakdown' => $typeBreakdown,
            ],
            'recentPayments' => $recentPayments,
        ]);
    }

    public function unpaid(Request $request)
    {
        $term = $request->query('academic_term', $this->defaultTerm);
        $search = $request->query('search');

        $query = Member::query()
            ->where('is_verified', true)
            ->where('membership_status', MembershipStatus::Active)
            ->whereDoesntHave('payments', function ($q) use ($term) {
                $q->where('payment_type', PaymentType::Dues)
                    ->where('status', PaymentStatus::Completed)
                    ->where('academic_term', $term);
            });

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                    ->orWhere('student_number', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }

        $members = $query->orderBy('student_number')
            ->paginate(20)
            ->withQueryString()
            ->through(fn ($m) => [
                'member_id' => $m->member_id,
                'student_number' => $m->student_number,
                'email' => $m->email,
                'username' => $m->username ?? 'N/A',
                'membership_status' => is_object($m->membership_status) ? $m->membership_status->value : $m->membership_status,
            ]);

        return Inertia::render('finance/Unpaid', [
            'members' => $members,
            'selectedTerm' => $term,
            'filters' => [
                'search' => $search,
                'academic_term' => $term,
            ],
            'duesAmount' => config('association.dues_amount', '25.00'),
        ]);
    }

    public function exportUnpaid(Request $request): StreamedResponse
    {
        $term = $request->query('academic_term', $this->defaultTerm);

        $members = Member::query()
            ->where('is_verified', true)
            ->where('membership_status', MembershipStatus::Active)
            ->whereDoesntHave('payments', function ($q) use ($term) {
                $q->where('payment_type', PaymentType::Dues)
                    ->where('status', PaymentStatus::Completed)
                    ->where('academic_term', $term);
            })
            ->orderBy('student_number')
            ->get(['username', 'student_number', 'email']);

        $filename = "unpaid-dues-{$term}.csv";

        return response()->streamDownload(function () use ($members) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Name', 'Student Number', 'Email']);

            foreach ($members as $member) {
                fputcsv($handle, [
                    $member->username ?? 'N/A',
                    $member->student_number,
                    $member->email,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }

    public function payments(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status');
        $type = $request->query('payment_type');
        $term = $request->query('academic_term');

        $query = Payment::query()->with('member');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('provider_reference', 'like', "%{$search}%")
                    ->orWhereHas('member', function ($mq) use ($search) {
                        $mq->where('email', 'like', "%{$search}%")
                            ->orWhere('student_number', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%");
                    });
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($type) {
            $query->where('payment_type', $type);
        }

        if ($term) {
            $query->where('academic_term', $term);
        }

        $payments = $query->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn ($p) => [
                'payment_id' => $p->payment_id,
                'payment_type' => is_object($p->payment_type) ? $p->payment_type->value : $p->payment_type,
                'status' => is_object($p->status) ? $p->status->value : $p->status,
                'amount' => number_format((float) $p->amount, 2),
                'academic_term' => $p->academic_term,
                'provider_reference' => $p->provider_reference,
                'created_at' => $p->created_at->format('M d, Y H:i'),
                'member' => $p->member ? [
                    'member_id' => $p->member->member_id,
                    'student_number' => $p->member->student_number,
                    'email' => $p->member->email,
                    'username' => $p->member->username,
                ] : null,
            ]);

        return Inertia::render('finance/Payments', [
            'payments' => $payments,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'payment_type' => $type,
                'academic_term' => $term,
            ],
            'statusOptions' => array_column(PaymentStatus::cases(), 'value'),
            'typeOptions' => array_column(PaymentType::cases(), 'value'),
        ]);
    }
}

