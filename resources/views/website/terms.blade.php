@extends('layouts.website')

@section('title', __('web.seo_terms_title'))
@section('meta_description', __('web.seo_terms_description'))

@section('content')

{{-- Hero --}}
<section class="relative bg-gradient-to-br from-brand-700 to-surface-900 overflow-hidden">
    <div class="absolute inset-0">
        <div class="absolute top-0 right-0 w-[400px] h-[400px] bg-white/5 rounded-full -translate-y-1/2 translate-x-1/3"></div>
        <div class="absolute bottom-0 left-0 w-[300px] h-[300px] bg-white/5 rounded-full translate-y-1/2 -translate-x-1/3"></div>
    </div>
    <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20 text-center">
        <div class="inline-flex items-center gap-2 bg-white/10 text-white/90 text-sm px-4 py-1.5 rounded-full border border-white/10 mb-5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/>
            </svg>
            {{ app()->getLocale() === 'ru' ? 'Документ' : 'Hujjat' }}
        </div>
        <h1 class="text-3xl sm:text-4xl font-bold text-white mb-3">{{ __('web.terms_title') }}</h1>
        <p class="text-brand-200 text-sm">{{ __('web.terms_updated') }}</p>
    </div>
</section>

{{-- Breadcrumb --}}
<div class="bg-white border-b border-surface-100">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
        <nav class="flex items-center gap-2 text-sm text-surface-400">
            <a href="{{ route('home') }}" class="hover:text-brand-500 transition-colors">{{ __('web.home') }}</a>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            <span class="text-surface-700 font-medium">{{ __('web.terms_title') }}</span>
        </nav>
    </div>
</div>

{{-- Content --}}
<section class="py-12 sm:py-16 bg-surface-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row gap-8">
            {{-- Sidebar nav (desktop) --}}
            <aside class="hidden lg:block lg:w-56 flex-shrink-0">
                <div class="sticky top-24">
                    <nav class="space-y-1">
                        @foreach(__('web.terms_sections') as $i => $section)
                        <a href="#section-{{ $i }}"
                           class="block px-3 py-2 text-sm text-surface-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition-colors truncate">
                            {{ $section['title'] }}
                        </a>
                        @endforeach
                    </nav>
                </div>
            </aside>

            {{-- Main content --}}
            <div class="flex-1 min-w-0">
                <div class="bg-white rounded-2xl border border-surface-100 shadow-sm overflow-hidden">
                    @foreach(__('web.terms_sections') as $i => $section)
                    <div id="section-{{ $i }}" class="{{ $i > 0 ? 'border-t border-surface-100' : '' }} px-5 sm:px-8 py-6 sm:py-8 scroll-mt-24">
                        <h2 class="text-lg font-bold text-surface-900 mb-4 flex items-start gap-3">
                            @if($i === 2)
                            <span class="flex-shrink-0 mt-0.5 w-6 h-6 bg-danger-50 rounded-md flex items-center justify-center">
                                <svg class="w-3.5 h-3.5 text-danger-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                                </svg>
                            </span>
                            @endif
                            {{ $section['title'] }}
                        </h2>
                        @if($i === 2)
                        <div class="bg-danger-50 border border-danger-100 rounded-xl p-4 sm:p-5 mb-4">
                            <div class="text-surface-700 leading-relaxed text-[15px] prose-content">{!! $section['content'] !!}</div>
                        </div>
                        @else
                        <div class="text-surface-600 leading-relaxed text-[15px] prose-content">{!! $section['content'] !!}</div>
                        @endif
                    </div>
                    @endforeach
                </div>

                {{-- Bottom nav --}}
                <div class="mt-8 flex items-center justify-between">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-surface-500 hover:text-brand-600 font-medium transition-colors text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                        {{ __('web.home') }}
                    </a>
                    <a href="{{ route('privacy') }}" class="inline-flex items-center gap-2 text-brand-500 hover:text-brand-600 font-medium transition-colors text-sm">
                        {{ __('web.privacy_title') }}
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
