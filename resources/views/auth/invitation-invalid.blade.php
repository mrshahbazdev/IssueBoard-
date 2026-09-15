<x-layouts.auth :title="__('app.invitation.invalid_title')">
    <div class="text-center">
        <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-rose-100 text-rose-600">
            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M12 9v4"/>
                <path d="M12 17h.01"/>
                <path d="M10.3 3.7 2.6 17a2 2 0 0 0 1.7 3h15.4a2 2 0 0 0 1.7-3L13.7 3.7a2 2 0 0 0-3.4 0Z"/>
            </svg>
        </span>
        <p class="mt-5 text-sm font-bold uppercase tracking-[0.18em] text-rose-600">{{ __('app.invitation.invalid_eyebrow') }}</p>
        <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-slate-950">{{ __('app.invitation.invalid_heading') }}</h2>
        <p class="mx-auto mt-3 max-w-sm text-sm leading-6 text-slate-500">{{ __('app.invitation.invalid_description') }}</p>
        <a href="{{ route('login') }}" class="mt-7 inline-flex rounded-xl bg-slate-950 px-5 py-3 text-sm font-extrabold text-white transition hover:bg-slate-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-500">
            {{ __('app.invitation.go_to_login') }}
        </a>
    </div>
</x-layouts.auth>
