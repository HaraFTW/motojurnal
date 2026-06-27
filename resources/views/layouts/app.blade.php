<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#09090b">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="{{ config('app.name') }}">

    <link rel="manifest" href="{{ route('manifest') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/apple-touch-icon.png') }}">

    <title>@yield('title', config('app.name'))</title>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-dvh bg-zinc-950 text-zinc-100 antialiased">
    <div class="flex min-h-dvh flex-col">
        @include('partials.header')

        <main @class([
            'mx-auto flex w-full max-w-lg flex-1 flex-col px-4 py-6 sm:px-6',
            'pb-28' => auth()->check(),
        ])>
            @yield('content')
        </main>

        @auth
            @include('partials.bottom-nav')
        @else
            @include('partials.footer')
        @endauth
    </div>
    @stack('scripts')
</body>
</html>
