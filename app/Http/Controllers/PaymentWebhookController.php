<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentWebhookController extends Controller
{
    public function __invoke(Request $request, PaymentService $payments)
    {
        $data = $request->validate([
            'payment_id' => ['required', 'integer'],
            'provider_reference' => ['required', 'string'],
        ]);

        $payment = Payment::findOrFail($data['payment_id']);
        $completed = $payments->complete($payment, $data['provider_reference']);

        return response()->json([
            'status' => 'success',
            'payment_id' => $completed->payment_id,
        ]);
    }
}

