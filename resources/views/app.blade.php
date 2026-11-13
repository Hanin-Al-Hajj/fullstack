<!DOCTYPE html>
@php($rtl = app()->getLocale() === 'ar')
<html lang="{{ app()->getLocale() }}" dir="{{ $rtl ? 'rtl' : 'ltr' }}"{!! $rtl ? ' view-mode="rtl"' : '' !!}>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover, shrink-to-fit=no">
    <meta name="theme-color" content="#625AFA">
        @include('partials.seo')

    <link rel="preconnect" href="https://fonts.googleapis.com/">
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">

    @if ($rtl)
        <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
        <style>
            [dir=rtl] body, [dir=rtl] input, [dir=rtl] button, [dir=rtl] select, [dir=rtl] textarea { font-family: 'Tajawal', sans-serif; }
        </style>
    @endif

    <link rel="icon" type="image/png" href="/icons/favicon.png">
    <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
    <link rel="manifest" href="/manifest.webmanifest">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="{{ config('store.name') }}">

    {{-- Template CSS only. Tailwind (resources/css/app.css) is intentionally NOT loaded here. --}}
    <link rel="stylesheet" href="{{ asset('template/css/bootstrap'.($rtl ? '.rtl' : '').'.min.css') }}">
    <link rel="stylesheet" href="{{ asset('template/css/tabler-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('template/style.css') }}">

    @viteReactRefresh
    @vite('resources/js/app.jsx')
    @inertiaHead
</head>
<body>
    @inertia
</body>
</html>