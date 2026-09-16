<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class InvitationAcceptanceController extends Controller
{
    public function create(string $token): View|Response
    {
        $invitation = $this->findInvitation($token);

        if (! $invitation) {
            return response()->view('auth.invitation-invalid', status: 410);
        }

        $existingUser = User::whereRaw('LOWER(email) = ?', [mb_strtolower($invitation->email)])->first();

        return view('auth.accept-invitation', [
            'invitation' => $invitation,
            'existingUser' => $existingUser,
            'token' => $token,
        ]);
    }

    public function store(Request $request, string $token): RedirectResponse|Response
    {
        $invitation = $this->findInvitation($token);

        if (! $invitation) {
            return response()->view('auth.invitation-invalid', status: 410);
        }

        $existingUser = User::whereRaw('LOWER(email) = ?', [mb_strtolower($invitation->email)])->first();

        if ($existingUser) {
            if (Auth::check() && Auth::id() === $existingUser->id) {
                // User is already logged in as the invited account
            } else {
                $credentials = $request->validate([
                    'password' => ['required', 'string'],
                ]);

                if (! Auth::attempt(['email' => $existingUser->email, 'password' => $credentials['password']])) {
                    return back()->withErrors(['password' => __('app.login.invalid_credentials')])->withInput();
                }
            }

            // Update user to join this team
            $existingUser->update([
                'invited_by' => $invitation->invited_by,
                'role' => $invitation->role,
            ]);

            $invitation->update(['accepted_at' => now()]);
            $request->session()->regenerate();

            return redirect()->route('issueboard.index')
                ->with('status', __('app.invitation.team_joined', ['team' => $invitation->inviter?->name ?? 'workspace']));
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $user = DB::transaction(function () use ($data, $token): User {
            $invitation = TeamInvitation::query()
                ->where('token', hash('sha256', $token))
                ->lockForUpdate()
                ->first();

            abort_if(
                ! $invitation
                || $invitation->accepted_at
                || $invitation->expires_at->isPast(),
                410
            );

            $user = User::create([
                ...$data,
                'email' => $invitation->email,
                'role' => $invitation->role,
                'invited_by' => $invitation->invited_by,
            ]);

            $invitation->update(['accepted_at' => now()]);

            return $user;
        });

        event(new Registered($user));
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('issueboard.index')
            ->with('status', __('app.invitation.account_created'));
    }

    private function findInvitation(string $token): ?TeamInvitation
    {
        return TeamInvitation::query()
            ->where('token', hash('sha256', $token))
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->first();
    }
}
