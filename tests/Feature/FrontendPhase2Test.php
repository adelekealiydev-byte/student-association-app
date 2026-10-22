<?php

namespace Tests\Feature;

use App\Enums\MembershipStatus;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Models\IdCard;
use App\Models\Member;
use App\Models\Payment;
use App\Models\Role;
use App\Services\IdCardService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class FrontendPhase2Test extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test Dashboard renders with member cards and dues status
     */
    public function test_dashboard_renders_for_member(): void
    {
        $member = Member::factory()->create();

        $response = $this->actingAs($member)->get('/dashboard');

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->has('member.member_id')
                ->has('currentTerm')
                ->has('duesAmount')
                ->where('duesPaid', false)
            );
    }

    /**
     * Test Dashboard provides officer stats for officer roles
     */
    public function test_dashboard_provides_officer_stats_for_treasurer(): void
    {
        $member = Member::factory()->create();
        $treasurerRole = Role::firstOrCreate(['role_name' => 'Treasurer']);
        $member->roles()->attach($treasurerRole->role_id);

        $response = $this->actingAs($member)->get('/dashboard');

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->has('officerStats.total_members')
                ->has('officerStats.term_income')
            );
    }

    /**
     * Test ID Card view when eligible
     */
    public function test_card_view_shows_eligible_card(): void
    {
        $member = Member::factory()->create(); // verified
        $payment = Payment::factory()->create([
            'member_id' => $member->member_id,
            'payment_type' => PaymentType::Dues,
            'status' => PaymentStatus::Completed,
            'academic_term' => '2026-2027',
        ]);
        $card = app(IdCardService::class)->issueFor($payment);

        $response = $this->actingAs($member)->get('/me/card');

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('me/Card')
                ->where('card.is_eligible', true)
                ->where('card.card_number', $card->card_number)
                ->where('lockReason', null)
            );
    }

    /**
     * Test ID Card view shows locked reason when dues are unpaid
     */
    public function test_card_view_shows_locked_reason_when_dues_unpaid(): void
    {
        $member = Member::factory()->create();

        $response = $this->actingAs($member)->get('/me/card');

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('me/Card')
                ->where('lockReason', 'unpaid_dues')
            );
    }

    /**
     * Test Dues view renders payment list and unpaid dues call-to-action
     */
    public function test_dues_view_renders_for_member(): void
    {
        $member = Member::factory()->create();

        $response = $this->actingAs($member)->get('/me/dues');

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('me/Dues')
                ->where('hasCompletedDues', false)
                ->where('currentTerm', '2026-2027')
                ->has('payments')
            );
    }

    /**
     * Test Paying Dues activates the card and redirects to /me/dues
     */
    public function test_paying_dues_completes_payment_and_redirects(): void
    {
        $member = Member::factory()->create();

        $response = $this->actingAs($member)->post('/payments/dues', [
            'academic_term' => '2026-2027',
        ]);

        $response->assertRedirect('/me/dues');
        $this->assertTrue(
            $member->payments()
                ->where('payment_type', PaymentType::Dues)
                ->where('status', PaymentStatus::Completed)
                ->where('academic_term', '2026-2027')
                ->exists()
        );

        $card = $member->idCards()->latest()->first();
        $this->assertNotNull($card);
        $this->assertTrue($card->is_eligible);
    }

    /**
     * Test Membership profile page
     */
    public function test_membership_profile_renders(): void
    {
        $member = Member::factory()->create();

        $response = $this->actingAs($member)->get('/me/membership');

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('me/Membership')
                ->where('member.email', $member->email)
                ->where('member.student_number', $member->student_number)
            );
    }

    /**
     * Test Membership profile update
     */
    public function test_membership_profile_can_update_username(): void
    {
        $member = Member::factory()->create(['username' => 'original_name']);
        $newUsername = 'new_user_'.rand(1000, 9999);

        $response = $this->actingAs($member)->patch('/me/membership', [
            'username' => $newUsername,
        ]);

        $response->assertRedirect();
        $this->assertEquals($newUsername, $member->fresh()->username);
    }

    /**
     * Test Settings page and Password update
     */
    public function test_member_can_update_password_in_settings(): void
    {
        $member = Member::factory()->create([
            'password_hash' => Hash::make('CurrentPassword123!'),
        ]);

        $response = $this->actingAs($member)->get('/me/settings');
        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('me/Settings'));

        $updateResponse = $this->actingAs($member)->put('/me/settings/password', [
            'current_password' => 'CurrentPassword123!',
            'password' => 'BrandNewPassword123!',
            'password_confirmation' => 'BrandNewPassword123!',
        ]);

        $updateResponse->assertRedirect();
        $this->assertTrue(Hash::check('BrandNewPassword123!', $member->fresh()->password_hash));
    }
}

