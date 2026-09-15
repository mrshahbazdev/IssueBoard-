<x-app-shell :title="__('app.profile.title')">
    <div class="mx-auto max-w-5xl">
        <div class="mb-7">
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-orange-600">{{ __('app.profile.eyebrow') }}</p>
            <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-950">{{ __('app.profile.title') }}</h1>
            <p class="mt-2 text-sm text-slate-500">{{ __('app.profile.description') }}</p>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <form method="POST" action="{{ route('profile.update') }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                @csrf
                @method('PATCH')
                <div class="flex items-center gap-4">
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-950 text-base font-extrabold text-white">
                        {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                    </span>
                    <div>
                        <h2 class="font-extrabold text-slate-950">{{ __('app.profile.personal_details') }}</h2>
                        <p class="mt-1 text-xs text-slate-500">{{ __('app.profile.personal_details_help') }}</p>
                    </div>
                </div>

                <div class="mt-6 space-y-5">
                    @foreach ([
                        ['name', __('app.profile.name'), 'text', 'name'],
                        ['email', __('app.profile.email'), 'email', 'email'],
                        ['phone', __('app.profile.phone'), 'tel', 'tel'],
                    ] as [$field, $label, $type, $autocomplete])
                        <div>
                            <label for="{{ $field }}" class="text-sm font-bold text-slate-700">{{ $label }}</label>
                            <input id="{{ $field }}" name="{{ $field }}" type="{{ $type }}" value="{{ old($field, $user->{$field}) }}"
                                   autocomplete="{{ $autocomplete }}"
                                   class="mt-2 w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-3 text-sm focus:border-orange-500 focus:bg-white focus:ring-orange-500">
                            @error($field) <p class="mt-2 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    @endforeach
                </div>

                <button class="mt-6 rounded-xl bg-slate-950 px-5 py-3 text-sm font-extrabold text-white transition hover:bg-slate-800">{{ __('app.profile.save') }}</button>
            </form>

            <form method="POST" action="{{ route('profile.password') }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                @csrf
                @method('PUT')
                <div>
                    <h2 class="font-extrabold text-slate-950">{{ __('app.profile.change_password') }}</h2>
                    <p class="mt-1 text-xs text-slate-500">{{ __('app.profile.password_help') }}</p>
                </div>

                <div class="mt-6 space-y-5">
                    @foreach ([
                        ['current_password', __('app.profile.current_password'), 'current-password'],
                        ['password', __('app.profile.new_password'), 'new-password'],
                        ['password_confirmation', __('app.profile.confirm_password'), 'new-password'],
                    ] as [$field, $label, $autocomplete])
                        <div>
                            <label for="{{ $field }}" class="text-sm font-bold text-slate-700">{{ $label }}</label>
                            <input id="{{ $field }}" name="{{ $field }}" type="password" autocomplete="{{ $autocomplete }}"
                                   class="mt-2 w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-3 text-sm focus:border-orange-500 focus:bg-white focus:ring-orange-500">
                            @error($field, 'passwordUpdate') <p class="mt-2 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    @endforeach
                </div>

                <button class="mt-6 rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-extrabold text-slate-800 transition hover:border-slate-400 hover:bg-slate-50">{{ __('app.profile.update_password') }}</button>
            </form>
        </div>
    </div>
</x-app-shell>
