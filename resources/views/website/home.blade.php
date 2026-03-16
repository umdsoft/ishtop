@extends('layouts.website')

@section('title', 'KadrGo — ' . __('web.hero_title'))
@section('meta_description', __('web.hero_subtitle'))

@section('json_ld')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Organization",
    "name": "KadrGo",
    "alternateName": "КадрГо",
    "url": "{{ url('/') }}",
    "logo": "{{ asset('favicon.svg') }}",
    "description": "O'zbekistondagi eng yirik ish qidirish platformasi",
    "address": {
        "@type": "PostalAddress",
        "addressCountry": "UZ"
    },
    "sameAs": [
        "https://t.me/kadrgobot"
    ]
}
</script>
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "WebSite",
    "name": "KadrGo",
    "url": "{{ url('/') }}",
    "potentialAction": {
        "@type": "SearchAction",
        "target": {
            "@type": "EntryPoint",
            "urlTemplate": "{{ url('/vacancies') }}?q={search_term_string}"
        },
        "query-input": "required name=search_term_string"
    }
}
</script>
@endsection

@section('content')

    {{-- Hero + Search --}}
    @include('website.partials.search-hero')

    {{-- Categories --}}
    @include('website.partials.category-grid')

    {{-- TOP Vacancies --}}
    @if($topVacancies->isNotEmpty())
        <section class="py-10 lg:py-14 bg-surface-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-2.5">
                        <span class="inline-flex items-center gap-1 bg-gradient-to-r from-warning-500 to-warning-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-md uppercase">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            TOP
                        </span>
                        <h2 class="text-xl md:text-2xl font-bold text-surface-900">{{ __('web.top_vacancies') }}</h2>
                    </div>
                    <a href="{{ route('vacancies.index') }}" class="text-brand-500 hover:text-brand-600 font-medium text-sm hidden sm:inline-flex items-center gap-1">
                        {{ __('web.view_all') }}
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                    @foreach($topVacancies as $vacancy)
                        @include('website.partials.vacancy-card', ['vacancy' => $vacancy])
                    @endforeach
                </div>
                <div class="text-center mt-5 sm:hidden">
                    <a href="{{ route('vacancies.index') }}" class="text-brand-500 font-medium text-sm">{{ __('web.view_all') }} &rarr;</a>
                </div>
            </div>
        </section>
    @endif

    {{-- Urgent Vacancies --}}
    @if($urgentVacancies->isNotEmpty())
        <section class="py-10 lg:py-14 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-2.5">
                        <span class="inline-flex items-center bg-danger-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-md uppercase">
                            <svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                        <h2 class="text-xl md:text-2xl font-bold text-surface-900">{{ __('web.urgent_vacancies') }}</h2>
                    </div>
                    <a href="{{ route('vacancies.index') }}" class="text-brand-500 hover:text-brand-600 font-medium text-sm hidden sm:inline-flex items-center gap-1">
                        {{ __('web.view_all') }}
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($urgentVacancies as $vacancy)
                        @include('website.partials.vacancy-card', ['vacancy' => $vacancy])
                    @endforeach
                </div>
                <div class="text-center mt-5 sm:hidden">
                    <a href="{{ route('vacancies.index') }}" class="text-brand-500 font-medium text-sm">{{ __('web.view_all') }} &rarr;</a>
                </div>
            </div>
        </section>
    @endif

    {{-- Latest Vacancies --}}
    @if($latestVacancies->isNotEmpty())
        <section class="py-10 lg:py-14 bg-surface-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl md:text-2xl font-bold text-surface-900">{{ __('web.latest_vacancies') }}</h2>
                    <a href="{{ route('vacancies.index') }}" class="text-brand-500 hover:text-brand-600 font-medium text-sm hidden sm:inline-flex items-center gap-1">
                        {{ __('web.view_all') }}
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                    @foreach($latestVacancies as $vacancy)
                        @include('website.partials.vacancy-card', ['vacancy' => $vacancy])
                    @endforeach
                </div>
                <div class="text-center mt-5 sm:hidden">
                    <a href="{{ route('vacancies.index') }}" class="text-brand-500 font-medium text-sm">{{ __('web.view_all') }} &rarr;</a>
                </div>
            </div>
        </section>
    @endif

    {{-- Regions --}}
    @if($regions->isNotEmpty())
        <section class="py-10 lg:py-14 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-xl md:text-2xl font-bold text-surface-900 mb-2">{{ __('web.vacancies_by_city') }}</h2>
                <p class="text-sm text-surface-400 mb-6">{{ __('web.cities') }}</p>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2.5">
                    @foreach($regions as $region => $count)
                        <a href="{{ route('vacancies.index', ['region' => $region]) }}"
                           class="flex items-center justify-between px-4 py-3 rounded-xl border border-surface-100 hover:border-brand-200 hover:bg-brand-50 transition-all group">
                            <span class="text-sm font-medium text-surface-700 group-hover:text-brand-600">{{ __('web.region_names.' . $region) }}</span>
                            <span class="text-[11px] text-surface-400 bg-surface-100 group-hover:bg-brand-100 group-hover:text-brand-600 px-1.5 py-0.5 rounded-full font-medium">{{ $count }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Why KadrGo --}}
    <section class="py-10 lg:py-14 bg-surface-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-xl md:text-2xl font-bold text-surface-900 text-center mb-8">{{ __('web.why_kadrgo') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div class="bg-white rounded-xl border border-surface-100 p-6 text-center">
                    <div class="w-12 h-12 bg-brand-50 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-brand-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold text-surface-900 mb-1.5">{{ __('web.why1_title') }}</h3>
                    <p class="text-sm text-surface-500 leading-relaxed">{{ __('web.why1_desc') }}</p>
                </div>
                <div class="bg-white rounded-xl border border-surface-100 p-6 text-center">
                    <div class="w-12 h-12 bg-success-50 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-success-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold text-surface-900 mb-1.5">{{ __('web.why2_title') }}</h3>
                    <p class="text-sm text-surface-500 leading-relaxed">{{ __('web.why2_desc') }}</p>
                </div>
                <div class="bg-white rounded-xl border border-surface-100 p-6 text-center">
                    <div class="w-12 h-12 bg-warning-50 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-warning-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold text-surface-900 mb-1.5">{{ __('web.why3_title') }}</h3>
                    <p class="text-sm text-surface-500 leading-relaxed">{{ __('web.why3_desc') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="py-12 lg:py-16 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-brand-500 to-brand-600"></div>
        <div class="absolute inset-0">
            <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/3"></div>
            <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/3"></div>
        </div>
        <div class="relative max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 bg-white/10 text-white/90 text-sm px-4 py-1.5 rounded-full border border-white/10 mb-5">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                Telegram
            </div>
            <h2 class="text-2xl md:text-3xl font-bold text-white mb-3">{{ __('web.cta_title') }}</h2>
            <p class="text-brand-100 text-base md:text-lg mb-8 max-w-lg mx-auto">{{ __('web.cta_subtitle') }}</p>
            <a href="https://t.me/kadrgobot" target="_blank"
               class="inline-flex items-center gap-2.5 bg-white text-brand-600 hover:bg-brand-50 px-8 py-3.5 rounded-xl font-bold text-base transition-all shadow-lg hover:shadow-xl hover:-translate-y-0.5">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                {{ __('web.cta_btn') }}
            </a>
        </div>
    </section>

@endsection
