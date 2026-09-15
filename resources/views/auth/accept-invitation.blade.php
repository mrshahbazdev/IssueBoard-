<x-layouts.auth :title="__('app.invitation.title')">
    <div>
        <div class="flex items-center gap-3">
            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-orange-100 text-orange-600">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M4 6h16v12H4z"/>
                    <path d="m4 7 8 6 8-6"/>
                </svg>
            </span>
            <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wide ring-1 ring-inset {{ $invitation->role->badgeClasses() }}">{{ $invitation->role->label() }}</span>
        </div>
        <p class="mt-5 text-sm font-bold uppercase tracking-[0.18em] text-orange-600">{{ __('app.invitation.eyebrow') }}</p>
        <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-slate-950">{{ __('app.invitation.heading') }}</h2>
        <p class="mt-3 text-sm leading-6 text-slate-500">{{ __('app.invitation.description') }}</p>
    </div>

    <form method="POST" action="{{ route('invitations.store', ['token' => $token]) }}" class="mt-8 space-y-5">
        @csrf
        <div>
            <label class="text-sm font-bold text-slate-700">{{ __('app.invitation.email') }}</label>
            <div class="mt-2 rounded-xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm font-semibold text-slate-600">{{ $invitation->email }}</div>
        </div>

        <div>
            <label for="name" class="text-sm font-bold text-slate-700">{{ __('app.register.name') }}</label>
            <input id="name" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                   placeholder="{{ __('app.register.name_placeholder') }}"
                   class="mt-2 w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm shadow-sm placeholder:text-slate-400 focus:border-orange-500 focus:ring-orange-500">
            @error('name') <p class="mt-2 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="phone" class="text-sm font-bold text-slate-700">{{ __('app.register.phone') }} <span class="font-medium text-slate-400">{{ __('app.register.optional') }}</span></label>
            <input id="phone" name="phone" value="{{ old('phone') }}" autocomplete="tel"
                   placeholder="{{ __('app.register.phone_placeholder') }}"
                   class="mt-2 w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm shadow-sm placeholder:text-slate-400 focus:border-orange-500 focus:ring-orange-500">
            @error('phone') <p class="mt-2 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="password" class="text-sm font-bold text-slate-700">{{ __('app.register.password') }}</label>
                <input id="password" name="password" type="password" required autocomplete="new-password"
                       placeholder="{{ __('app.register.password_placeholder') }}"
                       class="mt-2 w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm shadow-sm placeholder:text-slate-400 focus:border-orange-500 focus:ring-orange-500">
                @error('password') <p class="mt-2 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="password_confirmation" class="text-sm font-bold text-slate-700">{{ __('app.register.password_confirmation') }}</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                       placeholder="{{ __('app.register.password_confirmation_placeholder') }}"
                       class="mt-2 w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm shadow-sm placeholder:text-slate-400 focus:border-orange-500 focus:ring-orange-500">
            </div>
        </div>

        <button class="flex w-full items-center justify-center rounded-xl bg-slate-950 px-4 py-3.5 text-sm font-extrabold text-white shadow-lg shadow-slate-950/15 transition hover:bg-slate-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-500">
            {{ __('app.invitation.submit') }}
        </button>
    </form>
</x-layouts.auth>
