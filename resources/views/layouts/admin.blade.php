<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#09090b">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('app-icons/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('app-icons/favicon-16x16.png') }}">

    <title>@yield('title', 'Admin — '.config('app.name'))</title>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-dvh bg-zinc-950 text-zinc-100 antialiased">
    <div class="flex min-h-dvh flex-col">
        <header class="sticky top-0 z-40 shrink-0 border-b border-zinc-800/80 bg-zinc-950/95 backdrop-blur">
            <div class="mx-auto flex w-full max-w-xl items-center justify-between gap-3 px-4 py-4 sm:px-6">
                <a
                    href="{{ request()->routeIs('admin.unlock') ? route('admin.unlock') : route('admin.files.index') }}"
                    class="font-semibold tracking-tight text-zinc-50 transition hover:text-amber-400"
                >
                    Admin
                </a>
            </div>
        </header>

        <main class="mx-auto flex w-full max-w-xl flex-1 flex-col px-4 py-6 sm:px-6">
            @yield('content')
        </main>
    </div>
    @stack('scripts')
</body>
</html>
