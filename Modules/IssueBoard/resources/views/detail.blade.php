<div class="mx-auto max-w-7xl">
    <a href="{{ route('issueboard.index') }}"
       class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-[#d97706]">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
        </svg>
        {{ __('issueboard::issueboard.back_to_board') }}
    </a>

    <section class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="h-1.5" style="background: {{ $issue->status->color() }}"></div>
        <div class="flex flex-col gap-5 p-5 sm:p-7 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2 text-xs font-medium text-slate-500">
                    <span class="font-mono font-bold text-slate-400">#{{ str_pad((string) $issue->id, 3, '0', STR_PAD_LEFT) }}</span>
                    <span aria-hidden="true">·</span>
                    <span>{{ $issue->project?->name ?? __('issueboard::issueboard.all_projects') }}</span>
                    <span aria-hidden="true">·</span>
                    <time datetime="{{ $issue->created_at->toDateString() }}">{{ $issue->created_at->format('d.m.Y') }}</time>
                </div>
                <h1 class="mt-3 max-w-4xl text-2xl font-bold leading-tight tracking-tight text-slate-900 sm:text-3xl">
                    {{ $issue->title }}
                </h1>
                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-bold"
                          style="background: {{ $issue->status->tint() }}; color: {{ $issue->status->color() }}">
                        <span class="h-2 w-2 rounded-full" style="background: {{ $issue->status->color() }}"></span>
                        {{ $issue->status->label() }}
                    </span>
                    <span @class([
                        'rounded-full px-3 py-1.5 text-xs font-bold',
                        'bg-rose-50 text-rose-700' => $issue->priority === 1,
                        'bg-amber-50 text-amber-700' => $issue->priority === 2,
                        'bg-slate-100 text-slate-600' => $issue->priority === 3,
                    ])>
                        {{ $issue->priority_label }}
                    </span>
                    @if ($issue->due_date)
                        <span @class([
                            'inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold',
                            'bg-rose-50 text-rose-700' => $issue->is_overdue,
                            'bg-slate-100 text-slate-600' => ! $issue->is_overdue,
                        ])>
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3.75 9h16.5m-15 12h13.5a1.5 1.5 0 0 0 1.5-1.5V6.75a1.5 1.5 0 0 0-1.5-1.5H5.25a1.5 1.5 0 0 0-1.5 1.5V19.5A1.5 1.5 0 0 0 5.25 21Z"/>
                            </svg>
                            {{ $issue->due_date->format('d.m.Y') }}
                        </span>
                    @endif
                </div>
            </div>

            @can('update', $issue)
                <a href="{{ route('issueboard.edit', $issue) }}"
                   class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:border-[#ff9200] hover:text-[#d97706]">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.862 4.487ZM16.862 4.487 19.5 7.125"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 13.5V19.125A1.875 1.875 0 0 1 16.125 21H4.875A1.875 1.875 0 0 1 3 19.125V7.875A1.875 1.875 0 0 1 4.875 6H10.5"/>
                    </svg>
                    {{ __('issueboard::issueboard.edit') }}
                </a>
            @endcan
        </div>
    </section>

    <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_340px] lg:items-start">
        <div class="space-y-6">
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5A3.375 3.375 0 0 0 10.125 2.25H8.25m0 12.75h7.5m-7.5 3h4.5m-7.5 3h13.5A2.25 2.25 0 0 0 21 18.75V11.25a9 9 0 0 0-9-9H5.25A2.25 2.25 0 0 0 3 4.5v14.25A2.25 2.25 0 0 0 5.25 21Z"/>
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">{{ __('issueboard::issueboard.issue_context') }}</h2>
                        <p class="mt-0.5 text-xs text-slate-500">{{ __('issueboard::issueboard.issue_context_help') }}</p>
                    </div>
                </div>

                <div class="mt-6">
                    @if ($issue->description)
                        <div class="issueboard-rich-text text-sm leading-7 text-slate-700">
                            {!! \Modules\IssueBoard\Support\RichText::clean($issue->description) !!}
                        </div>
                    @else
                        <p class="text-sm italic text-slate-400">{{ __('issueboard::issueboard.no_description') }}</p>
                    @endif
                </div>

                @if ($issue->suggested_solution)
                    <div class="mt-6 rounded-2xl border border-cyan-200 bg-cyan-50/60 p-5">
                        <div class="flex items-center gap-2 text-sm font-bold text-[#007d94]">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.456-2.456L14.25 6l1.035-.259a3.375 3.375 0 0 0 2.456-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z"/>
                            </svg>
                            {{ __('issueboard::issueboard.suggested_solution') }}
                        </div>
                        <div class="issueboard-rich-text mt-3 text-sm leading-6 text-slate-700">
                            {!! \Modules\IssueBoard\Support\RichText::clean($issue->suggested_solution) !!}
                        </div>
                    </div>
                @endif
            </section>

            @if ($issue->links->isNotEmpty() || $issue->attachments->isNotEmpty())
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#fff4e5] text-[#d97706]">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l9.9-9.9a3 3 0 0 1 4.243 4.243l-9.9 9.9a1.5 1.5 0 0 1-2.122-2.122l8.839-8.838"/>
                            </svg>
                        </span>
                        <div>
                            <h2 class="text-base font-bold text-slate-900">{{ __('issueboard::issueboard.reference_material') }}</h2>
                            <p class="mt-0.5 text-xs text-slate-500">{{ __('issueboard::issueboard.reference_material_help') }}</p>
                        </div>
                    </div>

                    @if ($issue->links->isNotEmpty())
                        <div class="mt-5 grid gap-2 sm:grid-cols-2">
                            @foreach ($issue->links as $link)
                                <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer"
                                   class="group flex min-w-0 items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 transition hover:border-[#0094af] hover:bg-cyan-50/50">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white text-[#0094af] shadow-sm">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H18A3 3 0 0 1 21 9v9a3 3 0 0 1-3 3H9a3 3 0 0 1-3-3v-4.5m4.5-6H6A3 3 0 0 0 3 10.5v7.5"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 3H21v7.5M21 3l-9 9"/>
                                        </svg>
                                    </span>
                                    <span class="min-w-0 flex-1 truncate text-sm font-semibold text-slate-700 group-hover:text-[#007d94]">{{ $link->display_label }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif

                    @if ($issue->attachments->isNotEmpty())
                        <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3">
                            @foreach ($issue->attachments as $attachment)
                                <a href="{{ $attachment->url }}" target="_blank" rel="noopener noreferrer"
                                   class="group block overflow-hidden rounded-xl border border-slate-200 bg-white transition hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-md">
                                    @if ($attachment->is_image)
                                        <img src="{{ $attachment->url }}" alt="{{ $attachment->original_name }}"
                                             class="h-36 w-full object-cover transition duration-300 group-hover:scale-[1.02]" loading="lazy">
                                    @else
                                        <div class="flex h-36 flex-col items-center justify-center bg-slate-50 px-4 text-center">
                                            <svg class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5A3.375 3.375 0 0 0 10.125 2.25H6.75A2.25 2.25 0 0 0 4.5 4.5v15a2.25 2.25 0 0 0 2.25 2.25h10.5A2.25 2.25 0 0 0 19.5 19.5v-5.25Z"/>
                                            </svg>
                                            <span class="mt-2 line-clamp-2 text-xs font-medium text-slate-600">{{ $attachment->original_name }}</span>
                                        </div>
                                    @endif
                                    <div class="flex items-center justify-between gap-2 px-3 py-2">
                                        <p class="min-w-0 flex-1 truncate text-xs font-medium text-slate-600">{{ $attachment->original_name }}</p>
                                        <span class="shrink-0 text-[10px] text-slate-400">{{ $attachment->readable_size }}</span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </section>
            @endif

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-700">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3h6m-9.75 9 1.5-4.5A8.25 8.25 0 1 1 8.25 18l-4.5 2.25Z"/>
                            </svg>
                        </span>
                        <div>
                            <h2 class="text-base font-bold text-slate-900">{{ __('issueboard::issueboard.comments') }}</h2>
                            <p class="mt-0.5 text-xs text-slate-500">{{ __('issueboard::issueboard.comments_help') }}</p>
                        </div>
                    </div>
                    @if ($openQuestionCount)
                        <span class="self-start rounded-full bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-700 ring-1 ring-inset ring-amber-200">
                            {{ trans_choice('issueboard::issueboard.open_questions', $openQuestionCount, ['count' => $openQuestionCount]) }}
                        </span>
                    @endif
                </div>

                <ul class="mt-6 space-y-4">
                    @forelse ($comments as $comment)
                        @php($commentInitial = mb_strtoupper(mb_substr($comment->user->name, 0, 1)))
                        <li wire:key="comment-{{ $comment->id }}"
                            @class([
                                'rounded-2xl border p-4 sm:p-5',
                                'border-amber-200 bg-amber-50/60' => $comment->is_open_question,
                                'border-slate-200 bg-slate-50/70' => ! $comment->is_open_question,
                            ])>
                            <div class="flex items-start gap-3">
                                <span @class([
                                    'flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-xs font-bold text-white',
                                    'bg-amber-600' => $comment->is_open_question,
                                    'bg-slate-700' => ! $comment->is_open_question,
                                ])>
                                    {{ $commentInitial }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="text-sm font-bold text-slate-900">{{ $comment->user->name }}</p>
                                            @if ($comment->is_question)
                                                <span @class([
                                                    'rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide',
                                                    'bg-amber-100 text-amber-800' => ! $comment->answered_at,
                                                    'bg-emerald-100 text-emerald-700' => $comment->answered_at,
                                                ])>
                                                    {{ $comment->answered_at
                                                        ? __('issueboard::issueboard.answered')
                                                        : __('issueboard::issueboard.open_question') }}
                                                </span>
                                            @endif
                                        </div>
                                        <time class="text-xs text-slate-400">{{ $comment->created_at->format('d.m.Y H:i') }}</time>
                                    </div>

                                    <div class="issueboard-rich-text mt-2 text-sm leading-6 text-slate-700">
                                        {!! \Modules\IssueBoard\Support\RichText::clean($comment->body) !!}
                                    </div>

                                    @if ($comment->attachments->isNotEmpty())
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            @foreach ($comment->attachments as $attachment)
                                                <a href="{{ $attachment->url }}" target="_blank" rel="noopener noreferrer"
                                                   class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-medium text-[#007d94] hover:border-[#0094af]">
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l9.9-9.9"/>
                                                    </svg>
                                                    {{ $attachment->original_name }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif

                                    <div class="mt-3 flex flex-wrap items-center gap-4 text-xs font-semibold">
                                        @can('comment', $issue)
                                            <button type="button" wire:click="startReply({{ $comment->id }})"
                                                    class="text-slate-500 hover:text-slate-900">
                                                {{ __('issueboard::issueboard.reply') }}
                                            </button>

                                            @if ($comment->is_open_question)
                                                <button type="button" wire:click="markAnswered({{ $comment->id }})"
                                                        class="text-emerald-700 hover:text-emerald-900">
                                                    {{ __('issueboard::issueboard.mark_answered') }}
                                                </button>
                                            @endif

                                            @if ($comment->user_id === auth()->id() || auth()->user()->can('delete', $issue))
                                                <button type="button" wire:click="deleteComment({{ $comment->id }})"
                                                        wire:confirm="{{ __('issueboard::issueboard.confirm_delete_comment') }}"
                                                        class="text-slate-400 hover:text-rose-700">
                                                    {{ __('issueboard::issueboard.delete') }}
                                                </button>
                                            @endif
                                        @endcan
                                    </div>
                                </div>
                            </div>

                            @if ($comment->replies->isNotEmpty())
                                <ul class="ml-4 mt-4 space-y-3 border-l-2 border-slate-200 pl-5 sm:ml-12">
                                    @foreach ($comment->replies as $reply)
                                        <li wire:key="reply-{{ $reply->id }}" class="rounded-xl bg-white p-4 shadow-sm">
                                            <div class="flex flex-wrap items-center justify-between gap-2">
                                                <p class="text-sm font-bold text-slate-900">{{ $reply->user->name }}</p>
                                                <time class="text-xs text-slate-400">{{ $reply->created_at->format('d.m.Y H:i') }}</time>
                                            </div>
                                            <div class="issueboard-rich-text mt-2 text-sm leading-6 text-slate-700">
                                                {!! \Modules\IssueBoard\Support\RichText::clean($reply->body) !!}
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    @empty
                        <li class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-5 py-8 text-center">
                            <span class="mx-auto flex h-11 w-11 items-center justify-center rounded-full bg-white text-slate-400 shadow-sm">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.142-4.03 7.5-9 7.5S3 16.142 3 12s4.03-7.5 9-7.5 9 3.358 9 7.5Z"/>
                                </svg>
                            </span>
                            <p class="mt-3 text-sm text-slate-500">{{ __('issueboard::issueboard.no_comments') }}</p>
                        </li>
                    @endforelse
                </ul>

                @can('comment', $issue)
                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                    @if ($replyTo)
                        <div class="mb-3 flex items-center justify-between gap-3 rounded-lg bg-cyan-50 px-3 py-2 text-xs font-medium text-[#007d94]">
                            <span>{{ __('issueboard::issueboard.replying') }}</span>
                            <button type="button" wire:click="cancelReply" class="font-bold hover:text-slate-900">
                                {{ __('issueboard::issueboard.cancel') }}
                            </button>
                        </div>
                    @endif

                    <label for="comment-body" class="sr-only">{{ __('issueboard::issueboard.comment_placeholder') }}</label>
                    <x-rich-text-editor
                        id="comment-body"
                        wire:model="body"
                        :placeholder="__('issueboard::issueboard.comment_placeholder')"
                        min-height="min-h-28"
                        focus-on-comment
                    />
                    @error('body') <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror

                    <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-center">
                        <label class="inline-flex cursor-pointer items-center gap-2 text-xs font-semibold text-slate-500 hover:text-[#007d94]">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l9.9-9.9"/>
                            </svg>
                            {{ __('issueboard::issueboard.attach_files') }}
                            <input type="file" wire:model="commentFiles" multiple class="sr-only">
                        </label>

                        @if (! $replyTo)
                            <label class="inline-flex cursor-pointer items-center gap-2 text-xs font-semibold text-slate-600">
                                <input type="checkbox" wire:model="isQuestion"
                                       class="rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                                {{ __('issueboard::issueboard.as_question') }}
                            </label>
                        @endif

                        <button type="button" wire:click="addComment" wire:loading.attr="disabled" wire:target="addComment"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-slate-700 disabled:opacity-50 sm:ml-auto">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m6 12 3.269 3.269a2.25 2.25 0 0 0 3.182 0L18 9.72M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z"/>
                            </svg>
                            {{ __('issueboard::issueboard.post_comment') }}
                        </button>
                    </div>

                    @if ($commentFiles)
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($commentFiles as $file)
                                <span class="max-w-full truncate rounded-lg bg-slate-100 px-2.5 py-1.5 text-xs text-slate-600">
                                    {{ $file->getClientOriginalName() }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                    </div>
                @else
                    <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-500">
                        {{ __('issueboard::issueboard.viewer_read_only') }}
                    </div>
                @endcan
            </section>
        </div>

        <aside class="space-y-5 lg:sticky lg:top-24">
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-sm font-bold text-slate-900">{{ __('issueboard::issueboard.move_work_forward') }}</h2>
                <p class="mt-1 text-xs leading-5 text-slate-500">{{ __('issueboard::issueboard.move_work_forward_help') }}</p>

                <div class="mt-4 space-y-2">
                    @foreach ($statuses as $status)
                        @can('moveTo', [$issue, $status])
                            <button type="button" wire:click="setStatus('{{ $status->value }}')"
                                    @class([
                                        'flex w-full items-center gap-3 rounded-xl border px-3 py-3 text-left text-sm transition',
                                        'font-bold shadow-sm' => $issue->status === $status,
                                        'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50' => $issue->status !== $status,
                                    ])
                                    @style([
                                        'border-color: '.$status->color() => $issue->status === $status,
                                        'background: '.$status->tint() => $issue->status === $status,
                                        'color: '.$status->color() => $issue->status === $status,
                                    ])>
                                <span class="h-3 w-3 shrink-0 rounded-full" style="background: {{ $status->color() }}"></span>
                                <span class="min-w-0 flex-1">
                                    <span class="block">{{ $status->label() }}</span>
                                    <span class="mt-0.5 block text-[10px] font-medium opacity-70">
                                        {{ __('issueboard::issueboard.status_help.'.$status->value) }}
                                    </span>
                                </span>
                                @if ($issue->status === $status)
                                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                                    </svg>
                                @endif
                            </button>
                        @endcan
                    @endforeach
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-sm font-bold text-slate-900">{{ __('issueboard::issueboard.people_and_contact') }}</h2>
                <dl class="mt-4 divide-y divide-slate-100 text-sm">
                    <div class="flex items-center gap-3 py-3 first:pt-0">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-600">
                            {{ $issue->author ? mb_strtoupper(mb_substr($issue->author->name, 0, 1)) : '—' }}
                        </span>
                        <div class="min-w-0">
                            <dt class="text-[10px] font-bold uppercase tracking-wide text-slate-400">{{ __('issueboard::issueboard.reported_by') }}</dt>
                            <dd class="truncate font-semibold text-slate-800">{{ $issue->author?->name ?? '—' }}</dd>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 py-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-cyan-50 text-xs font-bold text-[#007d94]">
                            {{ $issue->assignee ? mb_strtoupper(mb_substr($issue->assignee->name, 0, 1)) : '—' }}
                        </span>
                        <div class="min-w-0">
                            <dt class="text-[10px] font-bold uppercase tracking-wide text-slate-400">{{ __('issueboard::issueboard.assignee') }}</dt>
                            <dd class="truncate font-semibold text-slate-800">{{ $issue->assignee?->name ?? __('issueboard::issueboard.unassigned') }}</dd>
                        </div>
                    </div>
                    @if ($issue->contact_line)
                        <div class="py-3 last:pb-0">
                            <dt class="text-[10px] font-bold uppercase tracking-wide text-slate-400">{{ __('issueboard::issueboard.contact') }}</dt>
                            <dd class="mt-2 space-y-1 text-sm">
                                @if ($issue->contact_name)
                                    <p class="font-semibold text-slate-800">{{ $issue->contact_name }}</p>
                                @endif
                                @if ($issue->contact_email)
                                    <a href="mailto:{{ $issue->contact_email }}" class="block break-all font-medium text-[#007d94] hover:underline">{{ $issue->contact_email }}</a>
                                @endif
                                @if ($issue->contact_phone)
                                    <a href="tel:{{ $issue->contact_phone }}" class="block font-medium text-slate-600 hover:text-slate-900">{{ $issue->contact_phone }}</a>
                                @endif
                            </dd>
                        </div>
                    @endif
                </dl>
            </section>

            @if ($history->isNotEmpty())
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-sm font-bold text-slate-900">{{ __('issueboard::issueboard.history') }}</h2>
                    <ul class="relative mt-4 space-y-4 before:absolute before:bottom-2 before:left-[5px] before:top-2 before:w-px before:bg-slate-200">
                        @foreach ($history as $log)
                            <li class="relative flex gap-3">
                                <span class="relative z-10 mt-1 h-2.5 w-2.5 shrink-0 rounded-full ring-4 ring-white"
                                      style="background: {{ $log->to_status->color() }}"></span>
                                <div class="min-w-0">
                                    <p class="text-xs leading-5 text-slate-600">
                                        <span class="font-semibold text-slate-800">{{ $log->user->name }}</span>
                                        {{ __('issueboard::issueboard.moved_to') }}
                                        <span class="font-semibold" style="color: {{ $log->to_status->color() }}">{{ $log->to_status->label() }}</span>
                                    </p>
                                    <time class="mt-0.5 block text-[10px] text-slate-400">{{ $log->created_at->format('d.m.Y H:i') }}</time>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </aside>
    </div>
</div>
