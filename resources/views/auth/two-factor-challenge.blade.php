<x-layouts.auth :title="__('Two-Factor Authentication')">
    <div x-data="{ recovery: false }">
        <div class="flex items-center gap-3">
            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-orange-100 text-orange-600">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
            </span>
            <span class="inline-flex rounded-full bg-orange-100 px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wide text-orange-800">
                2FA Security
            </span>
        </div>

        <h2 class="mt-5 text-2xl font-extrabold tracking-tight text-slate-950">
            <span x-show="!recovery">{{ __('Verify your identity') }}</span>
            <span x-show="recovery" x-cloak>{{ __('Use emergency recovery code') }}</span>
        </h2>
        
        <p class="mt-2 text-sm leading-6 text-slate-500">
            <span x-show="!recovery">{{ __('Please enter the 6-digit verification code sent to your email.') }}</span>
            <span x-show="recovery" x-cloak>{{ __('Enter one of your emergency recovery codes to access your account.') }}</span>
        </p>

        @if (session('status'))
            <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-semibold text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('two-factor.challenge') }}" class="mt-8 space-y-5">
            @csrf

            <div x-show="!recovery">
                <label for="code" class="text-xs font-bold text-slate-700">{{ __('6-Digit Verification Code') }}</label>
                <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="6"
                       placeholder="••••••"
                       class="mt-2 w-full text-center font-mono text-2xl tracking-[0.5em] rounded-xl border-slate-300 bg-white px-4 py-3.5 shadow-sm placeholder:text-slate-300 focus:border-orange-500 focus:ring-orange-500">
                @error('code') <p class="mt-2 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div x-show="recovery" x-cloak>
                <label for="recovery_code" class="text-xs font-bold text-slate-700">{{ __('Emergency Recovery Code') }}</label>
                <input id="recovery_code" name="recovery_code" type="text"
                       placeholder="e.g. abcd1234ef"
                       class="mt-2 w-full font-mono text-sm rounded-xl border-slate-300 bg-white px-4 py-3 shadow-sm placeholder:text-slate-400 focus:border-orange-500 focus:ring-orange-500">
                @error('recovery_code') <p class="mt-2 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="flex w-full items-center justify-center rounded-xl bg-orange-500 px-4 py-3.5 text-sm font-extrabold text-white shadow-lg shadow-orange-950/15 transition hover:bg-orange-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-500">
                {{ __('Verify & Sign In') }}
            </button>
        </form>

        <div class="mt-6 flex items-center justify-between border-t border-slate-100 pt-4 text-xs font-semibold">
            <button type="button" @click="recovery = !recovery" class="text-slate-500 hover:text-slate-900 transition">
                <span x-show="!recovery">{{ __('Use recovery code instead') }}</span>
                <span x-show="recovery" x-cloak>{{ __('Use email verification code') }}</span>
            </button>

            <form method="POST" action="{{ route('two-factor.resend') }}" x-show="!recovery">
                @csrf
                <button type="submit" class="text-orange-600 hover:text-orange-700 transition">
                    {{ __('Resend Code') }}
                </button>
            </form>
        </div>
    </div>
</x-layouts.auth>
