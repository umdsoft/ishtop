<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'KadrGo — O\'zbekistondagi eng yirik ish qidirish platformasi' }}</title>
    <meta name="description" content="{{ $description ?? 'Minglab vakansiyalar, ishonchli kompaniyalar. Telegramdan chiqmasdan ish toping.' }}">
    <meta name="theme-color" content="#0D9488">
    <link rel="canonical" href="{{ $canonical ?? url()->current() }}">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">

    {{-- Open Graph --}}
    <meta property="og:type" content="{{ $ogType ?? 'website' }}">
    <meta property="og:title" content="{{ $ogTitle ?? $title ?? 'KadrGo' }}">
    <meta property="og:description" content="{{ $ogDescription ?? $description ?? '' }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="KadrGo">
    @if(!empty($ogImage))
        <meta property="og:image" content="{{ $ogImage }}">
    @endif

    {{-- JSON-LD --}}
    @if(!empty($jsonLd))
        <script type="application/ld+json">{!! $jsonLd !!}</script>
    @endif

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet">

    @vite(['resources/css/website.css', 'resources/js/website.js'])
</head>
<body class="antialiased bg-surface-50 text-surface-900 font-sans">
    <div id="website-app"></div>
</body>
</html>
