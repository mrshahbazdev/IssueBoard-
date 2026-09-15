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

        <div class="mt-7 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
            @foreach ($roles as $role)
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wide ring-1 ring-inset {{ $role->badgeClasses() }}">{{ $role->label() }}</span>
                    <p class="mt-3 text-xs leading-5 text-slate-500">{{ $role->description() }}</p>
                </div>
            @endforeach
        </div>

        @if ($errors->any())
            <div class="mt-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">{{ $errors->first() }}</div>
        @endif

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
