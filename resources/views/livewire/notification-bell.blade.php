<div x-data="{ open: false }" class="relative" wire:poll.45s>
    <button @click="open = !open" 
            @keydown.escape.window="open = false"
            type="button" 
            class="relative flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-500"
            aria-label="{{ __('app.notifications.title') ?? 'Notifications' }}">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
        </svg>

        @if ($unreadCount > 0)
            <span class="absolute -top-1 -right-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-orange-600 px-1.5 text-[10px] font-extrabold text-white shadow-sm ring-2 ring-white animate-pulse">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </button>

    <div x-show="open" 
         @click.outside="open = false" 
         x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute right-0 z-50 mt-2 w-80 sm:w-96 rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-900/10 overflow-hidden">
        
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3 bg-slate-50/70">
            <div class="flex items-center gap-2">
                <span class="text-sm font-extrabold text-slate-900">{{ __('app.notifications.heading') ?? 'Notifications' }}</span>
                @if ($unreadCount > 0)
                    <span class="rounded-full bg-orange-100 px-2 py-0.5 text-[11px] font-bold text-orange-700">
                        {{ $unreadCount }} {{ __('app.notifications.new') ?? 'new' }}
                    </span>
                @endif
            </div>

            @if ($unreadCount > 0)
                <button wire:click="markAllAsRead" type="button" class="text-xs font-semibold text-orange-600 hover:text-orange-700 transition">
                    {{ __('app.notifications.mark_all_read') ?? 'Mark all as read' }}
                </button>
            @endif
        </div>

        <div class="max-h-96 overflow-y-auto divide-y divide-slate-100">
            @forelse ($notifications as $notification)
                @php
                    $isUnread = is_null($notification->read_at);
                    $data = $notification->data;
                    $issueId = $data['issue_id'] ?? null;
                    $type = $data['type'] ?? 'notification';
                @endphp
                <div class="relative group p-3.5 transition {{ $isUnread ? 'bg-orange-50/40 hover:bg-orange-50/70' : 'hover:bg-slate-50' }}">
                    <div class="flex items-start gap-3">
                        <div class="shrink-0 mt-0.5">
                            @if ($type === 'mention')
                                <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-purple-100 text-purple-600">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 12a4 4 0 1 0-8 0 4 4 0 0 0 8 0Zm0 0v1.5a2.5 2.5 0 0 0 5 0V12a9 9 0 1 0-9 9m4.5-1.206a8.959 8.959 0 0 1-4.5 1.207" />
                                    </svg>
                                </span>
                            @elseif ($type === 'issue_status_changed')
                                <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                                    </svg>
                                </span>
                            @elseif ($type === 'issue_comment' || $type === 'issue_question')
                                <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.502 49.188 49.188 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                                    </svg>
                                </span>
                            @else
                                <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-orange-100 text-orange-600">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                                    </svg>
                                </span>
                            @endif
                        </div>

                        <div class="flex-1 min-w-0">
                            @if ($issueId)
                                <a href="{{ route('issueboard.show', $issueId) }}" 
                                   wire:click="markAsRead('{{ $notification->id }}')" 
                                   class="block text-xs font-bold text-slate-900 hover:text-orange-600 transition truncate">
                                    {{ $data['issue_title'] ?? $data['title'] ?? ('Issue #' . $issueId) }}
                                </a>
                            @else
                                <p class="text-xs font-bold text-slate-900 truncate">
                                    {{ $data['title'] ?? 'Notification' }}
                                </p>
                            @endif

                            <p class="text-[11px] text-slate-600 mt-0.5 line-clamp-2">
                                @if ($type === 'mention')
                                    {{ $data['message'] ?? 'You were mentioned in a comment.' }}
                                @elseif ($type === 'issue_status_changed')
                                    Status moved to <span class="font-semibold text-slate-800">{{ $data['to'] ?? '' }}</span>
                                @elseif ($type === 'issue_comment')
                                    New comment added
                                @elseif ($type === 'issue_question')
                                    New question asked
                                @else
                                    New update on this issue
                                @endif
                            </p>

                            <p class="text-[10px] text-slate-400 mt-1">
                                {{ $notification->created_at->diffForHumans() }}
                            </p>
                        </div>

                        @if ($isUnread)
                            <button wire:click="markAsRead('{{ $notification->id }}')" 
                                    title="Mark as read"
                                    type="button" 
                                    class="shrink-0 h-2 w-2 rounded-full bg-orange-600 mt-1.5 hover:scale-125 transition">
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="py-8 px-4 text-center">
                    <svg class="mx-auto h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                    </svg>
                    <p class="mt-2 text-xs font-semibold text-slate-500">{{ __('app.notifications.empty') ?? 'No notifications yet' }}</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
