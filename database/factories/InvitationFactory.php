<?php

namespace Database\Factories;

use App\Enums\InvitationStatus;
use App\Models\Invitation;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invitation>
 */
class InvitationFactory extends Factory
{
    protected $model = Invitation::class;

    public function definition(): array
    {
        return [
            'member_id' => Member::factory()->pendingInvite(),
            'invitation_token' => hash('sha256', Str::random(64)),
            'status' => InvitationStatus::Sent,
            'expires_at' => now()->addDays(7),
            'submitted_at' => null,
        ];
    }

    public function submitted(): static
    {
        return $this->state(fn () => [
            'status' => InvitationStatus::FormSubmitted,
            'submitted_at' => now(),
        ]);
    }
}

