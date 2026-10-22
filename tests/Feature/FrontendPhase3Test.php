<?php

namespace Tests\Feature;

use App\Enums\InvitationStatus;
use App\Enums\MembershipStatus;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Models\Invitation;
use App\Models\Member;
use App\Models\Payment;
use App\Models\Role;
use App\Services\InvitationService;
use App\Services\PaymentService;
use App\Services\RoleService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class FrontendPhase3Test extends TestCase
{
    use DatabaseTransactions;

    protected Role $adminRole;
    protected Role $secretaryRole;
    protected Role $treasurerRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminRole = Role::firstOrCreate(['role_name' => 'Admin']);
        $this->secretaryRole = Role::firstOrCreate(['role_name' => 'Secretary']);
        $this->treasurerRole = Role::firstOrCreate(['role_name' => 'Treasurer']);

        Mail::fake();
    }

    /**
     * RBAC Protection: Guests and regular members are restricted
     */
    public function test_guest_is_redirected_from_officer_routes(): void
    {
        $this->get('/members')->assertRedirect('/login');
        $this->get('/members/invite')->assertRedirect('/login');
        $this->get('/invitations')->assertRedirect('/login');
        $this->get('/roles')->assertRedirect('/login');
        $this->get('/finance')->assertRedirect('/login');
        $this->get('/finance/unpaid')->assertRedirect('/login');
        $this->get('/finance/payments')->assertRedirect('/login');
    }

    public function test_regular_member_forbidden_from_officer_areas(): void
    {
        $member = Member::factory()->create();

        $this->actingAs($member)->get('/members')->assertForbidden();
        $this->actingAs($member)->get('/members/invite')->assertForbidden();
        $this->actingAs($member)->get('/invitations')->assertForbidden();
        $this->actingAs($member)->get('/roles')->assertForbidden();
        $this->actingAs($member)->get('/finance')->assertForbidden();
        $this->actingAs($member)->get('/finance/unpaid')->assertForbidden();
        $this->actingAs($member)->get('/finance/payments')->assertForbidden();
    }

    public function test_secretary_can_access_members_and_invitations_but_not_roles_or_finance(): void
    {
        $secretary = Member::factory()->create();
        $secretary->roles()->attach($this->secretaryRole->role_id);

        $this->actingAs($secretary)->get('/members')->assertOk();
        $this->actingAs($secretary)->get('/members/invite')->assertOk();
        $this->actingAs($secretary)->get('/invitations')->assertOk();

        $this->actingAs($secretary)->get('/roles')->assertForbidden();
        $this->actingAs($secretary)->get('/finance')->assertForbidden();
    }

    public function test_treasurer_can_access_finance_but_not_members_or_roles(): void
    {
        $treasurer = Member::factory()->create();
        $treasurer->roles()->attach($this->treasurerRole->role_id);

        $this->actingAs($treasurer)->get('/finance')->assertOk();
        $this->actingAs($treasurer)->get('/finance/unpaid')->assertOk();
        $this->actingAs($treasurer)->get('/finance/payments')->assertOk();

        $this->actingAs($treasurer)->get('/members')->assertForbidden();
        $this->actingAs($treasurer)->get('/roles')->assertForbidden();
    }

    /**
     * Member Directory & Management
     */
    public function test_admin_can_view_member_directory_with_filters(): void
    {
        $admin = Member::factory()->create();
        $admin->roles()->attach($this->adminRole->role_id);

        $target = Member::factory()->create([
            'email' => 'target.student@university.edu',
            'student_number' => 'S99988877',
            'membership_status' => MembershipStatus::Active,
        ]);

        $response = $this->actingAs($admin)->get('/members?search=S99988877&status=Active');

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('members/Index')
                ->has('members.data')
                ->where('filters.search', 'S99988877')
                ->where('filters.status', 'Active')
            );
    }

    public function test_officer_can_invite_new_member(): void
    {
        $admin = Member::factory()->create();
        $admin->roles()->attach($this->adminRole->role_id);

        $response = $this->actingAs($admin)->post('/members/invite', [
            'student_number' => 'S77889900',
            'email' => 'new.invitee@university.edu',
        ]);

        $response->assertRedirect('/members');

        $this->assertDatabaseHas('members', [
            'student_number' => 'S77889900',
            'email' => 'new.invitee@university.edu',
            'membership_status' => MembershipStatus::PendingInvite->value,
            'is_verified' => false,
        ]);

        $newMember = Member::where('student_number', 'S77889900')->first();
        $this->assertDatabaseHas('invitations', [
            'member_id' => $newMember->member_id,
            'status' => InvitationStatus::Sent->value,
        ]);
    }

    public function test_admin_can_view_member_details_and_history(): void
    {
        $admin = Member::factory()->create();
        $admin->roles()->attach($this->adminRole->role_id);

        $member = Member::factory()->create();
        $invitation = app(InvitationService::class)->issueFor($member);

        $response = $this->actingAs($admin)->get("/members/{$member->member_id}");

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('members/Show')
                ->where('member.member_id', $member->member_id)
                ->has('invitations')
                ->has('payments')
            );
    }

    public function test_admin_can_suspend_and_activate_member(): void
    {
        $admin = Member::factory()->create();
        $admin->roles()->attach($this->adminRole->role_id);

        $member = Member::factory()->create(['membership_status' => MembershipStatus::Active]);

        // Suspend
        $this->actingAs($admin)->post("/members/{$member->member_id}/suspend")
            ->assertSessionHas('status');

        $this->assertEquals(MembershipStatus::Suspended, $member->fresh()->membership_status);

        // Activate
        $this->actingAs($admin)->post("/members/{$member->member_id}/activate")
            ->assertSessionHas('status');

        $this->assertEquals(MembershipStatus::Active, $member->fresh()->membership_status);
    }

    /**
     * Invitation Management
     */
    public function test_officer_can_view_invitations_and_revoke_open_invitation(): void
    {
        $secretary = Member::factory()->create();
        $secretary->roles()->attach($this->secretaryRole->role_id);

        $member = Member::factory()->create(['membership_status' => MembershipStatus::PendingInvite]);
        $invitation = app(InvitationService::class)->issueFor($member);

        $response = $this->actingAs($secretary)->get('/invitations');
        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('invitations/Index')
                ->has('invitations.data')
            );

        // Revoke
        $this->actingAs($secretary)->post("/invitations/{$invitation->invitation_id}/revoke")
            ->assertSessionHas('status');

        $this->assertEquals(InvitationStatus::Revoked, $invitation->fresh()->status);
    }

    public function test_officer_can_resend_invitation_for_unverified_member(): void
    {
        $secretary = Member::factory()->create();
        $secretary->roles()->attach($this->secretaryRole->role_id);

        $member = Member::factory()->create([
            'is_verified' => false,
            'membership_status' => MembershipStatus::PendingInvite,
        ]);
        $invitation = app(InvitationService::class)->issueFor($member);
        $invitation->update(['status' => InvitationStatus::Expired]);

        $this->actingAs($secretary)->post("/invitations/{$invitation->invitation_id}/resend")
            ->assertSessionHas('status');

        $this->assertDatabaseHas('invitations', [
            'member_id' => $member->member_id,
            'status' => InvitationStatus::Sent->value,
        ]);
    }

    /**
     * Role Management & Guards
     */
    public function test_admin_can_view_and_assign_roles(): void
    {
        $admin = Member::factory()->create();
        $admin->roles()->attach($this->adminRole->role_id);

        $target = Member::factory()->create(['is_verified' => true]);

        $response = $this->actingAs($admin)->get('/roles');
        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('roles/Index')
                ->has('roles')
                ->has('membersWithRoles')
                ->has('assignableMembers')
            );

        // Assign Secretary role
        $this->actingAs($admin)->post('/roles', [
            'member_id' => $target->member_id,
            'role_id' => $this->secretaryRole->role_id,
        ])->assertSessionHas('status');

        $this->assertTrue($target->fresh()->hasRole('Secretary'));
    }

    public function test_admin_cannot_remove_the_last_admin(): void
    {
        // Ensure only one admin exists
        \Illuminate\Support\Facades\DB::table('member_roles')->where('role_id', $this->adminRole->role_id)->delete();

        $soleAdmin = Member::factory()->create();
        $soleAdmin->roles()->attach($this->adminRole->role_id);

        $this->actingAs($soleAdmin)
            ->delete("/members/{$soleAdmin->member_id}/roles/{$this->adminRole->role_id}")
            ->assertSessionHasErrors(['role']);

        $this->assertTrue($soleAdmin->fresh()->hasRole('Admin'));
    }

    public function test_admin_can_remove_admin_role_if_another_admin_exists(): void
    {
        $admin1 = Member::factory()->create();
        $admin1->roles()->attach($this->adminRole->role_id);

        $admin2 = Member::factory()->create();
        $admin2->roles()->attach($this->adminRole->role_id);

        $this->actingAs($admin1)
            ->delete("/members/{$admin2->member_id}/roles/{$this->adminRole->role_id}")
            ->assertSessionHas('status');

        $this->assertFalse($admin2->fresh()->hasRole('Admin'));
        $this->assertTrue($admin1->fresh()->hasRole('Admin'));
    }

    /**
     * Finance Views & CSV Export
     */
    public function test_treasurer_can_view_finance_dashboard(): void
    {
        $treasurer = Member::factory()->create();
        $treasurer->roles()->attach($this->treasurerRole->role_id);

        $payer = Member::factory()->create(['is_verified' => true]);
        $payment = app(PaymentService::class)->start(
            $payer,
            PaymentType::Dues,
            '2026-2027',
            '25.00'
        );
        app(PaymentService::class)->complete($payment, 'PAY-FIN-12345');

        $response = $this->actingAs($treasurer)->get('/finance?academic_term=2026-2027');

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('finance/Index')
                ->where('selectedTerm', '2026-2027')
                ->has('summary.total_revenue')
                ->has('summary.type_breakdown')
                ->has('recentPayments')
            );
    }

    public function test_treasurer_can_view_unpaid_dues_list(): void
    {
        $treasurer = Member::factory()->create();
        $treasurer->roles()->attach($this->treasurerRole->role_id);

        $unpaidMember = Member::factory()->create([
            'is_verified' => true,
            'membership_status' => MembershipStatus::Active,
            'student_number' => 'S11223344',
        ]);

        $response = $this->actingAs($treasurer)->get('/finance/unpaid?academic_term=2026-2027');

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('finance/Unpaid')
                ->has('members.data')
                ->where('selectedTerm', '2026-2027')
            );
    }

    public function test_treasurer_can_export_unpaid_members_csv(): void
    {
        $treasurer = Member::factory()->create();
        $treasurer->roles()->attach($this->treasurerRole->role_id);

        $unpaidMember = Member::factory()->create([
            'is_verified' => true,
            'membership_status' => MembershipStatus::Active,
            'student_number' => 'S88776655',
            'email' => 'unpaid.student@university.edu',
            'username' => 'unpaid_student',
        ]);

        $response = $this->actingAs($treasurer)->get('/finance/unpaid/export?academic_term=2026-2027');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('attachment; filename=unpaid-dues-2026-2027.csv', $response->headers->get('content-disposition'));

        $streamedContent = $response->streamedContent();
        $this->assertStringContainsString('Student Number', $streamedContent);
        $this->assertStringContainsString('Email', $streamedContent);
        $this->assertStringContainsString('unpaid_student,S88776655,unpaid.student@university.edu', $streamedContent);
    }

    public function test_treasurer_can_filter_payments_list(): void
    {
        $treasurer = Member::factory()->create();
        $treasurer->roles()->attach($this->treasurerRole->role_id);

        $payer = Member::factory()->create();
        $payment = app(PaymentService::class)->start(
            $payer,
            PaymentType::Dues,
            '2026-2027',
            '25.00'
        );
        app(PaymentService::class)->complete($payment, 'PAY-FILTER-999');

        $response = $this->actingAs($treasurer)->get('/finance/payments?search=PAY-FILTER-999&status=Completed');

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('finance/Payments')
                ->has('payments.data')
                ->where('filters.search', 'PAY-FILTER-999')
                ->where('filters.status', 'Completed')
            );
    }
}
