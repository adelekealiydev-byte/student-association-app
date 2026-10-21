<?php

namespace Database\Factories;

use App\Enums\MembershipStatus;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Member>
 */
class MemberFactory extends Factory
{
    protected $model = Member::class;

    public function definition(): array
    {
        return [
            'email' => fake()->unique()->numerify('member_########@example.org'),
            'student_number' => fake()->unique()->numerify('S########'),
            'username' => fake()->unique()->userName().'_'.fake()->unique()->numerify('####'),
            'password_hash' => 'password', // hashed by the model cast
            'membership_status' => MembershipStatus::Active,
            'is_verified' => true,
        ];
    }

    public function pendingInvite(): static
    {
        return $this->state(fn () => [
            'username' => null,
            'password_hash' => null,
            'membership_status' => MembershipStatus::PendingInvite,
            'is_verified' => false,
        ]);
    }
}

