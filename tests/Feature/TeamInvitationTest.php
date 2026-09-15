<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\TeamInvitation;
use App\Models\User;
use App\Notifications\TeamInvitationNotification;
use DateTimeInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class TeamInvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_invite_a_user_with_a_role(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $this->configureSmtp($admin);

        $this->actingAs($admin)
            ->post(route('team.invitations.store'), [
                'email' => 'New.User@example.com',
                'role' => UserRole::Developer->value,
            ])
            ->assertSessionHas('status', 'Invitation sent to new.user@example.com.');

        $invitation = TeamInvitation::sole();
        $this->assertSame('new.user@example.com', $invitation->email);
        $this->assertSame(UserRole::Developer, $invitation->role);
        $this->assertSame($admin->id, $invitation->invited_by);

        Notification::assertSentOnDemand(
            TeamInvitationNotification::class,
            function (TeamInvitationNotification $notification, array $channels, object $notifiable) use ($invitation): bool {
                return $channels === ['mail']
                    && $notifiable->routes['mail'] === $invitation->email
                    && hash('sha256', $notification->token) === $invitation->token
                    && $notification->token !== $invitation->token;
            }
        );
    }

    public function test_invited_user_can_create_an_account_with_the_selected_role(): void
    {
        $token = Str::random(64);
        $invitation = $this->invitation($token, UserRole::Manager);

        $this->get(route('invitations.accept', $token))
            ->assertOk()
            ->assertSee($invitation->email)
            ->assertSee(UserRole::Manager->label());

        $this->post(route('invitations.store', $token), [
            'name' => 'Invited Manager',
            'phone' => '+49 123',
            'password' => 'secure123',
            'password_confirmation' => 'secure123',
        ])->assertRedirect(route('issueboard.index'));

        $user = User::where('email', $invitation->email)->firstOrFail();
        $this->assertSame(UserRole::Manager, $user->role);
        $this->assertNotNull($invitation->refresh()->accepted_at);
        $this->assertAuthenticatedAs($user);
    }

    public function test_expired_and_used_invitation_tokens_cannot_be_used(): void
    {
        $expiredToken = Str::random(64);
        $this->invitation($expiredToken, expiresAt: now()->subMinute());

        $this->get(route('invitations.accept', $expiredToken))
            ->assertStatus(410)
            ->assertSee('This invitation cannot be used');

        $usedToken = Str::random(64);
        $this->invitation($usedToken, acceptedAt: now());

        $this->get(route('invitations.accept', $usedToken))
            ->assertStatus(410);
    }

    public function test_admin_can_resend_and_cancel_pending_invitations(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $this->configureSmtp($admin);
        $invitation = $this->invitation('original-token', invitedBy: $admin);
        $originalHash = $invitation->token;

        $this->actingAs($admin)
            ->post(route('team.invitations.resend', $invitation))
            ->assertSessionHas('status');

        $this->assertNotSame($originalHash, $invitation->refresh()->token);
        Notification::assertSentOnDemand(TeamInvitationNotification::class);

        $this->delete(route('team.invitations.destroy', $invitation))
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('team_invitations', ['id' => $invitation->id]);
    }

    public function test_non_admin_cannot_manage_invitations(): void
    {
        $member = User::factory()->create(['role' => UserRole::Member]);
        $invitation = $this->invitation('member-cannot-manage');

        $this->actingAs($member)
            ->post(route('team.invitations.store'), [
                'email' => 'blocked@example.com',
                'role' => UserRole::Member->value,
            ])
            ->assertForbidden();

        $this->post(route('team.invitations.resend', $invitation))
            ->assertForbidden();

        $this->delete(route('team.invitations.destroy', $invitation))
            ->assertForbidden();
    }

    public function test_invitation_rejects_existing_users_and_requires_sender_smtp(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $existing = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('team.invitations.store'), [
                'email' => 'new@example.com',
                'role' => UserRole::Member->value,
            ])
            ->assertSessionHasErrors('email');

        $this->configureSmtp($admin);

        $this->post(route('team.invitations.store'), [
            'email' => mb_strtoupper($existing->email),
            'role' => UserRole::Member->value,
        ])->assertSessionHasErrors('email');

        $this->assertDatabaseCount('team_invitations', 0);
    }

    public function test_invitation_page_and_email_are_available_in_german(): void
    {
        $token = Str::random(64);
        $invitation = $this->invitation($token);

        $this->withSession(['locale' => 'de'])
            ->get(route('invitations.accept', $token))
            ->assertOk()
            ->assertSee('Dem IssueBoard-Team beitreten');

        app()->setLocale('de');
        $message = (new TeamInvitationNotification($invitation->load('inviter'), $token))
            ->toMail(new \stdClass);

        $this->assertSame('Sie wurden zu IssueBoard eingeladen', $message->subject);
        $this->assertSame('Einladung annehmen', $message->actionText);
    }

    private function configureSmtp(User $user): void
    {
        $user->mailSetting()->create([
            'host' => 'smtp.example.com',
            'port' => 587,
            'scheme' => 'smtp',
            'username' => 'mailer@example.com',
            'password' => 'secret-password',
            'from_address' => 'mailer@example.com',
            'from_name' => 'IssueBoard Mailer',
        ]);
    }

    private function invitation(
        string $token,
        UserRole $role = UserRole::Developer,
        ?User $invitedBy = null,
        ?DateTimeInterface $expiresAt = null,
        ?DateTimeInterface $acceptedAt = null,
    ): TeamInvitation {
        $invitedBy ??= User::factory()->create(['role' => UserRole::Admin]);

        return TeamInvitation::create([
            'email' => fake()->unique()->safeEmail(),
            'role' => $role,
            'token' => hash('sha256', $token),
            'invited_by' => $invitedBy->id,
            'expires_at' => $expiresAt ?? now()->addDays(7),
            'accepted_at' => $acceptedAt,
        ]);
    }
}
