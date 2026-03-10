@extends('layouts.website')

@section('title', $vacancy->title($lang) . ' — KadrGo')
@section('meta_description', Str::limit(strip_tags($vacancy->description($lang)), 160))
@section('canonical', route('vacancies.show', $vacancy))

@section('og_meta')
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $vacancy->title($lang) }}">
    <meta property="og:description" content="{{ Str::limit(strip_tags($vacancy->description($lang)), 200) }}">
    <meta property="og:url" content="{{ route('vacancies.show', $vacancy) }}">
    <meta property="og:site_name" content="KadrGo">
@endsection

@section('json_ld')
    @include('website.partials.json-ld', ['vacancy' => $vacancy])
@endsection

@section('content')

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-sm text-surface-400 mb-6">
            <a href="{{ route('home') }}" class="hover:text-brand-500">{{ __('web.home') }}</a>
            <span>/</span>
            <a href="{{ route('vacancies.index') }}" class="hover:text-brand-500">{{ __('web.vacancies') }}</a>
            <span>/</span>
            <span class="text-surface-600 truncate max-w-xs">{{ $vacancy->title($lang) }}</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Main content --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Header --}}
                <div class="bg-white rounded-2xl shadow-soft p-6 border border-surface-100">
                    {{-- Badges --}}
                    <div class="flex items-center gap-2 mb-4">
                        @if($vacancy->isTopActive())
                            <span class="bg-warning-100 text-warning-700 text-xs font-bold px-2.5 py-1 rounded-lg">TOP</span>
                        @endif
                        @if($vacancy->is_urgent && $vacancy->urgent_until?->isFuture())
                            <span class="bg-danger-100 text-danger-700 text-xs font-bold px-2.5 py-1 rounded-lg">{{ __('web.urgent') }}</span>
                        @endif
                    </div>

                    <h1 class="text-2xl md:text-3xl font-bold text-surface-900 mb-4">{{ $vacancy->title($lang) }}</h1>

                    {{-- Salary --}}
                    <div class="text-xl font-bold text-brand-600 mb-4">
                        @if($vacancy->salary_type === 'negotiable')
                            {{ __('web.negotiable') }}
                        @elseif($vacancy->salary_min && $vacancy->salary_max)
                            {{ number_format($vacancy->salary_min) }} - {{ number_format($vacancy->salary_max) }} {{ __('web.som') }}
                        @elseif($vacancy->salary_min)
                            {{ __('web.salary_from') }} {{ number_format($vacancy->salary_min) }} {{ __('web.som') }}
                        @elseif($vacancy->salary_max)
                            {{ __('web.salary_to') }} {{ number_format($vacancy->salary_max) }} {{ __('web.som') }}
                        @else
                            {{ __('web.negotiable') }}
                        @endif
                    </div>

                    {{-- Meta tags --}}
                    <div class="flex flex-wrap items-center gap-3 text-sm">
                        @if($vacancy->city)
                            <span class="flex items-center gap-1.5 bg-surface-50 px-3 py-1.5 rounded-lg text-surface-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                {{ $vacancy->city }}{{ $vacancy->district ? ', ' . $vacancy->district : '' }}
                            </span>
                        @endif

                        @if($vacancy->work_type)
                            <span class="bg-surface-50 px-3 py-1.5 rounded-lg text-surface-600">
                                {{ __('web.' . $vacancy->work_type->value) }}
                            </span>
                        @endif

                        @if($vacancy->experience_required)
                            <span class="bg-surface-50 px-3 py-1.5 rounded-lg text-surface-600">
                                {{ __('web.experience') }}: {{ $vacancy->experience_required }}
                            </span>
                        @else
                            <span class="bg-success-50 text-success-700 px-3 py-1.5 rounded-lg">
                                {{ __('web.no_experience') }}
                            </span>
                        @endif

                        @if($vacancy->published_at)
                            <span class="text-surface-400">
                                {{ __('web.published') }}: {{ $vacancy->published_at->diffForHumans() }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Description --}}
                @if($vacancy->description($lang))
                    <div class="bg-white rounded-2xl shadow-soft p-6 border border-surface-100">
                        <h2 class="text-lg font-semibold text-surface-900 mb-4">{{ __('web.description') }}</h2>
                        <div class="prose prose-surface max-w-none text-surface-600 text-sm leading-relaxed">
                            {!! nl2br(e($vacancy->description($lang))) !!}
                        </div>
                    </div>
                @endif

                {{-- Requirements --}}
                @if($vacancy->requirements($lang))
                    <div class="bg-white rounded-2xl shadow-soft p-6 border border-surface-100">
                        <h2 class="text-lg font-semibold text-surface-900 mb-4">{{ __('web.requirements') }}</h2>
                        <div class="prose prose-surface max-w-none text-surface-600 text-sm leading-relaxed">
                            {!! nl2br(e($vacancy->requirements($lang))) !!}
                        </div>
                    </div>
                @endif

                {{-- Responsibilities --}}
                @if($vacancy->responsibilities($lang))
                    <div class="bg-white rounded-2xl shadow-soft p-6 border border-surface-100">
                        <h2 class="text-lg font-semibold text-surface-900 mb-4">{{ __('web.responsibilities') }}</h2>
                        <div class="prose prose-surface max-w-none text-surface-600 text-sm leading-relaxed">
                            {!! nl2br(e($vacancy->responsibilities($lang))) !!}
                        </div>
                    </div>
                @endif

                {{-- Map --}}
                @if($vacancy->latitude && $vacancy->longitude)
                    <div class="bg-white rounded-2xl shadow-soft p-6 border border-surface-100">
                        <h2 class="text-lg font-semibold text-surface-900 mb-4">{{ __('web.location_on_map') }}</h2>
                        <div id="vacancy-map" class="w-full h-64 sm:h-80 rounded-xl overflow-hidden z-0"></div>
                    </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Company card --}}
                <div class="bg-white rounded-2xl shadow-soft p-6 border border-surface-100">
                    <h3 class="text-lg font-semibold text-surface-900 mb-4">{{ __('web.about_company') }}</h3>
                    <div class="flex items-center gap-3 mb-4">
                        @if($vacancy->employer?->logo_url)
                            <img src="{{ $vacancy->employer->logo_url }}" alt="{{ $vacancy->company_name }}" class="w-12 h-12 rounded-xl object-cover">
                        @else
                            <div class="w-12 h-12 rounded-xl bg-brand-100 text-brand-600 flex items-center justify-center text-lg font-bold">
                                {{ mb_substr($vacancy->company_name ?? 'C', 0, 1) }}
                            </div>
                        @endif
                        <div>
                            <div class="flex items-center gap-1.5">
                                <span class="font-semibold text-surface-900">{{ $vacancy->company_name }}</span>
                                @if($vacancy->employer?->verification_level === 'verified')
                                    <svg class="w-4 h-4 text-brand-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                            </div>
                            @if($vacancy->employer?->rating)
                                <div class="flex items-center gap-1 mt-1">
                                    <svg class="w-4 h-4 text-warning-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    <span class="text-sm font-medium text-surface-700">{{ number_format($vacancy->employer->rating, 1) }}</span>
                                    @if($vacancy->employer->rating_count)
                                        <span class="text-xs text-surface-400">({{ $vacancy->employer->rating_count }})</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($vacancy->employer?->description)
                        <p class="text-sm text-surface-500 leading-relaxed">
                            {{ Str::limit($vacancy->employer->description, 200) }}
                        </p>
                    @endif
                </div>

                {{-- Contact --}}
                @if($vacancy->contact_phone)
                    <div class="bg-white rounded-2xl shadow-soft p-6 border border-surface-100">
                        <h3 class="text-sm font-semibold text-surface-900 mb-3">{{ __('web.contact_phone') }}</h3>
                        <a href="tel:{{ $vacancy->contact_phone }}" class="text-brand-500 hover:text-brand-600 font-medium">
                            {{ $vacancy->contact_phone }}
                        </a>
                    </div>
                @endif

                {{-- Stats --}}
                <div class="bg-white rounded-2xl shadow-soft p-6 border border-surface-100">
                    <div class="grid grid-cols-2 gap-4 text-center">
                        <div>
                            <div class="text-lg font-bold text-surface-900">{{ number_format($vacancy->views_count) }}</div>
                            <div class="text-xs text-surface-400">{{ __('web.views') }}</div>
                        </div>
                        <div>
                            <div class="text-lg font-bold text-surface-900">{{ number_format($vacancy->applications_count) }}</div>
                            <div class="text-xs text-surface-400">{{ __('web.applicants') }}</div>
                        </div>
                    </div>
                </div>

                {{-- Apply form in sidebar --}}
                <div class="bg-white rounded-2xl shadow-soft p-6 border border-surface-100" id="apply">
                    @include('website.partials.apply-form', ['vacancy' => $vacancy])
                </div>
            </div>
        </div>

        {{-- Similar vacancies --}}
        @if($similarVacancies->isNotEmpty())
            <section class="mt-12">
                <h2 class="text-2xl font-bold text-surface-900 mb-6">{{ __('web.similar_vacancies') }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($similarVacancies as $similar)
                        @include('website.partials.vacancy-card', ['vacancy' => $similar])
                    @endforeach
                </div>
            </section>
        @endif
    </div>

    {{-- Leaflet map --}}
    @if($vacancy->latitude && $vacancy->longitude)
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var map = L.map('vacancy-map').setView([{{ $vacancy->latitude }}, {{ $vacancy->longitude }}], 15);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap'
                }).addTo(map);
                L.marker([{{ $vacancy->latitude }}, {{ $vacancy->longitude }}])
                    .addTo(map)
                    .bindPopup(@json($vacancy->title($lang) . '<br>' . $vacancy->city . ($vacancy->district ? ', ' . $vacancy->district : '')))
                    .openPopup();
            });
        </script>
    @endif

@endsection
