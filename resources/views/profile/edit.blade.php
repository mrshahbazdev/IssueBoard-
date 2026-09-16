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

        @php($smtpRoutesAvailable = Route::has('profile.smtp') && Route::has('profile.smtp.test') && Route::has('profile.smtp.destroy'))

        <section id="smtp" class="relative mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <span class="absolute inset-y-0 left-0 w-1.5 bg-cyan-500"></span>
            <div class="p-6 sm:p-7">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-50 text-cyan-700">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M4 6h16v12H4z"/>
                                    <path d="m4 7 8 6 8-6"/>
                                </svg>
                            </span>
                            <div>
                                <h2 class="font-extrabold text-slate-950">{{ __('app.profile.smtp_heading') }}</h2>
                                <p class="mt-1 text-xs text-slate-500">{{ __('app.profile.smtp_help') }}</p>
                            </div>
                        </div>
                    </div>
                    @if ($user->mailSetting)
                        <span class="self-start rounded-full bg-emerald-50 px-3 py-1 text-[10px] font-extrabold uppercase tracking-wide text-emerald-700 ring-1 ring-inset ring-emerald-200">{{ __('app.profile.smtp_saved') }}</span>
                    @endif
                </div>

                @error('smtp', 'smtpUpdate')
                    <div class="mt-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">{{ $message }}</div>
                @enderror

                @unless ($smtpRoutesAvailable)
                    <div class="mt-5 rounded-xl border border-orange-200 bg-orange-50 px-4 py-3 text-sm font-semibold text-orange-800">
                        {{ __('app.profile.smtp_routes_unavailable') }}
                    </div>
                @endunless

                <form method="POST" action="{{ $smtpRoutesAvailable ? route('profile.smtp') : '#' }}" class="mt-6">
                    @csrf
                    @method('PUT')

                    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-[minmax(0,1fr)_120px_180px]">
                        <div>
                            <label for="host" class="text-sm font-bold text-slate-700">{{ __('app.profile.smtp_host') }}</label>
                            <input id="host" name="host" value="{{ old('host', $user->mailSetting?->host) }}" required placeholder="smtp.example.com"
                                   class="mt-2 w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-3 text-sm focus:border-cyan-500 focus:bg-white focus:ring-cyan-500">
                            @error('host', 'smtpUpdate') <p class="mt-2 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="port" class="text-sm font-bold text-slate-700">{{ __('app.profile.smtp_port') }}</label>
                            <input id="port" name="port" type="number" min="1" max="65535" value="{{ old('port', $user->mailSetting?->port ?? 587) }}" required
                                   class="mt-2 w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-3 text-sm focus:border-cyan-500 focus:bg-white focus:ring-cyan-500">
                            @error('port', 'smtpUpdate') <p class="mt-2 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="scheme" class="text-sm font-bold text-slate-700">{{ __('app.profile.smtp_security') }}</label>
                            <select id="scheme" name="scheme" required class="mt-2 w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-3 text-sm font-semibold focus:border-cyan-500 focus:bg-white focus:ring-cyan-500">
                                <option value="smtp" @selected(old('scheme', $user->mailSetting?->scheme ?? 'smtp') === 'smtp')>{{ __('app.profile.smtp_tls') }}</option>
                                <option value="smtps" @selected(old('scheme', $user->mailSetting?->scheme) === 'smtps')>{{ __('app.profile.smtp_ssl') }}</option>
                            </select>
                            @error('scheme', 'smtpUpdate') <p class="mt-2 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mt-5 grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="username" class="text-sm font-bold text-slate-700">{{ __('app.profile.smtp_username') }}</label>
                            <input id="username" name="username" value="{{ old('username', $user->mailSetting?->username) }}" autocomplete="username"
                                   class="mt-2 w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-3 text-sm focus:border-cyan-500 focus:bg-white focus:ring-cyan-500">
                            @error('username', 'smtpUpdate') <p class="mt-2 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="smtp_password" class="text-sm font-bold text-slate-700">{{ __('app.profile.smtp_password') }}</label>
                            <input id="smtp_password" name="password" type="password" autocomplete="new-password"
                                   placeholder="{{ $user->mailSetting ? __('app.profile.smtp_password_saved') : '' }}"
                                   class="mt-2 w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-3 text-sm placeholder:text-slate-400 focus:border-cyan-500 focus:bg-white focus:ring-cyan-500">
                            @error('password', 'smtpUpdate') <p class="mt-2 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mt-5 grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="from_address" class="text-sm font-bold text-slate-700">{{ __('app.profile.smtp_from_address') }}</label>
                            <input id="from_address" name="from_address" type="email" value="{{ old('from_address', $user->mailSetting?->from_address ?? $user->email) }}" required
                                   class="mt-2 w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-3 text-sm focus:border-cyan-500 focus:bg-white focus:ring-cyan-500">
                            @error('from_address', 'smtpUpdate') <p class="mt-2 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="from_name" class="text-sm font-bold text-slate-700">{{ __('app.profile.smtp_from_name') }}</label>
                            <input id="from_name" name="from_name" value="{{ old('from_name', $user->mailSetting?->from_name ?? $user->name) }}"
                                   class="mt-2 w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-3 text-sm focus:border-cyan-500 focus:bg-white focus:ring-cyan-500">
                            @error('from_name', 'smtpUpdate') <p class="mt-2 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mt-6 flex flex-wrap items-center gap-3">
                        <button type="submit" @disabled(! $smtpRoutesAvailable) class="rounded-xl bg-cyan-600 px-5 py-3 text-sm font-extrabold text-white transition hover:bg-cyan-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cyan-600 disabled:cursor-not-allowed disabled:opacity-50">{{ __('app.profile.smtp_save') }}</button>
                        <button type="submit" formaction="{{ route('profile.smtp.test') }}" @disabled(! $smtpRoutesAvailable) class="rounded-xl border border-cyan-500 bg-cyan-50 px-5 py-3 text-sm font-extrabold text-cyan-800 transition hover:bg-cyan-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cyan-600 disabled:cursor-not-allowed disabled:opacity-50">
                            {{ __('app.profile.smtp_save_and_test') }}
                        </button>
                    </div>
                </form>

                @if ($user->mailSetting)
                    <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <div>
                            <h3 class="text-sm font-extrabold text-slate-900">{{ __('app.profile.smtp_test_heading') }}</h3>
                            <p class="mt-1 text-xs text-slate-500">{{ __('app.profile.smtp_test_desc') }}</p>
                        </div>
                        <form method="POST" action="{{ $smtpRoutesAvailable ? route('profile.smtp.test') : '#' }}" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center">
                            @csrf
                            <div class="flex-1">
                                <input type="email" name="test_email" value="{{ old('test_email', $user->email) }}" required placeholder="recipient@example.com"
                                       class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                            </div>
                            <button type="submit" @disabled(! $smtpRoutesAvailable) class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-xs font-extrabold text-white transition hover:bg-slate-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-900 disabled:cursor-not-allowed disabled:opacity-50">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/></svg>
                                {{ __('app.profile.smtp_test') }}
                            </button>
                        </form>
                        <div class="mt-4 border-t border-slate-200 pt-3">
                            <form method="POST" action="{{ $smtpRoutesAvailable ? route('profile.smtp.destroy') : '#' }}" onsubmit="return confirm('{{ __('app.profile.smtp_remove') }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" @disabled(! $smtpRoutesAvailable) class="text-xs font-extrabold text-rose-600 hover:text-rose-700 disabled:cursor-not-allowed disabled:opacity-50">{{ __('app.profile.smtp_remove') }}</button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </section>
    </div>
</x-app-shell>
