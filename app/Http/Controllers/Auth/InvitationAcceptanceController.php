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

        if (! $invitation || $this->userExists($invitation->email)) {
            return response()->view('auth.invitation-invalid', status: 410);
        }

        return view('auth.accept-invitation', [
            'invitation' => $invitation,
            'token' => $token,
        ]);
    }

    public function store(Request $request, string $token): RedirectResponse|Response
    {
        $invitation = $this->findInvitation($token);

        if (! $invitation || $this->userExists($invitation->email)) {
            return response()->view('auth.invitation-invalid', status: 410);
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
                || $invitation->expires_at->isPast()
                || $this->userExists($invitation->email),
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

    private function userExists(string $email): bool
    {
        return User::whereRaw('LOWER(email) = ?', [mb_strtolower($email)])->exists();
    }
}
