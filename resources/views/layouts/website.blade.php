<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', __('web.seo_home_title'))</title>
    <meta name="description" content="@yield('meta_description', __('web.seo_home_description'))">
    <meta name="theme-color" content="#0D9488">
    <link rel="canonical" href="@yield('canonical', url()->current())">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">

    {{-- Search Engine Verification --}}
    @if(config('services.google.site_verification'))
        <meta name="google-site-verification" content="{{ config('services.google.site_verification') }}">
    @endif
    @if(config('services.yandex.site_verification'))
        <meta name="yandex-verification" content="{{ config('services.yandex.site_verification') }}">
    @endif

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
        <meta property="og:image" content="{{ asset('og-image.svg') }}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta property="og:locale" content="uz_UZ">
        <meta property="og:locale:alternate" content="ru_RU">
    @endif

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', 'KadrGo')">
    <meta name="twitter:description" content="@yield('meta_description')">
    <meta name="twitter:image" content="@yield('twitter_image', asset('og-image.svg'))">

    @yield('json_ld')

    {{-- DNS Prefetch for external resources --}}
    <link rel="dns-prefetch" href="https://fonts.bunny.net">
    <link rel="dns-prefetch" href="https://mc.yandex.ru">
    <link rel="dns-prefetch" href="https://www.googletagmanager.com">
    <link rel="dns-prefetch" href="https://connect.facebook.net">
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">

    {{-- Font loading --}}
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
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

    {{-- Google Analytics 4 --}}
    @if(config('services.google.analytics_id'))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.google.analytics_id') }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ config('services.google.analytics_id') }}');
        </script>
    @endif

    {{-- Meta Pixel --}}
    @if(config('services.meta.pixel_id'))
        <script>
            !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
            n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
            document,'script','https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '{{ config('services.meta.pixel_id') }}');
            fbq('track', 'PageView');
        </script>
        <noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id={{ config('services.meta.pixel_id') }}&ev=PageView&noscript=1"/></noscript>
    @endif

    {{-- Yandex Metrika --}}
    @if(config('services.yandex.metrika_id'))
        <script type="text/javascript">
            (function(m,e,t,r,i,k,a){
                m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
                m[i].l=1*new Date();
                for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
                k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)
            })(window, document,'script','https://mc.yandex.ru/metrika/tag.js?id={{ config('services.yandex.metrika_id') }}', 'ym');
            ym({{ config('services.yandex.metrika_id') }}, 'init', {ssr:true, webvisor:true, clickmap:true, ecommerce:"dataLayer", referrer: document.referrer, url: location.href, accurateTrackBounce:true, trackLinks:true});
        </script>
        <noscript><div><img src="https://mc.yandex.ru/watch/{{ config('services.yandex.metrika_id') }}" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
    @endif
</body>
</html>
