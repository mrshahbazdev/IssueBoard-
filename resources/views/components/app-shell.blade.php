@props(['title' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#020617">
    <title>{{ $title ?? __('issueboard::issueboard.board_title') }} · {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&display=swap" rel="stylesheet">
    @unless (app()->environment('testing'))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endunless
    @livewireStyles
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="min-h-full bg-slate-50 font-sans text-slate-800 antialiased">
<div class="min-h-screen">
    <header class="sticky top-0 z-30 border-b border-slate-200/80 bg-white/90 backdrop-blur-xl">
        <div class="mx-auto flex h-17 max-w-[1600px] items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
            <div class="flex min-w-0 items-center gap-5">
                <a href="{{ route('issueboard.index') }}" class="flex min-w-0 items-center gap-3" aria-label="{{ __('app.navigation.home') }}">
                    <x-brand-mark class="h-10 w-10 shrink-0 rounded-xl"/>
                    <div class="hidden min-w-0 sm:block">
                        <span class="block truncate text-sm font-extrabold tracking-tight text-slate-950">IssueBoard</span>
                        <span class="block truncate text-[11px] font-medium text-slate-400">{{ __('app.brand_tagline') }}</span>
                    </div>
                </a>

                <nav class="hidden items-center gap-1 border-l border-slate-200 pl-5 md:flex" aria-label="{{ __('app.navigation.main') }}">
                    <a href="{{ route('issueboard.index') }}"
                       @class([
                           'rounded-lg px-3 py-2 text-sm font-bold transition',
                           'bg-slate-950 text-white' => request()->routeIs('issueboard.*'),
                           'text-slate-500 hover:bg-slate-100 hover:text-slate-900' => ! request()->routeIs('issueboard.*'),
                       ])>
                        {{ __('app.navigation.board') }}
                    </a>
                    <a href="{{ \Illuminate\Support\Facades\Route::has('projects.index') ? route('projects.index') : url('/projects') }}"
                       @class([
                           'rounded-lg px-3 py-2 text-sm font-bold transition',
                           'bg-slate-950 text-white' => request()->routeIs('projects.*'),
                           'text-slate-500 hover:bg-slate-100 hover:text-slate-900' => ! request()->routeIs('projects.*'),
                       ])>
                        {{ __('app.navigation.projects') }}
                    </a>
                    @if (auth()->user()->canManageTeam())
                        <a href="{{ route('team.index') }}"
                           @class([
                               'rounded-lg px-3 py-2 text-sm font-bold transition',
                               'bg-slate-950 text-white' => request()->routeIs('team.*'),
                               'text-slate-500 hover:bg-slate-100 hover:text-slate-900' => ! request()->routeIs('team.*'),
                           ])>
                            {{ __('app.navigation.team') }}
                        </a>
                    @endif
                </nav>
            </div>

            <div class="flex items-center gap-2 sm:gap-3">
                <x-language-switcher compact/>

                @can('create', \Modules\IssueBoard\Models\Issue::class)
                    <a href="{{ route('issueboard.create') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-xl bg-orange-500 px-3.5 py-2.5 text-sm font-extrabold text-white shadow-sm transition hover:bg-orange-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-500 sm:px-4">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.3" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                        </svg>
                        <span class="hidden sm:inline">{{ __('issueboard::issueboard.new_issue') }}</span>
                    </a>
                @endcan

                <details class="group relative">
                    <summary class="flex cursor-pointer list-none items-center gap-2 rounded-xl border border-slate-200 bg-white p-1.5 pr-2.5 transition hover:border-slate-300 hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-500">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-950 text-xs font-extrabold text-white">
                            {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                        </span>
                        <span class="hidden max-w-32 truncate text-sm font-bold text-slate-700 lg:block">{{ auth()->user()->name }}</span>
                        <svg class="hidden h-4 w-4 text-slate-400 transition group-open:rotate-180 sm:block" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                        </svg>
                    </summary>
                    <div class="absolute right-0 mt-2 w-64 overflow-hidden rounded-2xl border border-slate-200 bg-white p-2 shadow-xl shadow-slate-900/10">
                        <div class="border-b border-slate-100 px-3 py-3">
                            <p class="truncate text-sm font-extrabold text-slate-900">{{ auth()->user()->name }}</p>
                            <p class="mt-0.5 truncate text-xs text-slate-500">{{ auth()->user()->email }}</p>
                            <span class="mt-2 inline-flex rounded-full px-2 py-1 text-[10px] font-extrabold uppercase tracking-wide ring-1 ring-inset {{ auth()->user()->role->badgeClasses() }}">
                                {{ auth()->user()->role->label() }}
                            </span>
                        </div>
                        <a href="{{ route('profile.edit') }}" class="mt-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-950">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.1a7.5 7.5 0 0 1 15 0 17.9 17.9 0 0 1-15 0Z"/></svg>
                            {{ __('app.navigation.profile') }}
                        </a>
                        <a href="{{ \Illuminate\Support\Facades\Route::has('projects.index') ? route('projects.index') : url('/projects') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-950 md:hidden">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z"/></svg>
                            {{ __('app.navigation.projects') }}
                        </a>
                        @if (auth()->user()->canManageTeam())
                            <a href="{{ route('team.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-950 md:hidden">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.1 9.1 0 0 0 3.74-.48 3.75 3.75 0 0 0-5.73-3.26M18 18.72v-.01c0-1.31-.34-2.54-.94-3.61M18 18.72v.13A12 12 0 0 1 12 20.25c-2.18 0-4.23-.58-6-1.59v-.16a6 6 0 0 1 11.06-3.4M15 7.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"/></svg>
                                {{ __('app.navigation.manage_team') }}
                            </a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-slate-600 hover:bg-rose-50 hover:text-rose-700">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6A2.25 2.25 0 0 0 5.25 5.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/></svg>
                                {{ __('app.navigation.sign_out') }}
                            </button>
                        </form>
                    </div>
                </details>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-[1600px] px-4 py-5 sm:px-6 sm:py-7 lg:px-8">
        @if (session('status'))
            <div class="mb-5 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                {{ session('status') }}
            </div>
        @endif

        {{ $slot }}
    </main>
</div>

<div x-data="{ show: false, message: '', error: false }"
     x-on:board-updated.window="message = $event.detail.message; error = false; show = true; setTimeout(() => show = false, 2500)"
     x-on:board-error.window="message = $event.detail.message; error = true; show = true; setTimeout(() => show = false, 4000)"
     x-show="show" x-cloak
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="translate-y-2 opacity-0"
     x-transition:enter-end="translate-y-0 opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="translate-y-0 opacity-100"
     x-transition:leave-end="translate-y-2 opacity-0"
     :class="error ? 'bg-rose-700' : 'bg-slate-950'"
     class="fixed bottom-6 left-1/2 z-50 flex max-w-[calc(100%-2rem)] -translate-x-1/2 items-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold text-white shadow-xl"
     role="status" aria-live="polite">
    <span class="h-2 w-2 rounded-full bg-current opacity-70"></span>
    <span x-text="message"></span>
</div>

@livewireScripts
</body>
</html>
