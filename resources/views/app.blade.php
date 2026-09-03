<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}"
    data-chrome="{{ $chrome ?? 'navy' }}"
    data-accent="{{ $accent ?? 'brass' }}"
>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">

    <title inertia>{{ config('app.name', 'Walidia Yachts') }}</title>

    <link rel="preload" href="/fonts/dm-sans-8ca1b7f2.woff2" as="font" type="font/woff2" crossorigin>
    {{-- The Walidia emblem, cropped out of the full logo so it still reads at 32px. --}}
    <link rel="icon" href="/favicon-32.png" sizes="32x32" type="image/png">
    <link rel="icon" href="/favicon-192.png" sizes="192x192" type="image/png">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    @routes
    @viteReactRefresh
    @vite(['resources/js/app.tsx'])
    @inertiaHead
</head>
<body class="antialiased">
    @inertia
</body>
</html>
