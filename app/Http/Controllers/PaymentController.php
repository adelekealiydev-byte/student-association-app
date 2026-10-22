<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentController extends Controller
{
    public function showDues(Request $request)
    {
        $member = $request->user();
        $term = '2026-2027';

        $hasCompletedDues = $member->payments()
            ->where('payment_type', PaymentType::Dues)
            ->where('status', PaymentStatus::Completed)
            ->where('academic_term', $term)
            ->exists();

        $payments = $member->payments()
            ->latest()
            ->get()
            ->map(fn ($p) => [
                'payment_id' => $p->payment_id,
                'payment_type' => is_object($p->payment_type) ? $p->payment_type->value : $p->payment_type,
                'status' => is_object($p->status) ? $p->status->value : $p->status,
                'amount' => number_format((float) $p->amount, 2),
                'academic_term' => $p->academic_term,
                'provider_reference' => $p->provider_reference,
                'created_at' => $p->created_at->format('M d, Y H:i'),
            ]);

        return Inertia::render('me/Dues', [
            'hasCompletedDues' => $hasCompletedDues,
            'currentTerm' => $term,
            'duesAmount' => config('association.dues_amount', '25.00'),
            'payments' => $payments,
        ]);
    }

    public function storeDues(Request $request, PaymentService $payments)
    {
        $request->validate(['academic_term' => ['required', 'string', 'max:20']]);

        $payment = $payments->start(
            $request->user(),
            PaymentType::Dues,
            $request->academic_term,
            config('association.dues_amount', '25.00'),
        );

        // Complete payment (in a mock/direct checkout flow, or redirect to provider)
        $ref = 'PAY-'.strtoupper(uniqid());
        $payments->complete($payment, $ref);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'completed',
                'payment_id' => $payment->payment_id,
                'amount' => $payment->amount,
                'provider_reference' => $ref,
            ]);
        }

        return redirect()->route('me.dues')->with('status', 'Dues paid successfully. Your ID card is now active!');
    }
}
