<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login', [
            'registrationOpen' => true,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $key = Str::transliterate(Str::lower($credentials['email']).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'email' => __('app.login.too_many_attempts', [
                    'seconds' => RateLimiter::availableIn($key),
                ]),
            ]);
        }

        $user = User::whereRaw('LOWER(email) = ?', [Str::lower($credentials['email'])])->first();

        if (! $user || ! \Illuminate\Support\Facades\Hash::check($credentials['password'], $user->password)) {
            RateLimiter::hit($key, 60);

            throw ValidationException::withMessages([
                'email' => __('app.login.invalid_credentials'),
            ]);
        }

        RateLimiter::clear($key);

        if ($user->two_factor_enabled) {
            $request->session()->put('two_factor_user_id', $user->id);
            $request->session()->put('two_factor_remember', (bool) ($credentials['remember'] ?? false));

            TwoFactorChallengeController::sendTwoFactorCode($user);

            return redirect()->route('two-factor.challenge');
        }

        Auth::login($user, (bool) ($credentials['remember'] ?? false));
        $request->session()->regenerate();

        return redirect()->intended(route('issueboard.index'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
