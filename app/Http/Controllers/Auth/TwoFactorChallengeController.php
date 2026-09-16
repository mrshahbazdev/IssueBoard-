<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\UserSmtpMailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TwoFactorChallengeController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('two_factor_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    public function store(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('two_factor_user_id');
        if (! $userId) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($userId);

        if ($request->filled('recovery_code')) {
            $code = trim($request->recovery_code);
            $codes = json_decode($user->two_factor_recovery_codes ?? '[]', true) ?: [];

            if (! in_array($code, $codes, true)) {
                throw ValidationException::withMessages([
                    'recovery_code' => __('Invalid recovery code.'),
                ]);
            }

            // Remove the used recovery code
            $codes = array_values(array_diff($codes, [$code]));
            $user->update(['two_factor_recovery_codes' => json_encode($codes)]);

            $this->completeTwoFactorLogin($request, $user);

            return redirect()->intended(route('issueboard.index'));
        }

        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $hashedInput = hash('sha256', $request->code);

        if (
            ! $user->two_factor_code
            || ! hash_equals($user->two_factor_code, $hashedInput)
            || ! $user->two_factor_expires_at
            || $user->two_factor_expires_at->isPast()
        ) {
            throw ValidationException::withMessages([
                'code' => __('The two-factor code is invalid or has expired.'),
            ]);
        }

        // Clear code after successful verification
        $user->update([
            'two_factor_code' => null,
            'two_factor_expires_at' => null,
        ]);

        $this->completeTwoFactorLogin($request, $user);

        return redirect()->intended(route('issueboard.index'));
    }

    public function resend(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('two_factor_user_id');
        if (! $userId) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($userId);
        $this->sendTwoFactorCode($user);

        return back()->with('status', __('A fresh verification code has been sent to your email.'));
    }

    public static function sendTwoFactorCode(User $user): void
    {
        $code = sprintf('%06d', mt_rand(100000, 999999));
        $user->update([
            'two_factor_code' => hash('sha256', $code),
            'two_factor_expires_at' => now()->addMinutes(10),
        ]);

        try {
            $mail = (new MailMessage)
                ->subject(__('Your IssueBoard verification code: :code', ['code' => $code]))
                ->greeting(__('Hello :name,', ['name' => $user->name]))
                ->line(__('Here is your 6-digit login verification code:'))
                ->line('### ' . $code)
                ->line(__('This code will expire in 10 minutes.'))
                ->line(__('If you did not attempt to sign in, please change your password immediately.'));

            $appliedMail = app(UserSmtpMailer::class)->apply($mail, $user);

            Mail::mailer($appliedMail->mailer ?? config('mail.default'))->send([], [], function ($message) use ($user, $appliedMail) {
                $message->to($user->email)
                    ->subject($appliedMail->subject)
                    ->html($appliedMail->render());
            });
        } catch (\Throwable $e) {
            // Log error but don't crash flow
            report($e);
        }
    }

    protected function completeTwoFactorLogin(Request $request, User $user): void
    {
        $remember = (bool) $request->session()->get('two_factor_remember', false);
        $request->session()->forget(['two_factor_user_id', 'two_factor_remember']);

        Auth::login($user, $remember);
        $request->session()->regenerate();
    }
}
