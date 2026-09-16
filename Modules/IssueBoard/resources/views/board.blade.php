<div class="space-y-6">
    @php
        $totalIssues = $issues->sum(fn ($column) => $column->count());
        $doneIssues = $issues->get(\Modules\IssueBoard\Enums\IssueStatus::Done->value, collect())->count();
        $openIssues = $totalIssues - $doneIssues;
        $questionCount = $issues->flatten()->sum('open_questions_count');
    @endphp

    <section class="overflow-hidden rounded-2xl bg-slate-900 text-white shadow-sm">
        <div class="grid gap-8 px-5 py-6 sm:px-7 sm:py-8 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end">
            <div class="max-w-3xl">
                <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em] text-[#ffb24c]">
                    <span class="h-2 w-2 rounded-full bg-[#ff9200]"></span>
                    {{ __('issueboard::issueboard.internal_communication') }}
                </div>
                <h1 class="mt-4 max-w-2xl text-2xl font-bold tracking-tight sm:text-3xl">
                    {{ __('issueboard::issueboard.hero_title') }}
                </h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-300 sm:text-base">
                    {{ __('issueboard::issueboard.hero_description') }}
                </p>
            </div>

            <dl class="grid grid-cols-3 gap-2 sm:min-w-[360px]">
                <div class="rounded-xl border border-white/10 bg-white/5 p-3 sm:p-4">
                    <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-400">
                        {{ __('issueboard::issueboard.open') }}
                    </dt>
                    <dd class="mt-1 text-2xl font-bold text-white">{{ $openIssues }}</dd>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 p-3 sm:p-4">
                    <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-400">
                        {{ __('issueboard::issueboard.questions_short') }}
                    </dt>
                    <dd class="mt-1 text-2xl font-bold text-amber-300">{{ $questionCount }}</dd>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 p-3 sm:p-4">
                    <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-400">
                        {{ __('issueboard::issueboard.done_short') }}
                    </dt>
                    <dd class="mt-1 text-2xl font-bold text-emerald-300">{{ $doneIssues }}</dd>
                </div>
            </dl>
        </div>

        <div class="grid border-t border-white/10 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($statuses as $status)
                @php $column = $issues->get($status->value, collect()); @endphp
                <div class="relative flex items-center gap-3 border-white/10 px-5 py-4 sm:border-r last:border-r-0">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-sm font-bold"
                          style="background: {{ $status->tint() }}; color: {{ $status->color() }}">
                        {{ $loop->iteration }}
                    </span>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold">{{ $status->label() }}</p>
                        <p class="mt-0.5 truncate text-xs text-slate-400">
                            {{ __('issueboard::issueboard.status_help.'.$status->value) }}
                        </p>
                    </div>
                    <span class="ml-auto rounded-full bg-white/10 px-2 py-0.5 text-xs font-semibold text-slate-200">
                        {{ $column->count() }}
                    </span>
                </div>
            @endforeach
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-1 flex-col gap-3 sm:flex-row sm:items-center">
                <label class="relative block w-full sm:max-w-sm">
                    <span class="sr-only">{{ __('issueboard::issueboard.search_placeholder') }}</span>
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400"
                         fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m2.1-5.4a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z"/>
                    </svg>
                    <input type="search"
                           wire:model.live.debounce.400ms="search"
                           placeholder="{{ __('issueboard::issueboard.search_placeholder') }}"
                           class="w-full rounded-xl border-slate-300 bg-slate-50 py-2.5 pl-10 pr-3 text-sm placeholder:text-slate-400 focus:border-[#ff9200] focus:bg-white focus:ring-[#ff9200]">
                </label>

                @if ($projects->isNotEmpty() || auth()->user()->canManageTeam())
                    <div class="flex items-center gap-1.5">
                        @if ($projects->isNotEmpty())
                            <label>
                                <span class="sr-only">{{ __('issueboard::issueboard.all_projects') }}</span>
                                <select wire:model.live="projectId"
                                        class="w-full rounded-xl border-slate-300 bg-slate-50 py-2.5 text-sm focus:border-[#ff9200] focus:bg-white focus:ring-[#ff9200] sm:w-auto sm:min-w-48">
                                    <option value="">{{ __('issueboard::issueboard.all_projects') }}</option>
                                    @foreach ($projects as $project)
                                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                                    @endforeach
                                </select>
                            </label>
                        @endif
                        @if (auth()->user()->canManageTeam())
                            <a href="{{ \Illuminate\Support\Facades\Route::has('projects.index') ? route('projects.index') : url('/projects') }}" title="{{ __('app.projects.heading') }}" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 px-3 text-xs font-bold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900">
                                <svg class="h-4 w-4 sm:mr-1 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                <span class="hidden sm:inline">{{ __('app.projects.new_project') }}</span>
                            </a>
                        @endif
                    </div>
                @endif

                <label class="inline-flex min-h-11 items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm font-medium text-slate-600">
                    <input type="checkbox" wire:model.live="onlyMine"
                           class="rounded border-slate-300 text-[#ff9200] focus:ring-[#ff9200]">
                    {{ __('issueboard::issueboard.only_mine') }}
                </label>
            </div>

            <div class="flex items-center gap-3">
                @if ($hasFilters)
                    <button type="button" wire:click="resetFilters"
                            class="text-sm font-medium text-slate-500 hover:text-slate-900">
                        {{ __('issueboard::issueboard.reset_filters') }}
                    </button>
                @endif

                <div wire:loading.delay class="flex items-center gap-2 text-sm text-slate-400">
                    <span class="h-2 w-2 animate-pulse rounded-full bg-[#ff9200]"></span>
                    {{ __('issueboard::issueboard.loading') }}
                </div>
            </div>
        </div>
    </section>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        @foreach ($statuses as $status)
            @php $column = $issues->get($status->value, collect()); @endphp

            <section class="flex min-w-0 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-slate-100/70 shadow-sm">
                <header class="border-b border-slate-200 bg-white px-4 py-4">
                    <div class="flex items-center gap-3">
                        <span class="h-3 w-3 rounded-full ring-4"
                              style="background: {{ $status->color() }}; --tw-ring-color: {{ $status->tint() }}"></span>
                        <div class="min-w-0 flex-1">
                            <h2 class="truncate text-sm font-bold text-slate-900">{{ $status->label() }}</h2>
                            <p class="mt-0.5 truncate text-xs text-slate-500">
                                {{ __('issueboard::issueboard.status_help.'.$status->value) }}
                            </p>
                        </div>
                        <span class="rounded-full px-2.5 py-1 text-xs font-bold"
                              style="background: {{ $status->tint() }}; color: {{ $status->color() }}">
                            {{ $column->count() }}
                        </span>
                    </div>
                </header>

                <div class="issue-column flex min-h-64 flex-1 flex-col gap-3 p-3"
                     data-status="{{ $status->value }}"
                     x-data
                     x-init="
                        new Sortable($el, {
                            group: 'issues',
                            animation: 180,
                            ghostClass: 'opacity-40',
                            dragClass: 'rotate-1',
                            draggable: '[data-issue-id]',
                            onEnd(evt) {
                                const status = evt.to.dataset.status;
                                const ids = Array.from(evt.to.querySelectorAll('[data-issue-id]'))
                                    .map(el => parseInt(el.dataset.issueId));
                                $wire.moveCard(parseInt(evt.item.dataset.issueId), status, ids);
                            }
                        })
                     ">
                    @forelse ($column as $issue)
                        @include('issueboard::partials.card', ['issue' => $issue])
                    @empty
                        <div class="flex flex-1 flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white/50 px-4 py-8 text-center">
                            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-white text-slate-400 shadow-sm">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                </svg>
                            </span>
                            <p class="mt-3 text-xs font-medium text-slate-500">
                                {{ __('issueboard::issueboard.empty_column') }}
                            </p>
                        </div>
                    @endforelse
                </div>
            </section>
        @endforeach
    </div>
</div>
