<x-app-shell :title="__('app.projects.title')">
    <div class="mx-auto max-w-6xl">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.18em] text-orange-600">{{ __('app.projects.eyebrow') }}</p>
                <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-950">{{ __('app.projects.heading') }}</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">{{ __('app.projects.description') }}</p>
            </div>
            <span class="self-start rounded-full bg-slate-950 px-3 py-1.5 text-xs font-extrabold text-white">
                {{ trans_choice('app.projects.project_count', $projects->count(), ['count' => $projects->count()]) }}
            </span>
        </div>

        @if ($canManage)
            <section class="relative mt-7 overflow-hidden rounded-3xl bg-slate-950 p-6 text-white shadow-xl shadow-slate-950/10 sm:p-8">
                <div class="absolute -right-20 -top-24 h-64 w-64 rounded-full bg-orange-400/10 blur-3xl"></div>
                <div class="absolute -bottom-28 left-1/3 h-64 w-64 rounded-full bg-cyan-400/10 blur-3xl"></div>

                <div class="relative">
                    <span class="inline-flex rounded-full border border-orange-300/25 bg-orange-300/10 px-3 py-1 text-[10px] font-extrabold uppercase tracking-[0.18em] text-orange-200">
                        {{ __('app.projects.new_project') }}
                    </span>
                    <h2 class="mt-3 text-2xl font-extrabold tracking-tight">{{ __('app.projects.create_heading') }}</h2>
                    <p class="mt-1 max-w-md text-sm text-slate-300">{{ __('app.projects.create_description') }}</p>

                    <form method="POST" action="{{ route('projects.store') }}" class="mt-6 grid gap-4 sm:grid-cols-[minmax(0,1.2fr)_minmax(0,1.5fr)_130px_auto] sm:items-end">
                        @csrf
                        <div>
                            <label for="name" class="text-xs font-bold text-slate-300">{{ __('app.projects.name') }} <span class="text-rose-400">*</span></label>
                            <input id="name" name="name" type="text" value="{{ old('name') }}" required
                                   placeholder="{{ __('app.projects.name_placeholder') }}"
                                   class="mt-2 w-full rounded-xl border-white/15 bg-white/10 px-4 py-3 text-sm text-white shadow-sm placeholder:text-slate-500 focus:border-orange-400 focus:ring-orange-400">
                        </div>
                        <div>
                            <label for="description" class="text-xs font-bold text-slate-300">{{ __('app.projects.description_field') }}</label>
                            <input id="description" name="description" type="text" value="{{ old('description') }}"
                                   placeholder="{{ __('app.projects.description_placeholder') }}"
                                   class="mt-2 w-full rounded-xl border-white/15 bg-white/10 px-4 py-3 text-sm text-white shadow-sm placeholder:text-slate-500 focus:border-orange-400 focus:ring-orange-400">
                        </div>
                        <div>
                            <label for="color" class="text-xs font-bold text-slate-300">{{ __('app.projects.color') }}</label>
                            <div class="mt-2 flex items-center gap-2 rounded-xl border border-white/15 bg-white/10 px-3 py-2.5">
                                <input id="color" name="color" type="color" value="{{ old('color', '#0f766e') }}"
                                       class="h-7 w-8 cursor-pointer rounded border-0 bg-transparent p-0">
                                <span class="text-xs font-mono text-slate-300 uppercase" x-text="$el.previousElementSibling.value"></span>
                            </div>
                        </div>
                        <button type="submit" class="rounded-xl bg-orange-500 px-5 py-3 text-sm font-extrabold text-white shadow-lg shadow-orange-950/20 transition hover:bg-orange-400 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-300">
                            {{ __('app.projects.save') }}
                        </button>
                    </form>
                </div>
            </section>
        @endif

        @if ($errors->any())
            <div class="mt-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <section class="mt-7 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            @if ($projects->isEmpty())
                <div class="p-12 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-base font-extrabold text-slate-900">{{ __('app.projects.no_projects') }}</h3>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr class="text-left text-[11px] font-extrabold uppercase tracking-wider text-slate-400">
                                <th class="px-5 py-3.5">{{ __('app.projects.name') }}</th>
                                <th class="px-5 py-3.5">{{ __('app.projects.description_field') }}</th>
                                <th class="px-5 py-3.5">{{ __('issueboard::issueboard.board_title') }}</th>
                                @if ($canManage)
                                    <th class="px-5 py-3.5 text-right">{{ __('app.team.action') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100" x-data="{ editingId: null }">
                            @foreach ($projects as $project)
                                <tr>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <span class="h-3.5 w-3.5 shrink-0 rounded-full ring-2 ring-white shadow-sm" style="background-color: {{ $project->color }}"></span>
                                            <a href="{{ route('issueboard.index', ['project' => $project->id]) }}" class="text-sm font-extrabold text-slate-900 hover:text-orange-600 transition">
                                                {{ $project->name }}
                                            </a>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 max-w-md">
                                        <p class="text-xs text-slate-500 truncate">{{ $project->description ?: '—' }}</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <a href="{{ route('issueboard.index', ['project' => $project->id]) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-bold text-slate-700 hover:bg-slate-100 transition">
                                            <span>{{ trans_choice('app.projects.issue_count', $project->issues_count, ['count' => $project->issues_count]) }}</span>
                                            <span aria-hidden="true">→</span>
                                        </a>
                                    </td>
                                    @if ($canManage)
                                        <td class="px-5 py-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <button type="button" @click="editingId = (editingId === {{ $project->id }} ? null : {{ $project->id }})" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-extrabold text-slate-700 hover:bg-slate-50 transition">
                                                    {{ __('app.projects.update') }}
                                                </button>
                                                <form method="POST" action="{{ route('projects.destroy', $project) }}" onsubmit="return confirm('{{ __('app.projects.delete_confirm') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="rounded-lg px-2.5 py-1.5 text-xs font-extrabold text-rose-600 hover:bg-rose-50 transition">
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                                @if ($canManage)
                                    <tr x-show="editingId === {{ $project->id }}" x-cloak class="bg-slate-50/75">
                                        <td colspan="4" class="px-5 py-4 border-t border-slate-200">
                                            <form method="POST" action="{{ route('projects.update', $project) }}" class="flex flex-wrap items-center gap-3">
                                                @csrf
                                                @method('PUT')
                                                <input type="text" name="name" value="{{ $project->name }}" required class="rounded-xl border-slate-300 bg-white px-3 py-2 text-xs font-semibold focus:border-orange-500 focus:ring-orange-500">
                                                <input type="text" name="description" value="{{ $project->description }}" placeholder="{{ __('app.projects.description_field') }}" class="flex-1 min-w-[200px] rounded-xl border-slate-300 bg-white px-3 py-2 text-xs focus:border-orange-500 focus:ring-orange-500">
                                                <input type="color" name="color" value="{{ $project->color }}" class="h-8 w-10 cursor-pointer rounded border border-slate-200 bg-white p-0">
                                                <button type="submit" class="rounded-xl bg-slate-950 px-4 py-2 text-xs font-extrabold text-white hover:bg-slate-800 transition">{{ __('app.projects.update') }}</button>
                                                <button type="button" @click="editingId = null" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-extrabold text-slate-600 hover:bg-slate-100 transition">{{ __('app.projects.cancel') }}</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
</x-app-shell>
