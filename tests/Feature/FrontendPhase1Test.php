<?php

namespace Tests\Feature;

use App\Enums\MembershipStatus;
use App\Models\Member;
use App\Models\Role;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class FrontendPhase1Test extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test Public Landing Page (Section 4.1, 5.1, 6)
     */
    public function test_landing_page_renders_with_welcome_component(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Welcome')
            );
    }

    /**
     * Test Help Page (Section 4.1, 6)
     */
    public function test_help_page_renders_with_help_component(): void
    {
        $response = $this->get('/help');

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Help')
            );
    }

    /**
     * Test Join Expired Page (Section 4.1, 6, 7.2)
     */
    public function test_join_expired_page_renders(): void
    {
        $response = $this->get('/join/expired');

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('auth/JoinExpired')
            );
    }

    /**
     * Test Login Page renders (Section 6, 7.3)
     */
    public function test_login_page_renders(): void
    {
        $response = $this->get('/login');

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('auth/Login')
            );
    }

    /**
     * Test Login: Invalid credentials error (Section 7.3 rule 3)
     */
    public function test_login_fails_with_generic_message_on_wrong_credentials(): void
    {
        $response = $this->post('/login', [
            'login' => 'nonexistent@example.org',
            'password' => 'wrong_password_123',
        ]);

        $response->assertSessionHasErrors([
            'login' => 'These credentials do not match our records.',
        ]);
    }

    /**
     * Test Login: Unverified account error message (Section 7.3 rule 4)
     */
    public function test_login_shows_not_active_message_for_unverified_member(): void
    {
        $member = Member::factory()->pendingInvite()->create([
            'email' => 'unverified_phase1@example.org',
            'username' => 'unverified_p1',
            'password_hash' => Hash::make('password12345'),
            'is_verified' => false,
        ]);

        $response = $this->post('/login', [
            'login' => 'unverified_phase1@example.org',
            'password' => 'password12345',
        ]);

        $response->assertSessionHasErrors([
            'login' => 'This account is not active yet. Complete your invitation form first.',
        ]);
    }

    /**
     * Test Login: Suspended account error message (Section 7.3 rule 5)
     */
    public function test_login_shows_suspended_message_for_suspended_member(): void
    {
        $member = Member::factory()->create([
            'email' => 'suspended_phase1@example.org',
            'password_hash' => Hash::make('password12345'),
            'membership_status' => MembershipStatus::Suspended,
            'is_verified' => true,
        ]);

        $response = $this->post('/login', [
            'login' => 'suspended_phase1@example.org',
            'password' => 'password12345',
        ]);

        $response->assertSessionHasErrors([
            'login' => 'This account is suspended. Contact the office.',
        ]);
    }

    /**
     * Test Login: Successful authentication and redirect (Section 7.3 rule 2)
     */
    public function test_login_succeeds_for_verified_active_member(): void
    {
        $member = Member::factory()->create([
            'email' => 'active_phase1@example.org',
            'username' => 'active_p1',
            'password_hash' => Hash::make('password12345'),
            'membership_status' => MembershipStatus::Active,
            'is_verified' => true,
        ]);

        $response = $this->post('/login', [
            'login' => 'active_phase1@example.org',
            'password' => 'password12345',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($member);
    }

    /**
     * Test Password Setup: Signed URL allows viewing form (Section 7.2)
     */
    public function test_password_setup_renders_with_signed_url(): void
    {
        $member = Member::factory()->pendingInvite()->create([
            'password_hash' => null,
            'is_verified' => false,
        ]);

        $signedUrl = URL::temporarySignedRoute(
            'password.setup',
            now()->addHours(24),
            ['member' => $member->member_id]
        );

        $response = $this->get($signedUrl);

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('auth/PasswordSetup')
                ->where('alreadySet', false)
                ->has('member.member_id')
            );
    }

    /**
     * Test Password Setup: Sets password and redirects to login (Section 7.2)
     */
    public function test_password_setup_stores_password_and_redirects(): void
    {
        $member = Member::factory()->pendingInvite()->create([
            'password_hash' => null,
            'is_verified' => false,
        ]);

        $signedUrl = URL::temporarySignedRoute(
            'password.setup',
            now()->addHours(24),
            ['member' => $member->member_id]
        );

        $response = $this->post($signedUrl, [
            'password' => 'NewSecurePassword123!',
            'password_confirmation' => 'NewSecurePassword123!',
        ]);

        $response->assertRedirect('/login');
        $this->assertTrue(Hash::check('NewSecurePassword123!', $member->fresh()->password_hash));
    }

    /**
     * Test Password Setup: Detects if password already set (Section 7.2 exception)
     */
    public function test_password_setup_shows_already_set_when_hash_exists(): void
    {
        $member = Member::factory()->create([
            'password_hash' => Hash::make('already_set_123'),
        ]);

        $signedUrl = URL::temporarySignedRoute(
            'password.setup',
            now()->addHours(24),
            ['member' => $member->member_id]
        );

        $response = $this->get($signedUrl);

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('auth/PasswordSetup')
                ->where('alreadySet', true)
            );
    }

    /**
     * Test Shared Auth Props in Authenticated Shell (Section 5.2)
     */
    public function test_authenticated_shell_receives_roles_and_member_data(): void
    {
        $member = Member::factory()->create();
        $adminRole = Role::firstOrCreate(['role_name' => 'Admin']);
        $member->roles()->attach($adminRole->role_id);

        $response = $this->actingAs($member)->get('/dashboard');

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('auth.user', fn (Assert $user) => $user
                    ->where('member_id', $member->member_id)
                    ->where('email', $member->email)
                    ->where('is_verified', true)
                    ->where('roles', ['Admin'])
                    ->etc()
                )
            );
    }
}

