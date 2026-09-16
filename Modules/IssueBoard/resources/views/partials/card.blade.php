@php
    $thumb = $issue->relationLoaded('attachments')
        ? $issue->attachments->firstWhere('is_image', true)
        : null;
    $assigneeInitial = $issue->assignee
        ? mb_strtoupper(mb_substr($issue->assignee->name, 0, 1))
        : null;
@endphp

<article data-issue-id="{{ $issue->id }}"
         wire:key="issue-{{ $issue->id }}"
         class="group cursor-grab overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-md active:cursor-grabbing">
    @if ($thumb)
        <a href="{{ route('issueboard.show', $issue) }}" class="block overflow-hidden">
            <img src="{{ $thumb->url }}" alt="{{ $thumb->original_name }}"
                 class="h-32 w-full object-cover transition duration-300 group-hover:scale-[1.02]" loading="lazy">
        </a>
    @endif

    <div class="p-4">
        <div class="flex items-center justify-between gap-3">
            <span class="font-mono text-[11px] font-semibold text-slate-400">
                #{{ str_pad((string) $issue->id, 3, '0', STR_PAD_LEFT) }}
            </span>

            @if ($issue->priority === 1)
                <span class="rounded-full bg-rose-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-rose-700 ring-1 ring-inset ring-rose-200">
                    {{ __('issueboard::issueboard.priority.1') }}
                </span>
            @elseif ($issue->priority === 3)
                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-slate-500">
                    {{ __('issueboard::issueboard.priority.3') }}
                </span>
            @endif
        </div>

        <a href="{{ route('issueboard.show', $issue) }}"
           class="mt-2 block rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-[#ff9200] focus-visible:ring-offset-2">
            <h3 class="text-sm font-bold leading-5 text-slate-900 transition group-hover:text-[#d97706]">
                {{ $issue->title }}
            </h3>

            @if ($issue->description)
                <p class="mt-2 line-clamp-2 text-xs leading-5 text-slate-500">
                    {{ \Illuminate\Support\Str::limit($issue->description, 110) }}
                </p>
            @endif
        </a>

        @if ($issue->labels && $issue->labels->isNotEmpty())
            <div class="mt-2.5 flex flex-wrap gap-1">
                @foreach ($issue->labels as $label)
                    <span class="inline-flex items-center rounded-md px-1.5 py-0.5 text-[10px] font-bold"
                          style="background-color: {{ $label->color }}22; color: {{ $label->color }}; border: 1px solid {{ $label->color }}44;">
                        {{ $label->name }}
                    </span>
                @endforeach
            </div>
        @endif

        <div class="mt-3 flex flex-wrap items-center gap-1.5">
            <span class="inline-flex max-w-full items-center gap-1 rounded-lg bg-slate-100 px-2 py-1 text-[11px] font-medium text-slate-600">
                <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15l-.75 18H5.25L4.5 3Zm4.5 4.5h6m-6 4.5h6"/>
                </svg>
                <span class="truncate">{{ $issue->project?->name ?? __('issueboard::issueboard.all_projects') }}</span>
            </span>

            @if ($issue->due_date)
                <span @class([
                    'inline-flex items-center gap-1 rounded-lg px-2 py-1 text-[11px] font-medium',
                    'bg-rose-50 text-rose-700' => $issue->is_overdue,
                    'bg-slate-100 text-slate-600' => ! $issue->is_overdue,
                ])>
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3.75 9h16.5m-15 12h13.5a1.5 1.5 0 0 0 1.5-1.5V6.75a1.5 1.5 0 0 0-1.5-1.5H5.25a1.5 1.5 0 0 0-1.5 1.5V19.5A1.5 1.5 0 0 0 5.25 21Z"/>
                    </svg>
                    {{ $issue->due_date->format('d.m.') }}
                </span>
            @endif

            @if ($issue->spent_hours > 0 || $issue->estimated_hours > 0)
                <span class="inline-flex items-center gap-1 rounded-lg bg-slate-100 px-2 py-1 text-[11px] font-medium text-slate-600" title="Time tracked: Spent / Estimated">
                    <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    {{ $issue->spent_hours ?: 0 }}h{{ $issue->estimated_hours ? ' / '.$issue->estimated_hours.'h' : '' }}
                </span>
            @endif
        </div>

        @if (($issue->checklist_total_count ?? 0) > 0)
            <div class="mt-3">
                <div class="flex items-center justify-between text-[10px] font-bold text-slate-500">
                    <span class="inline-flex items-center gap-1">
                        <svg class="h-3 w-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        {{ $issue->checklist_completed_count }}/{{ $issue->checklist_total_count }}
                    </span>
                    <span>{{ round(($issue->checklist_completed_count / $issue->checklist_total_count) * 100) }}%</span>
                </div>
                <div class="mt-1 h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full bg-emerald-500 transition-all duration-300" style="width: {{ ($issue->checklist_completed_count / $issue->checklist_total_count) * 100 }}%"></div>
                </div>
            </div>
        @endif

        <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3">
            <div class="flex min-w-0 items-center gap-2">
                @if ($issue->assignee)
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-[#0094af] text-[11px] font-bold text-white"
                          title="{{ $issue->assignee->name }}">
                        {{ $assigneeInitial }}
                    </span>
                    <span class="truncate text-[11px] font-medium text-slate-600">{{ $issue->assignee->name }}</span>
                @else
                    <span class="inline-flex items-center gap-1 text-[11px] text-slate-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.1a7.5 7.5 0 0 1 15 0"/>
                        </svg>
                        {{ __('issueboard::issueboard.unassigned') }}
                    </span>
                @endif
            </div>

            <div class="ml-3 flex shrink-0 items-center gap-2.5 text-[11px] text-slate-400">
                @if ($issue->open_questions_count)
                    <span class="inline-flex items-center gap-1 font-semibold text-amber-700" title="{{ __('issueboard::issueboard.open_question') }}">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9a3.75 3.75 0 1 1 6.75 2.25c-.8.8-1.5 1.2-1.5 2.25m0 3h.008v.008H13.5V16.5ZM21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        </svg>
                        {{ $issue->open_questions_count }}
                    </span>
                @endif

                @if ($issue->comments_count)
                    <span class="inline-flex items-center gap-1" title="{{ __('issueboard::issueboard.comments') }}">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3h6m-9.75 9 1.5-4.5A8.25 8.25 0 1 1 8.25 18l-4.5 2.25Z"/>
                        </svg>
                        {{ $issue->comments_count }}
                    </span>
                @endif

                @if ($issue->attachments_count)
                    <span class="inline-flex items-center gap-1" title="{{ __('issueboard::issueboard.attachments') }}">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l9.9-9.9a3 3 0 0 1 4.243 4.243l-9.9 9.9a1.5 1.5 0 0 1-2.122-2.122l8.839-8.838"/>
                        </svg>
                        {{ $issue->attachments_count }}
                    </span>
                @endif
            </div>
        </div>
    </div>
</article>
