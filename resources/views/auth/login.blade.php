<x-layouts.auth :title="__('app.login.title')">
    <div>
        <p class="text-sm font-bold uppercase tracking-[0.18em] text-orange-600">{{ __('app.login.eyebrow') }}</p>
        <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-slate-950">{{ __('app.login.heading') }}</h2>
        <p class="mt-3 text-sm leading-6 text-slate-500">{{ __('app.login.description') }}</p>
    </div>

    <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-5">
        @csrf
        <div>
            <label for="email" class="text-sm font-bold text-slate-700">{{ __('app.login.email') }}</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                   placeholder="{{ __('app.login.email_placeholder') }}"
                   class="mt-2 w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm shadow-sm placeholder:text-slate-400 focus:border-orange-500 focus:ring-orange-500">
            @error('email') <p class="mt-2 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <div class="flex items-center justify-between gap-3">
                <label for="password" class="text-sm font-bold text-slate-700">{{ __('app.login.password') }}</label>
                <span class="text-xs font-medium text-slate-400">{{ __('app.login.password_hint') }}</span>
            </div>
            <input id="password" name="password" type="password" required autocomplete="current-password"
                   placeholder="{{ __('app.login.password_placeholder') }}"
                   class="mt-2 w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm shadow-sm placeholder:text-slate-400 focus:border-orange-500 focus:ring-orange-500">
            @error('password') <p class="mt-2 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
        </div>

        <label class="flex cursor-pointer items-center gap-3 text-sm font-medium text-slate-600">
            <input name="remember" type="checkbox" value="1" class="rounded border-slate-300 text-orange-600 focus:ring-orange-500">
            {{ __('app.login.remember') }}
        </label>

        <button class="flex w-full items-center justify-center rounded-xl bg-slate-950 px-4 py-3.5 text-sm font-extrabold text-white shadow-lg shadow-slate-950/15 transition hover:bg-slate-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-500">
            {{ __('app.login.submit') }}
        </button>
    </form>

    @if ($registrationOpen)
        <p class="mt-7 text-center text-sm text-slate-500">
            {{ __('app.login.new_user') }}
            <a href="{{ route('register') }}" class="font-bold text-orange-600 hover:text-orange-700">{{ __('app.login.create_account') }}</a>
        </p>
    @else
        <p class="mt-7 text-center text-sm leading-6 text-slate-500">{{ __('app.login.invitation_only') }}</p>
    @endif
</x-layouts.auth>
