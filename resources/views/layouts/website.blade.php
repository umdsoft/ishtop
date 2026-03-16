<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'KadrGo — ' . __('web.hero_title'))</title>
    <meta name="description" content="@yield('meta_description', __('web.hero_subtitle'))">
    <meta name="theme-color" content="#0D9488">
    <link rel="canonical" href="@yield('canonical', url()->current())">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">

    {{-- Hreflang --}}
    <link rel="alternate" hreflang="uz" href="{{ url()->current() }}?lang=uz">
    <link rel="alternate" hreflang="ru" href="{{ url()->current() }}?lang=ru">
    <link rel="alternate" hreflang="x-default" href="{{ url()->current() }}">

    {{-- Open Graph (default, overridable per page) --}}
    @hasSection('og_meta')
        @yield('og_meta')
    @else
        <meta property="og:type" content="website">
        <meta property="og:title" content="@yield('title', 'KadrGo')">
        <meta property="og:description" content="@yield('meta_description')">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:site_name" content="KadrGo">
    @endif

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', 'KadrGo')">
    <meta name="twitter:description" content="@yield('meta_description')">

    @yield('json_ld')

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet">

    @vite(['resources/css/website.css'])

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
</head>
<body class="antialiased bg-surface-50 text-surface-900 font-sans">

    @include('website.partials.navbar')

    <main>
        @yield('content')
    </main>

    @include('website.partials.footer')

</body>
</html>
