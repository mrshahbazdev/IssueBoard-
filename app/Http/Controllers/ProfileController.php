<?php

namespace App\Http\Controllers;

use App\Support\UserSmtpMailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Throwable;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:40'],
        ]);

        $user->update($data);

        return back()->with('status', __('app.profile.updated'));
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validateWithBag('passwordUpdate', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $request->user()->update(['password' => $data['password']]);

        return back()->with('status', __('app.profile.password_updated'));
    }

    public function updateSmtp(Request $request): RedirectResponse
    {
        $user = $request->user();
        $setting = $user->mailSetting()->first();

        $data = $request->validateWithBag('smtpUpdate', [
            'host' => ['required', 'string', 'max:255', 'not_regex:/[\\s\\/]/'],
            'port' => ['required', 'integer', 'between:1,65535'],
            'scheme' => ['required', Rule::in(['smtp', 'smtps'])],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => [Rule::requiredIf(! $setting), 'nullable', 'string', 'max:1000'],
            'from_address' => ['required', 'email', 'max:255'],
            'from_name' => ['nullable', 'string', 'max:255'],
        ]);

        if (blank($data['password'])) {
            unset($data['password']);
        }

        $user->mailSetting()->updateOrCreate([], $data);

        return back()->with('status', __('app.profile.smtp_updated'));
    }

    public function testSmtp(Request $request, UserSmtpMailer $smtpMailer): RedirectResponse
    {
        $user = $request->user()->load('mailSetting');
        $setting = $user->mailSetting;

        // If credentials are submitted with the test request, validate and save them first
        if ($request->filled('host')) {
            $data = $request->validateWithBag('smtpUpdate', [
                'host' => ['required', 'string', 'max:255', 'not_regex:/[\\s\\/]/'],
                'port' => ['required', 'integer', 'between:1,65535'],
                'scheme' => ['required', Rule::in(['smtp', 'smtps'])],
                'username' => ['nullable', 'string', 'max:255'],
                'password' => [Rule::requiredIf(! $setting), 'nullable', 'string', 'max:1000'],
                'from_address' => ['required', 'email', 'max:255'],
                'from_name' => ['nullable', 'string', 'max:255'],
            ]);

            if (blank($data['password'])) {
                unset($data['password']);
            }

            $user->mailSetting()->updateOrCreate([], $data);
            $user->load('mailSetting');
        }

        $mailer = $smtpMailer->mailerFor($user);

        if (! $mailer) {
            return back()->withErrors(
                ['smtp' => __('app.profile.smtp_missing')],
                'smtpUpdate'
            );
        }

        $toEmail = $request->input('test_email', $user->email);
        if (! filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            $toEmail = $user->email;
        }

        try {
            Mail::mailer($mailer)->raw(
                __('app.profile.smtp_test_body'),
                fn ($message) => $message
                    ->to($toEmail)
                    ->from(
                        $user->mailSetting->from_address,
                        $user->mailSetting->from_name ?: $user->name
                    )
                    ->subject(__('app.profile.smtp_test_subject'))
            );
        } catch (Throwable $exception) {
            Log::warning('User SMTP test failed.', [
                'user_id' => $user->getKey(),
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return back()->withErrors(
                ['smtp' => __('app.profile.smtp_test_failed') . ' (' . $exception->getMessage() . ')'],
                'smtpUpdate'
            );
        }

        return back()->with('status', __('app.profile.smtp_test_sent', ['email' => $toEmail]));
    }

    public function destroySmtp(Request $request): RedirectResponse
    {
        $request->user()->mailSetting()->delete();

        return back()->with('status', __('app.profile.smtp_removed'));
    }
}
