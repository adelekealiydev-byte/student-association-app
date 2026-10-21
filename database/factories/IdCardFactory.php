<?php

namespace Database\Factories;

use App\Models\IdCard;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\IdCard>
 */
class IdCardFactory extends Factory
{
    protected $model = IdCard::class;

    public function definition(): array
    {
        return [
            'payment_id' => Payment::factory(),
            // The card's member must own the payment; the trigger enforces it.
            'member_id' => fn (array $attrs) => Payment::find($attrs['payment_id'])->member_id,
            'card_number' => 'CARD-' . fake()->unique()->numerify('######'),
            'qr_code_data' => fake()->uuid(),
        ];
    }
}

