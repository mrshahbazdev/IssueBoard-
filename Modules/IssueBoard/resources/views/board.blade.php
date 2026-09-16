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

    <!-- Toolbar & Filter Controls -->
    <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
        <div class="flex flex-col gap-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <!-- Search & Filters -->
                <div class="flex flex-1 flex-wrap items-center gap-2.5 sm:gap-3">
                    <label class="relative block w-full sm:w-64">
                        <span class="sr-only">{{ __('issueboard::issueboard.search_placeholder') }}</span>
                        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                             fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m2.1-5.4a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z"/>
                        </svg>
                        <input type="search"
                               wire:model.live.debounce.400ms="search"
                               placeholder="{{ __('issueboard::issueboard.search_placeholder') }}"
                               class="w-full rounded-xl border-slate-300 bg-slate-50 py-2 pl-9 pr-3 text-xs placeholder:text-slate-400 focus:border-orange-500 focus:bg-white focus:ring-orange-500">
                    </label>

                    <!-- Projects Dropdown -->
                    @if ($projects->isNotEmpty() || auth()->user()->canManageTeam())
                        <div class="flex items-center gap-1.5">
                            @if ($projects->isNotEmpty())
                                <select wire:model.live="projectId"
                                        class="rounded-xl border-slate-300 bg-slate-50 py-2 text-xs font-semibold focus:border-orange-500 focus:bg-white focus:ring-orange-500">
                                    <option value="">{{ __('issueboard::issueboard.all_projects') }}</option>
                                    @foreach ($projects as $project)
                                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                    @endif

                    <!-- Priority Dropdown -->
                    <select wire:model.live="priority"
                            class="rounded-xl border-slate-300 bg-slate-50 py-2 text-xs font-semibold focus:border-orange-500 focus:bg-white focus:ring-orange-500">
                        <option value="">{{ __('issueboard::issueboard.all_priorities') ?? 'All Priorities' }}</option>
                        <option value="4">{{ __('issueboard::issueboard.priority.4') ?? 'Urgent' }}</option>
                        <option value="1">{{ __('issueboard::issueboard.priority.1') }}</option>
                        <option value="2">{{ __('issueboard::issueboard.priority.2') }}</option>
                        <option value="3">{{ __('issueboard::issueboard.priority.3') }}</option>
                    </select>

                    <!-- Assignee Dropdown -->
                    @if ($teamMembers->isNotEmpty())
                        <select wire:model.live="assigneeId"
                                class="rounded-xl border-slate-300 bg-slate-50 py-2 text-xs font-semibold focus:border-orange-500 focus:bg-white focus:ring-orange-500">
                            <option value="">{{ __('issueboard::issueboard.all_assignees') ?? 'All Assignees' }}</option>
                            <option value="unassigned">{{ __('issueboard::issueboard.unassigned') }}</option>
                            @foreach ($teamMembers as $member)
                                <option value="{{ $member->id }}">{{ $member->name }}</option>
                            @endforeach
                        </select>
                    @endif

                    <!-- Labels Dropdown -->
                    @if ($labels->isNotEmpty())
                        <select wire:model.live="labelId"
                                class="rounded-xl border-slate-300 bg-slate-50 py-2 text-xs font-semibold focus:border-orange-500 focus:bg-white focus:ring-orange-500">
                            <option value="">{{ __('issueboard::issueboard.all_labels') ?? 'All Labels' }}</option>
                            @foreach ($labels as $label)
                                <option value="{{ $label->id }}">{{ $label->name }}</option>
                            @endforeach
                        </select>
                    @endif

                    <label class="inline-flex min-h-9 items-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50 px-2.5 text-xs font-semibold text-slate-600 cursor-pointer">
                        <input type="checkbox" wire:model.live="onlyMine"
                               class="rounded border-slate-300 text-orange-500 focus:ring-orange-500">
                        {{ __('issueboard::issueboard.only_mine') }}
                    </label>

                    @if ($hasFilters)
                        <button type="button" wire:click="resetFilters"
                                class="text-xs font-bold text-rose-600 hover:text-rose-700 transition">
                            {{ __('issueboard::issueboard.reset_filters') }}
                        </button>
                    @endif
                </div>

                <!-- Right Side Actions: View Switcher & CSV Export -->
                <div class="flex items-center gap-2 shrink-0">
                    <!-- Export CSV Button -->
                    <button type="button" wire:click="exportCsv"
                            title="Export to CSV"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold text-slate-700 transition hover:bg-slate-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-orange-500">
                        <svg class="h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M7.5 12 12 16.5m0 0L16.5 12M12 16.5V3" />
                        </svg>
                        <span>CSV Export</span>
                    </button>

                    <!-- View Switcher (Kanban / Calendar) -->
                    <div class="inline-flex rounded-xl border border-slate-200 bg-slate-100 p-0.5">
                        <button type="button" wire:click="setViewMode('board')"
                                class="flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-extrabold transition {{ $viewMode === 'board' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-900' }}">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 4.5v15m6-15v15m-10.5-15h15a2.25 2.25 0 0 1 2.25 2.25v10.5A2.25 2.25 0 0 1 19.5 19.5h-15A2.25 2.25 0 0 1 3 17.25V6.75A2.25 2.25 0 0 1 5.25 4.5Z" />
                            </svg>
                            <span>{{ __('app.navigation.board') ?? 'Board' }}</span>
                        </button>
                        <button type="button" wire:click="setViewMode('calendar')"
                                class="flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-extrabold transition {{ $viewMode === 'calendar' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-900' }}">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3.75 9h16.5m-15 12h13.5a1.5 1.5 0 0 0 1.5-1.5V6.75a1.5 1.5 0 0 0-1.5-1.5H5.25a1.5 1.5 0 0 0-1.5 1.5V19.5A1.5 1.5 0 0 0 5.25 21Z" />
                            </svg>
                            <span>{{ __('issueboard::issueboard.calendar') ?? 'Calendar' }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if ($viewMode === 'calendar')
        @php
            $weekDays = app()->getLocale() === 'de' 
                ? [
                    ['short' => 'Mo', 'full' => 'Montag'],
                    ['short' => 'Di', 'full' => 'Dienstag'],
                    ['short' => 'Mi', 'full' => 'Mittwoch'],
                    ['short' => 'Do', 'full' => 'Donnerstag'],
                    ['short' => 'Fr', 'full' => 'Freitag'],
                    ['short' => 'Sa', 'full' => 'Samstag'],
                    ['short' => 'So', 'full' => 'Sonntag'],
                ] 
                : [
                    ['short' => 'Mon', 'full' => 'Monday'],
                    ['short' => 'Tue', 'full' => 'Tuesday'],
                    ['short' => 'Wed', 'full' => 'Wednesday'],
                    ['short' => 'Thu', 'full' => 'Thursday'],
                    ['short' => 'Fri', 'full' => 'Friday'],
                    ['short' => 'Sat', 'full' => 'Saturday'],
                    ['short' => 'Sun', 'full' => 'Sunday'],
                ];
            $monthTotalIssues = collect($calendarDays)->sum(fn ($d) => $d['issues']->count());
        @endphp

        <!-- Redesigned Calendar View -->
        <section class="overflow-hidden rounded-3xl border border-slate-200/90 bg-white shadow-xl shadow-slate-900/5">
            <!-- Calendar Navigation Header -->
            <div class="flex flex-col gap-4 border-b border-slate-200/80 bg-gradient-to-b from-white to-slate-50/50 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-wrap items-center gap-3">
                    <h2 class="text-2xl font-black tracking-tight text-slate-950 capitalize">{{ $calendarTitle }}</h2>
                    <span class="rounded-full bg-orange-50 px-3 py-1 text-xs font-extrabold text-orange-700 ring-1 ring-inset ring-orange-200">
                        {{ $monthTotalIssues }} {{ __('issueboard::issueboard.open') ?? 'Tasks' }}
                    </span>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <!-- Status Legend -->
                    <div class="hidden items-center gap-3 pr-2 lg:flex text-xs font-semibold text-slate-500">
                        @foreach ($statuses as $status)
                            <span class="inline-flex items-center gap-1.5">
                                <span class="h-2.5 w-2.5 rounded-full" style="background-color: {{ $status->color() }}"></span>
                                {{ $status->label() }}
                            </span>
                        @endforeach
                    </div>

                    <!-- Month Controls -->
                    <div class="flex items-center gap-1.5">
                        <button type="button" wire:click="previousMonth" 
                                class="flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 shadow-xs transition hover:border-slate-300 hover:bg-slate-50 active:scale-95">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                            </svg>
                        </button>
                        <button type="button" wire:click="currentMonth" 
                                class="rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-extrabold text-slate-800 shadow-xs transition hover:border-slate-300 hover:bg-slate-50 active:scale-95">
                            {{ __('issueboard::issueboard.today') }}
                        </button>
                        <button type="button" wire:click="nextMonth" 
                                class="flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 shadow-xs transition hover:border-slate-300 hover:bg-slate-50 active:scale-95">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Calendar Days Header -->
            <div class="grid grid-cols-7 border-b border-slate-200/80 bg-slate-100/70 text-center text-xs font-extrabold uppercase tracking-wider text-slate-500">
                @foreach ($weekDays as $idx => $day)
                    <div class="py-3 px-2 {{ $idx >= 5 ? 'text-slate-400 bg-slate-100' : '' }}">
                        <span class="hidden md:inline">{{ $day['full'] }}</span>
                        <span class="md:hidden">{{ $day['short'] }}</span>
                    </div>
                @endforeach
            </div>

            <!-- Calendar Grid -->
            <div class="grid grid-cols-7 divide-x divide-y divide-slate-200/80 bg-slate-200/50">
                @foreach ($calendarDays as $dayIndex => $dayInfo)
                    @php
                        $isWeekend = in_array($dayInfo['date']->dayOfWeekIso, [6, 7], true);
                    @endphp
                    <div class="min-h-[145px] sm:min-h-[165px] p-2 sm:p-2.5 flex flex-col justify-between transition-colors {{ ! $dayInfo['isCurrentMonth'] ? 'bg-slate-50/70 text-slate-400' : ($isWeekend ? 'bg-slate-50/40' : 'bg-white hover:bg-orange-50/10') }}">
                        <!-- Top Cell Header: Date Number & Count -->
                        <div class="flex items-center justify-between">
                            @if ($dayInfo['isToday'])
                                <span class="flex h-7 w-7 items-center justify-center rounded-xl bg-orange-500 text-xs font-black text-white shadow-md shadow-orange-500/30 ring-4 ring-orange-100">
                                    {{ $dayInfo['date']->format('j') }}
                                </span>
                            @else
                                <span class="flex h-7 w-7 items-center justify-center rounded-xl text-xs font-extrabold {{ $dayInfo['isCurrentMonth'] ? 'text-slate-800' : 'text-slate-400 opacity-60' }}">
                                    {{ $dayInfo['date']->format('j') }}
                                </span>
                            @endif

                            @if ($dayInfo['issues']->isNotEmpty())
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-600">
                                    {{ $dayInfo['issues']->count() }}
                                </span>
                            @endif
                        </div>

                        <!-- Issues List inside Day Cell -->
                        <div class="mt-2 space-y-1.5 overflow-y-auto max-h-28 pr-0.5 scrollbar-thin">
                            @foreach ($dayInfo['issues'] as $cIssue)
                                <a href="{{ route('issueboard.show', $cIssue) }}" 
                                   title="{{ $cIssue->title }}"
                                   class="group block rounded-xl border border-slate-200/90 bg-white p-2 shadow-xs transition duration-150 hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-md"
                                   style="border-left-width: 4px; border-left-color: {{ $cIssue->status->color() }};">
                                    <div class="flex items-center justify-between gap-1.5">
                                        <div class="flex items-center gap-1 min-w-0">
                                            @if ($cIssue->priority === 1 || $cIssue->priority === 4)
                                                <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-rose-500"></span>
                                            @endif
                                            <span class="font-mono text-[10px] font-extrabold text-slate-400 group-hover:text-orange-600 shrink-0">#{{ $cIssue->id }}</span>
                                            <p class="truncate text-xs font-bold text-slate-800 group-hover:text-orange-950">{{ $cIssue->title }}</p>
                                        </div>

                                        @if ($cIssue->assignee)
                                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-slate-900 text-[9px] font-black text-white"
                                                  title="{{ $cIssue->assignee->name }}">
                                                {{ mb_strtoupper(mb_substr($cIssue->assignee->name, 0, 1)) }}
                                            </span>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>

                        <div class="pt-1"></div>
                    </div>
                @endforeach
            </div>
        </section>
    @else
        <!-- Kanban Columns View -->
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
    @endif
</div>
