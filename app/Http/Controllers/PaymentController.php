<?php

namespace App\Http\Controllers;

use App\Enums\PaymentType;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function storeDues(Request $request, PaymentService $payments)
    {
        $request->validate(['academic_term' => ['required', 'string', 'max:20']]);

        $payment = $payments->start(
            $request->user(),
            PaymentType::Dues,
            $request->academic_term,
            config('association.dues_amount', '25.00'), // never trust an amount from the client
        );

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'started',
                'payment_id' => $payment->payment_id,
                'amount' => $payment->amount,
            ]);
        }

        return redirect()->back()->with('status', 'Dues payment initiated.');
    }
}

