<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Models\IdCard;
use App\Models\Payment;
use DomainException;
use Illuminate\Support\Str;

class IdCardService
{
    public function issueFor(Payment $payment): IdCard
    {
        if ($payment->payment_type !== PaymentType::Dues || $payment->status !== PaymentStatus::Completed) {
            throw new DomainException('A card needs a completed Dues payment.');
        }

        if ($existing = $payment->idCard()->first()) {
            return $existing;
        }

        $number = 'SA'.now()->format('y').strtoupper(Str::random(8));
        $card = IdCard::create([
            'member_id' => $payment->member_id,
            'payment_id' => $payment->payment_id,
            'card_number' => $number,
            'qr_code_data' => $number.'.'.hash_hmac('sha256', $number, config('app.key')),
        ]);

        return $card->refresh(); // reads the trigger-computed is_eligible
    }

    /**
     * Scanned QR text in, eligible card out (or null).
     */
    public function verifyQr(string $qr): ?IdCard
    {
        [$number, $signature] = array_pad(explode('.', $qr, 2), 2, '');

        if (! hash_equals(hash_hmac('sha256', $number, config('app.key')), $signature)) {
            return null;
        }

        return IdCard::where('card_number', $number)->where('is_eligible', true)->first();
    }
}

