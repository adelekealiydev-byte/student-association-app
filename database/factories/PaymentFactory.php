<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Models\Member;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'member_id' => Member::factory(),
            'event_id' => null,
            'payment_type' => PaymentType::Dues,
            'status' => PaymentStatus::Completed,
            'amount' => 25.00,
            'academic_term' => '2026-2027',
            'provider_reference' => fake()->unique()->uuid(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => PaymentStatus::Pending,
        ]);
    }

    public function merch(): static
    {
        return $this->state(fn () => [
            'payment_type' => PaymentType::Merch,
            'amount' => 15.00,
        ]);
    }
}

