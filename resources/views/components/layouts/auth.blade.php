<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#020617">
    <title>{{ $title }} · {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&display=swap" rel="stylesheet">
    @unless (app()->environment('testing'))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endunless
</head>
<body class="min-h-full bg-slate-950 font-sans text-slate-900 antialiased">
<main class="relative grid min-h-screen lg:grid-cols-[minmax(0,1.05fr)_minmax(520px,.95fr)]">
    <div class="absolute right-5 top-5 z-20">
        <x-language-switcher/>
    </div>

    <section class="relative hidden overflow-hidden border-r border-white/10 bg-slate-950 p-12 text-white lg:flex lg:flex-col lg:justify-between xl:p-16">
        <div class="absolute inset-0 opacity-80" style="background: radial-gradient(circle at 25% 15%, rgba(34,211,238,.16), transparent 28rem), radial-gradient(circle at 85% 80%, rgba(251,146,60,.18), transparent 30rem)"></div>
        <div class="relative flex items-center gap-3">
            <x-brand-mark class="h-12 w-12"/>
            <div>
                <p class="text-lg font-extrabold tracking-tight">IssueBoard</p>
                <p class="text-xs font-medium text-slate-400">{{ __('app.brand_tagline') }}</p>
            </div>
        </div>

        <div class="relative max-w-xl">
            <p class="text-sm font-bold uppercase tracking-[0.22em] text-orange-300">{{ __('app.auth_intro.eyebrow') }}</p>
            <h1 class="mt-6 text-5xl font-extrabold leading-[1.08] tracking-tight xl:text-6xl">
                {{ __('app.auth_intro.title') }}
            </h1>
            <p class="mt-6 max-w-lg text-lg leading-8 text-slate-300">
                {{ __('app.auth_intro.description') }}
            </p>
        </div>

        <div class="relative grid grid-cols-4 gap-2">
            @foreach ([
                [__('issueboard::issueboard.status.new'), 'bg-red-500'],
                [__('issueboard::issueboard.status.review'), 'bg-amber-500'],
                [__('issueboard::issueboard.status.in_progress'), 'bg-blue-500'],
                [__('issueboard::issueboard.status.done'), 'bg-emerald-500'],
            ] as [$label, $color])
                <div class="rounded-xl border border-white/10 bg-white/5 p-3">
                    <span class="block h-2 w-8 rounded-full {{ $color }}"></span>
                    <span class="mt-3 block text-xs font-semibold text-slate-300">{{ $label }}</span>
                </div>
            @endforeach
        </div>
    </section>

    <section class="flex min-h-screen items-center justify-center bg-slate-50 px-5 py-10 sm:px-10">
        <div class="w-full max-w-md">
            <div class="mb-9 flex items-center gap-3 lg:hidden">
                <x-brand-mark class="h-11 w-11"/>
                <div>
                    <p class="font-extrabold tracking-tight">IssueBoard</p>
                    <p class="text-xs text-slate-500">{{ __('app.brand_tagline') }}</p>
                </div>
            </div>

            {{ $slot }}
        </div>
    </section>
</main>
</body>
</html>
