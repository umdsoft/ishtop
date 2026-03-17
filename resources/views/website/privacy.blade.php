@extends('layouts.website')

@section('title', __('web.seo_privacy_title'))
@section('meta_description', __('web.seo_privacy_description'))

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
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
            </svg>
            {{ app()->getLocale() === 'ru' ? 'Защита данных' : 'Ma\'lumotlar himoyasi' }}
        </div>
        <h1 class="text-3xl sm:text-4xl font-bold text-white mb-3">{{ __('web.privacy_title') }}</h1>
        <p class="text-brand-200 text-sm">{{ __('web.privacy_updated') }}</p>
    </div>
</section>

{{-- Breadcrumb --}}
<div class="bg-white border-b border-surface-100">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
        <nav class="flex items-center gap-2 text-sm text-surface-400">
            <a href="{{ route('home') }}" class="hover:text-brand-500 transition-colors">{{ __('web.home') }}</a>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            <span class="text-surface-700 font-medium">{{ __('web.privacy_title') }}</span>
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
                        @foreach(__('web.privacy_sections') as $i => $section)
                        <a href="#privacy-{{ $i }}"
                           class="block px-3 py-2 text-sm text-surface-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition-colors truncate">
                            {{ $section['title'] }}
                        </a>
                        @endforeach
                    </nav>
                </div>
            </aside>

            {{-- Main content --}}
            <div class="flex-1 min-w-0">
                {{-- Trust banner --}}
                <div class="bg-success-50 border border-success-200 rounded-2xl p-5 sm:p-6 mb-6 flex items-start gap-4">
                    <span class="flex-shrink-0 w-10 h-10 bg-success-100 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-success-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                        </svg>
                    </span>
                    <div>
                        <p class="font-semibold text-success-800 text-sm mb-1">
                            {{ app()->getLocale() === 'ru' ? 'Мы заботимся о вашей безопасности' : 'Biz sizning xavfsizligingiz haqida qayg\'uramiz' }}
                        </p>
                        <p class="text-success-700 text-sm leading-relaxed">
                            {{ app()->getLocale() === 'ru' ? 'Ваши данные защищены HTTPS шифрованием и не передаются третьим лицам без вашего согласия.' : 'Ma\'lumotlaringiz HTTPS shifrlash bilan himoyalangan va sizning roziligingiz siz uchinchi tomonlarga berilmaydi.' }}
                        </p>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-surface-100 shadow-sm overflow-hidden">
                    @foreach(__('web.privacy_sections') as $i => $section)
                    <div id="privacy-{{ $i }}" class="{{ $i > 0 ? 'border-t border-surface-100' : '' }} px-5 sm:px-8 py-6 sm:py-8 scroll-mt-24">
                        <h2 class="text-lg font-bold text-surface-900 mb-4 flex items-center gap-3">
                            <span class="flex-shrink-0 w-8 h-8 bg-brand-50 rounded-lg flex items-center justify-center text-sm font-bold text-brand-600">
                                {{ $i + 1 }}
                            </span>
                            {{ Str::after($section['title'], '. ') }}
                        </h2>
                        <div class="text-surface-600 leading-relaxed text-[15px] prose-content ml-11">{!! $section['content'] !!}</div>
                    </div>
                    @endforeach
                </div>

                {{-- Bottom nav --}}
                <div class="mt-8 flex items-center justify-between">
                    <a href="{{ route('terms') }}" class="inline-flex items-center gap-2 text-surface-500 hover:text-brand-600 font-medium transition-colors text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                        {{ __('web.terms_title') }}
                    </a>
                    <a href="{{ route('faq') }}" class="inline-flex items-center gap-2 text-brand-500 hover:text-brand-600 font-medium transition-colors text-sm">
                        {{ __('web.faq_title') }}
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
