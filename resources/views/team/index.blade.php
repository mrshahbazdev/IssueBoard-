<x-app-shell :title="__('app.team.title')">
    <div class="mx-auto max-w-6xl">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.18em] text-orange-600">{{ __('app.team.eyebrow') }}</p>
                <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-950">{{ __('app.team.heading') }}</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">{{ __('app.team.description') }}</p>
            </div>
            <span class="self-start rounded-full bg-slate-950 px-3 py-1.5 text-xs font-extrabold text-white">{{ trans_choice('app.team.people', $users->count(), ['count' => $users->count()]) }}</span>
        </div>

        @php($invitationRoutesAvailable = Route::has('team.invitations.store') && Route::has('team.invitations.resend') && Route::has('team.invitations.destroy'))

        <section class="relative mt-7 overflow-hidden rounded-3xl bg-slate-950 p-6 text-white shadow-xl shadow-slate-950/10 sm:p-8">
            <div class="absolute -right-20 -top-24 h-64 w-64 rounded-full bg-cyan-400/10 blur-3xl"></div>
            <div class="absolute -bottom-28 left-1/3 h-64 w-64 rounded-full bg-orange-400/10 blur-3xl"></div>

            <div class="relative grid gap-7 lg:grid-cols-[minmax(0,.8fr)_minmax(0,1.2fr)] lg:items-end">
                <div>
                    <span class="inline-flex rounded-full border border-orange-300/25 bg-orange-300/10 px-3 py-1 text-[10px] font-extrabold uppercase tracking-[0.18em] text-orange-200">{{ __('app.team.invite_eyebrow') }}</span>
                    <h2 class="mt-4 text-2xl font-extrabold tracking-tight">{{ __('app.team.invite_heading') }}</h2>
                    <p class="mt-2 max-w-md text-sm leading-6 text-slate-300">{{ __('app.team.invite_description') }}</p>
                    @if (auth()->user()->mailSetting()->exists())
                        <div class="mt-3 flex flex-wrap items-center gap-3">
                            <p class="inline-flex items-center gap-2 text-xs font-bold text-cyan-300">
                                <span class="h-2 w-2 rounded-full bg-cyan-400"></span>
                                {{ __('app.team.smtp_connected') }}
                            </p>
                            @if ($smtpRoutesAvailable ?? Route::has('profile.smtp.test'))
                                <form method="POST" action="{{ route('profile.smtp.test') }}" class="inline">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-cyan-400/40 bg-cyan-400/10 px-2.5 py-1 text-xs font-bold text-cyan-200 transition hover:bg-cyan-400/20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-cyan-400">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/></svg>
                                        {{ __('app.profile.smtp_test') }}
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('profile.edit') }}#smtp" class="text-xs font-semibold text-slate-400 underline decoration-slate-400/40 underline-offset-4 hover:text-white">
                                {{ __('app.navigation.profile') }}
                            </a>
                        </div>
                    @else
                        <a href="{{ route('profile.edit') }}#smtp" class="mt-3 inline-flex items-center gap-2 text-xs font-bold text-orange-300 underline decoration-orange-300/40 underline-offset-4 hover:text-orange-200">
                            {{ __('app.team.smtp_setup') }}
                            <span aria-hidden="true">→</span>
                        </a>
                    @endif
                </div>

                <form method="POST" action="{{ $invitationRoutesAvailable ? route('team.invitations.store') : '#' }}" class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_180px_auto] sm:items-end">
                    @csrf
                    <div>
                        <label for="invite_email" class="text-xs font-bold text-slate-300">{{ __('app.team.invite_email') }}</label>
                        <input id="invite_email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email"
                               placeholder="{{ __('app.team.invite_email_placeholder') }}"
                               class="mt-2 w-full rounded-xl border-white/15 bg-white/10 px-4 py-3 text-sm text-white shadow-sm placeholder:text-slate-500 focus:border-orange-400 focus:ring-orange-400">
                    </div>
                    <div>
                        <label for="invite_role" class="text-xs font-bold text-slate-300">{{ __('app.team.invite_role') }}</label>
                        <select id="invite_role" name="role" required class="mt-2 w-full rounded-xl border-white/15 bg-slate-900 px-3 py-3 text-sm font-semibold text-white focus:border-orange-400 focus:ring-orange-400">
                            @foreach ($roles as $role)
                                <option value="{{ $role->value }}" @selected(old('role', \App\Enums\UserRole::Member->value) === $role->value)>{{ $role->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button @disabled(! $invitationRoutesAvailable) class="rounded-xl bg-orange-500 px-5 py-3 text-sm font-extrabold text-white shadow-lg shadow-orange-950/20 transition hover:bg-orange-400 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-300 disabled:cursor-not-allowed disabled:opacity-50">
                        {{ __('app.team.send_invite') }}
                    </button>
                </form>
            </div>
        </section>

        @unless ($invitationRoutesAvailable)
            <section class="mt-6 rounded-2xl border border-orange-200 bg-orange-50 px-5 py-4 text-sm font-semibold text-orange-800">
                {{ __('app.team.invitation_routes_unavailable') }}
            </section>
        @endunless

        @if ($errors->any())
            <div class="mt-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">{{ $errors->first() }}</div>
        @endif

        @if ($invitations->isNotEmpty())
            <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 class="text-lg font-extrabold text-slate-950">{{ __('app.team.pending_heading') }}</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ __('app.team.pending_description') }}</p>
                    </div>
                    <span class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">{{ trans_choice('app.team.pending_count', $invitations->count(), ['count' => $invitations->count()]) }}</span>
                </div>

                <div class="mt-5 grid gap-3 md:grid-cols-2">
                    @foreach ($invitations as $invitation)
                        <article class="relative overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <span class="absolute inset-y-0 left-0 w-1 {{ $invitation->expires_at->isPast() ? 'bg-rose-500' : 'bg-cyan-500' }}"></span>
                            <div class="flex items-start justify-between gap-3 pl-2">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-extrabold text-slate-900">{{ $invitation->email }}</p>
                                    <div class="mt-2 flex flex-wrap items-center gap-2">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wide ring-1 ring-inset {{ $invitation->role->badgeClasses() }}">{{ $invitation->role->label() }}</span>
                                        <span class="text-xs font-semibold {{ $invitation->expires_at->isPast() ? 'text-rose-600' : 'text-slate-500' }}">
                                            {{ $invitation->expires_at->isPast() ? __('app.team.invite_expired') : __('app.team.invite_expires', ['date' => $invitation->expires_at->translatedFormat('j M Y')]) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex shrink-0 items-center gap-1.5">
                                    <form method="POST" action="{{ $invitationRoutesAvailable ? route('team.invitations.resend', $invitation) : '#' }}">
                                        @csrf
                                        <button @disabled(! $invitationRoutesAvailable) class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-extrabold text-slate-700 transition hover:border-cyan-300 hover:text-cyan-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cyan-600 disabled:cursor-not-allowed disabled:opacity-50">{{ __('app.team.resend') }}</button>
                                    </form>
                                    <form method="POST" action="{{ $invitationRoutesAvailable ? route('team.invitations.destroy', $invitation) : '#' }}">
                                        @csrf
                                        @method('DELETE')
                                        <button @disabled(! $invitationRoutesAvailable) class="rounded-lg px-3 py-2 text-xs font-extrabold text-rose-600 transition hover:bg-rose-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rose-600 disabled:cursor-not-allowed disabled:opacity-50">{{ __('app.team.cancel') }}</button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        <div class="mt-7 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
            @foreach ($roles as $role)
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wide ring-1 ring-inset {{ $role->badgeClasses() }}">{{ $role->label() }}</span>
                    <p class="mt-3 text-xs leading-5 text-slate-500">{{ $role->description() }}</p>
                </div>
            @endforeach
        </div>

        <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-[11px] font-extrabold uppercase tracking-wider text-slate-400">
                            <th class="px-5 py-3.5">{{ __('app.team.person') }}</th>
                            <th class="px-5 py-3.5">{{ __('app.team.contact') }}</th>
                            <th class="px-5 py-3.5">{{ __('app.team.role') }}</th>
                            <th class="px-5 py-3.5 text-right">{{ __('app.team.action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($users as $user)
                            <tr>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-950 text-xs font-extrabold text-white">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                                        <div>
                                            <p class="text-sm font-extrabold text-slate-900">{{ $user->name }}</p>
                                            @if (auth()->user()->is($user))
                                                <p class="mt-0.5 text-xs font-medium text-orange-600">{{ __('app.team.your_account') }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="text-sm font-medium text-slate-700">{{ $user->email }}</p>
                                    <p class="mt-0.5 text-xs text-slate-400">{{ $user->phone ?: __('app.team.no_phone') }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wide ring-1 ring-inset {{ $user->role->badgeClasses() }}">{{ $user->role->label() }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    @if (! auth()->user()->is($user))
                                        <form method="POST" action="{{ route('team.role.update', $user) }}" class="flex items-center justify-end gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <select name="role" aria-label="{{ __('app.team.role_for', ['name' => $user->name]) }}" class="rounded-lg border-slate-300 bg-slate-50 py-2 text-xs font-semibold focus:border-orange-500 focus:ring-orange-500">
                                                @foreach ($roles as $role)
                                                    <option value="{{ $role->value }}" @selected($user->role === $role)>{{ $role->label() }}</option>
                                                @endforeach
                                            </select>
                                            <button class="rounded-lg bg-slate-950 px-3 py-2 text-xs font-extrabold text-white hover:bg-slate-700">{{ __('app.team.save') }}</button>
                                        </form>
                                    @else
                                        <p class="text-right text-xs font-medium text-slate-400">{{ __('app.team.protected') }}</p>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-shell>
