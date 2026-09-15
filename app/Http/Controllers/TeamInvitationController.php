<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\TeamInvitation;
use App\Models\User;
use App\Notifications\TeamInvitationNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Throwable;

class TeamInvitationController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeTeamManagement($request);

        if (! $request->user()->mailSetting()->exists()) {
            return back()->withErrors(['email' => __('app.team.smtp_required')]);
        }

        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', 'string', 'in:'.implode(',', array_column(UserRole::cases(), 'value'))],
        ]);

        $email = Str::lower(trim($data['email']));

        if (User::whereRaw('LOWER(email) = ?', [$email])->exists()) {
            return back()->withErrors(['email' => __('app.team.invite_existing_user')])->withInput();
        }

        TeamInvitation::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->whereNull('accepted_at')
            ->delete();

        [$invitation, $token] = $this->createInvitation(
            email: $email,
            role: UserRole::from($data['role']),
            invitedBy: $request->user()->getKey(),
        );

        if (! $this->sendInvitation($invitation, $token)) {
            return back()->withErrors(['email' => __('app.team.invitation_send_failed')]);
        }

        return back()->with('status', __('app.team.invitation_sent', ['email' => $email]));
    }

    public function resend(Request $request, TeamInvitation $invitation): RedirectResponse
    {
        $this->authorizeTeamManagement($request);
        abort_if($invitation->accepted_at, 404);

        if (! $request->user()->mailSetting()->exists()) {
            return back()->withErrors(['email' => __('app.team.smtp_required')]);
        }

        $token = Str::random(64);

        $invitation->update([
            'token' => hash('sha256', $token),
            'expires_at' => now()->addDays(config('auth.invitation_expire_days')),
            'invited_by' => $request->user()->getKey(),
        ]);

        if (! $this->sendInvitation($invitation->fresh('inviter'), $token)) {
            return back()->withErrors(['email' => __('app.team.invitation_send_failed')]);
        }

        return back()->with('status', __('app.team.invitation_resent', ['email' => $invitation->email]));
    }

    public function destroy(Request $request, TeamInvitation $invitation): RedirectResponse
    {
        $this->authorizeTeamManagement($request);
        abort_if($invitation->accepted_at, 404);

        $email = $invitation->email;
        $invitation->delete();

        return back()->with('status', __('app.team.invitation_cancelled', ['email' => $email]));
    }

    private function authorizeTeamManagement(Request $request): void
    {
        abort_unless($request->user()->canManageTeam(), 403);
    }

    /**
     * @return array{TeamInvitation, string}
     */
    private function createInvitation(string $email, UserRole $role, int $invitedBy): array
    {
        $token = Str::random(64);
        $invitation = TeamInvitation::create([
            'email' => $email,
            'role' => $role,
            'token' => hash('sha256', $token),
            'invited_by' => $invitedBy,
            'expires_at' => now()->addDays(config('auth.invitation_expire_days')),
        ]);

        return [$invitation->load('inviter'), $token];
    }

    private function sendInvitation(TeamInvitation $invitation, string $token): bool
    {
        try {
            Notification::route('mail', $invitation->email)
                ->notify(new TeamInvitationNotification($invitation, $token));
        } catch (Throwable $exception) {
            Log::warning('Team invitation email failed.', [
                'invitation_id' => $invitation->getKey(),
                'exception' => $exception::class,
            ]);

            return false;
        }

        return true;
    }
}
