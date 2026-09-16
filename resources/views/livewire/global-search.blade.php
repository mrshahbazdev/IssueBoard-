<div x-data="{
        open: @entangle('isOpen'),
        init() {
            window.addEventListener('keydown', (e) => {
                if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                    e.preventDefault();
                    this.open = true;
                    $nextTick(() => this.$refs.searchInput?.focus());
                }
            });
        }
     }">
    <!-- Header Trigger Button -->
    <button @click="open = true; $nextTick(() => $refs.searchInput?.focus())"
            type="button"
            class="hidden md:flex items-center gap-2 rounded-xl border border-slate-200/90 bg-slate-50/80 px-3 py-1.5 text-xs font-semibold text-slate-500 transition hover:border-slate-300 hover:bg-white hover:text-slate-800 shadow-xs">
        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
        </svg>
        <span class="w-24 text-left truncate">{{ __('app.search.quick_search') ?? 'Quick search...' }}</span>
        <kbd class="rounded border border-slate-200 bg-white px-1.5 py-0.5 text-[10px] font-bold text-slate-500 shadow-xs">Ctrl K</kbd>
    </button>

    <!-- Modal Backdrop & Dialog -->
    <div x-show="open"
         x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-start justify-center p-4 sm:p-6 md:p-20 bg-slate-950/40 backdrop-blur-xs">
        
        <div @click.outside="open = false; $wire.close()"
             @keydown.escape.window="open = false; $wire.close()"
             x-show="open"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="w-full max-w-2xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">

            <!-- Search Input -->
            <div class="relative flex items-center border-b border-slate-100 px-4">
                <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                <input x-ref="searchInput"
                       wire:model.live.debounce.250ms="query"
                       type="text"
                       placeholder="{{ __('app.search.placeholder') ?? 'Type to search issues, projects, or team members...' }}"
                       class="w-full border-0 bg-transparent py-4 pl-3 pr-10 text-sm font-semibold text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-0">
                
                <button @click="open = false; $wire.close()" type="button" class="text-slate-400 hover:text-slate-600">
                    <kbd class="rounded border border-slate-200 bg-slate-100 px-1.5 py-0.5 text-[11px] font-bold text-slate-500">ESC</kbd>
                </button>
            </div>

            <!-- Search Results Area -->
            <div class="max-h-[60vh] overflow-y-auto p-3 space-y-4">
                @if (mb_strlen(trim($query)) >= 2)
                    @if ($issues->isEmpty() && $projects->isEmpty() && $users->isEmpty())
                        <div class="py-10 text-center">
                            <p class="text-sm font-semibold text-slate-600">{{ __('app.search.no_results') ?? 'No results found for' }} "<span class="font-bold text-slate-900">{{ $query }}</span>"</p>
                        </div>
                    @else
                        <!-- Issues Section -->
                        @if ($issues->isNotEmpty())
                            <div>
                                <h3 class="px-3 text-[11px] font-extrabold uppercase tracking-wider text-slate-400">
                                    {{ __('app.search.issues') ?? 'Issues' }} ({{ $issues->count() }})
                                </h3>
                                <div class="mt-1 space-y-1">
                                    @foreach ($issues as $issue)
                                        <a href="{{ route('issueboard.show', $issue) }}" 
                                           class="flex items-center justify-between rounded-xl px-3 py-2 text-sm transition hover:bg-orange-50/70 group">
                                            <div class="flex items-center gap-3 min-w-0">
                                                <span class="font-mono text-xs font-bold text-orange-600 shrink-0">#{{ str_pad($issue->id, 3, '0', STR_PAD_LEFT) }}</span>
                                                <span class="font-bold text-slate-800 group-hover:text-orange-950 truncate">{{ $issue->title }}</span>
                                                @if ($issue->project)
                                                    <span class="hidden sm:inline-flex rounded-md bg-slate-100 px-1.5 py-0.5 text-[10px] font-bold text-slate-600">{{ $issue->project->name }}</span>
                                                @endif
                                            </div>
                                            <span class="shrink-0 text-[10px] font-extrabold uppercase tracking-wide rounded-full px-2 py-0.5 {{ $issue->status->badgeClasses() }}">
                                                {{ $issue->status->label() }}
                                            </span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Projects Section -->
                        @if ($projects->isNotEmpty())
                            <div>
                                <h3 class="px-3 text-[11px] font-extrabold uppercase tracking-wider text-slate-400">
                                    {{ __('app.search.projects') ?? 'Projects' }} ({{ $projects->count() }})
                                </h3>
                                <div class="mt-1 space-y-1">
                                    @foreach ($projects as $project)
                                        <a href="{{ route('issueboard.index', ['project' => $project->id]) }}" 
                                           class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-bold text-slate-800 hover:bg-slate-100 transition">
                                            <svg class="h-4 w-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                                            </svg>
                                            <span class="truncate">{{ $project->name }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Team Members Section -->
                        @if ($users->isNotEmpty())
                            <div>
                                <h3 class="px-3 text-[11px] font-extrabold uppercase tracking-wider text-slate-400">
                                    {{ __('app.search.team') ?? 'Team Members' }} ({{ $users->count() }})
                                </h3>
                                <div class="mt-1 space-y-1">
                                    @foreach ($users as $u)
                                        <div class="flex items-center justify-between rounded-xl px-3 py-2 text-sm">
                                            <div class="flex items-center gap-3 min-w-0">
                                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-slate-900 text-xs font-extrabold text-white">
                                                    {{ mb_strtoupper(mb_substr($u->name, 0, 1)) }}
                                                </span>
                                                <div class="truncate">
                                                    <p class="font-bold text-slate-900 leading-tight truncate">{{ $u->name }}</p>
                                                    <p class="text-[11px] text-slate-400 leading-tight truncate">{{ $u->email }}</p>
                                                </div>
                                            </div>
                                            <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wide ring-1 ring-inset {{ $u->role->badgeClasses() }}">
                                                {{ $u->role->label() }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endif
                @else
                    <div class="py-8 text-center text-xs text-slate-400">
                        Type at least 2 characters to search across issues, projects, and team members.
                    </div>
                @endif
            </div>

            <!-- Footer Hints -->
            <div class="flex items-center justify-between border-t border-slate-100 bg-slate-50/70 px-4 py-2 text-[11px] text-slate-500">
                <span>Navigate quickly with <kbd class="rounded border border-slate-200 bg-white px-1 font-bold">Ctrl</kbd> + <kbd class="rounded border border-slate-200 bg-white px-1 font-bold">K</kbd></span>
                <span>Press <kbd class="rounded border border-slate-200 bg-white px-1 font-bold">Esc</kbd> to close</span>
            </div>
        </div>
    </div>
</div>
