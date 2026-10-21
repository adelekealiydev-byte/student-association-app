<?php

namespace Tests\Feature;

use App\Enums\InvitationStatus;
use App\Enums\MembershipStatus;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Mail\InvitationMail;
use App\Models\IdCard;
use App\Models\Invitation;
use App\Models\Member;
use App\Models\Payment;
use App\Models\Role;
use App\Services\InvitationService;
use App\Services\PaymentService;
use DomainException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TransactionRulesTest extends TestCase
{
    use DatabaseTransactions;

    protected function emailedToken(): string
    {
        return Mail::sent(InvitationMail::class)->first()->plainToken;
    }

    /**
     * Test 1: Send invitation - Member starts as Pending Invite and only token hash is stored.
     */
    public function test_starts_a_member_as_pending_invite_and_stores_only_the_token_hash(): void
    {
        Mail::fake();

        $invitation = app(InvitationService::class)->invite('sam@example.org', 'S20260001');

        $this->assertEquals(MembershipStatus::PendingInvite, $invitation->member->membership_status);
        $this->assertFalse($invitation->member->is_verified);
        $this->assertEquals(hash('sha256', $this->emailedToken()), $invitation->invitation_token);
    }

    /**
     * Test 2: One open invitation per member.
     */
    public function test_allows_only_one_open_invitation_per_member(): void
    {
        $member = Member::factory()->pendingInvite()->create();
        Invitation::factory()->create(['member_id' => $member->member_id]);

        $this->expectException(QueryException::class);
        Invitation::factory()->create(['member_id' => $member->member_id]);
    }

    /**
     * Test 3: Complete the Google Form - Valid token verifies member once.
     */
    public function test_verifies_the_member_when_the_form_is_submitted_once(): void
    {
        Mail::fake();

        $service = app(InvitationService::class);
        $invitation = $service->invite('sam2@example.org', 'S20260002');
        $token = $this->emailedToken();

        $member = $service->completeForm($token, 'sam');

        $this->assertTrue($member->fresh()->is_verified);
        $this->assertEquals(InvitationStatus::FormSubmitted, $invitation->fresh()->status);

        $this->expectException(DomainException::class);
        $service->completeForm($token, 'sam2');
    }

    /**
     * Test 4: Complete the Google Form - Rejects expired invitation.
     */
    public function test_rejects_an_expired_invitation(): void
    {
        Mail::fake();

        $service = app(InvitationService::class);
        $invitation = $service->invite('sam3@example.org', 'S20260003');
        $token = $this->emailedToken();
        $invitation->update(['expires_at' => now()->subMinute()]);

        $this->expectException(DomainException::class);
        $service->completeForm($token, 'sam3');
    }

    /**
     * Test 5: Complete the Google Form - Webhook rejects bad signatures.
     */
    public function test_rejects_a_webhook_with_a_bad_signature(): void
    {
        config(['association.webhook_secret' => 'test-secret']);

        $response = $this->postJson('/webhooks/google-form', [
            'token' => str_repeat('a', 64),
            'username' => 'sam',
        ], [
            'X-Signature' => 'wrong',
        ]);

        $response->assertUnauthorized();
    }

    /**
     * Test 6: Pay dues - Issues one eligible card when dues complete for verified member.
     */
    public function test_issues_one_eligible_card_when_dues_complete_for_a_verified_member(): void
    {
        $member = Member::factory()->create(); // verified
        $payment = Payment::factory()->pending()->create(['member_id' => $member->member_id]);

        app(PaymentService::class)->complete($payment, 'ref-1');
        app(PaymentService::class)->complete($payment, 'ref-1'); // repeat is harmless

        $card = IdCard::where('payment_id', $payment->payment_id)->sole();

        $this->assertEquals($member->member_id, $card->member_id);
        $this->assertTrue($card->is_eligible);
    }

    /**
     * Test 7: Print ID card - Keeps card ineligible until member is verified.
     */
    public function test_keeps_the_card_ineligible_until_the_member_is_verified(): void
    {
        $member = Member::factory()->create(['is_verified' => false]);
        $payment = Payment::factory()->create(['member_id' => $member->member_id]);
        $card = IdCard::factory()->create([
            'payment_id' => $payment->payment_id,
            'member_id' => $member->member_id,
        ]);

        $this->assertFalse($card->refresh()->is_eligible);

        $member->forceFill(['is_verified' => true])->save();

        $this->assertTrue($card->refresh()->is_eligible);
    }

    /**
     * Test 8: Print ID card - Switches card off when payment is refunded.
     */
    public function test_switches_the_card_off_when_the_payment_is_refunded(): void
    {
        $card = IdCard::factory()->create();
        $this->assertTrue($card->refresh()->is_eligible);

        $card->payment->update(['status' => PaymentStatus::Refunded]);

        $this->assertFalse($card->refresh()->is_eligible);
    }

    /**
     * Test 9: Print ID card - Never lets is_eligible be set by hand.
     */
    public function test_never_lets_is_eligible_be_set_by_hand(): void
    {
        $payment = Payment::factory()->pending()->create();
        $card = IdCard::factory()->create([
            'payment_id' => $payment->payment_id,
            'member_id' => $payment->member_id,
        ]);

        DB::table('id_cards')->where('id_card_id', $card->id_card_id)->update(['is_eligible' => true]);

        $this->assertFalse($card->refresh()->is_eligible);
    }

    /**
     * Test 10: Print ID card - Rejects card for someone else's payment or non-dues payment.
     */
    public function test_rejects_a_card_for_someone_elses_payment_or_a_non_dues_payment(): void
    {
        $payment = Payment::factory()->create();
        $other = Member::factory()->create();

        $this->expectException(QueryException::class);
        IdCard::factory()->create([
            'payment_id' => $payment->payment_id,
            'member_id' => $other->member_id,
        ]);
    }

    /**
     * Test 11: Pay dues - Allows one completed Dues payment per member per term.
     */
    public function test_allows_one_completed_dues_payment_per_member_per_term(): void
    {
        $member = Member::factory()->create();
        Payment::factory()->create([
            'member_id' => $member->member_id,
            'academic_term' => '2026-2027',
        ]);

        $this->expectException(QueryException::class);
        Payment::factory()->create([
            'member_id' => $member->member_id,
            'academic_term' => '2026-2027',
        ]);
    }

    /**
     * Test 12: Login - Blocks login until member is verified.
     */
    public function test_blocks_login_until_the_member_is_verified(): void
    {
        Member::factory()->pendingInvite()->create([
            'username' => 'sam_pending',
            'password_hash' => 'secret-pass-123',
        ]);

        $response = $this->post('/login', [
            'login' => 'sam_pending',
            'password' => 'secret-pass-123',
        ]);

        $response->assertSessionHasErrors('login');
    }

    /**
     * Test 13: RBAC - Lets only Admin and Secretary invite members.
     */
    public function test_lets_only_admin_and_secretary_invite_members(): void
    {
        $member = Member::factory()->create();
        $payload = ['email' => 'new@example.org', 'student_number' => 'S20269999'];

        $this->actingAs($member)->post('/members/invitations', $payload)->assertForbidden();

        $secretaryRole = Role::firstOrCreate(['role_name' => 'Secretary']);
        $member->roles()->attach($secretaryRole->role_id);

        Mail::fake();

        $this->actingAs($member->fresh())->post('/members/invitations', $payload)->assertRedirect();
    }
}

