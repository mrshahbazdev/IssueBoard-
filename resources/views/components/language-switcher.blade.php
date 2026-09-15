@props(['compact' => false])

<form method="POST" action="{{ route('locale.update') }}" class="shrink-0">
    @csrf
    <label>
        <span class="sr-only">{{ __('app.language') }}</span>
        <select
            name="locale"
            aria-label="{{ __('app.language') }}"
            onchange="this.form.submit()"
            @class([
                'rounded-xl border-slate-200 bg-white font-extrabold uppercase tracking-wide text-slate-600 shadow-sm transition hover:border-slate-300 focus:border-orange-500 focus:ring-orange-500',
                'py-2 text-xs' => $compact,
                'py-2.5 text-xs' => ! $compact,
            ])
        >
            @foreach (config('app.supported_locales') as $locale => $label)
                <option value="{{ $locale }}" @selected(app()->getLocale() === $locale)>{{ $label }}</option>
            @endforeach
        </select>
    </label>
</form>
