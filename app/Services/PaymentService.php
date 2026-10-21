<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Models\Member;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(private IdCardService $cards) {}

    public function start(Member $member, PaymentType $type, string $term, string $amount, ?int $eventId = null): Payment
    {
        return $member->payments()->create([
            'payment_type' => $type,
            'status' => PaymentStatus::Pending,
            'amount' => $amount,
            'academic_term' => $term,
            'event_id' => $eventId,
        ]);
    }

    /**
     * Called from the payment provider's webhook; safe to call twice.
     */
    public function complete(Payment $payment, string $providerReference): Payment
    {
        return DB::transaction(function () use ($payment, $providerReference) {
            $payment = Payment::whereKey($payment->payment_id)->lockForUpdate()->firstOrFail();

            if ($payment->status === PaymentStatus::Completed) {
                return $payment;
            }

            $payment->update([
                'status' => PaymentStatus::Completed,
                'provider_reference' => $providerReference,
            ]);

            if ($payment->payment_type === PaymentType::Dues) {
                $this->cards->issueFor($payment);
            }

            return $payment;
        });
    }
}

