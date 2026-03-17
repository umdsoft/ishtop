@extends('layouts.website')

@section('title', __('web.seo_faq_title'))
@section('meta_description', __('web.seo_faq_description'))

@section('json_ld')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => collect(__('web.faq'))->map(fn($item) => [
        '@type' => 'Question',
        'name' => $item['q'],
        'acceptedAnswer' => [
            '@type' => 'Answer',
            'text' => $item['a'],
        ],
    ])->toArray(),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endsection

@section('content')

{{-- Hero --}}
<section class="relative bg-gradient-to-br from-brand-700 to-surface-900 overflow-hidden">
    <div class="absolute inset-0">
        <div class="absolute top-0 right-0 w-[400px] h-[400px] bg-white/5 rounded-full -translate-y-1/2 translate-x-1/3"></div>
        <div class="absolute bottom-0 left-0 w-[300px] h-[300px] bg-white/5 rounded-full translate-y-1/2 -translate-x-1/3"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-brand-400/10 rounded-full blur-3xl"></div>
    </div>
    <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20 text-center">
        <div class="inline-flex items-center gap-2 bg-white/10 text-white/90 text-sm px-4 py-1.5 rounded-full border border-white/10 mb-5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z"/>
            </svg>
            FAQ
        </div>
        <h1 class="text-3xl sm:text-4xl font-bold text-white mb-3">{{ __('web.faq_title') }}</h1>
        <p class="text-brand-100 text-lg max-w-2xl mx-auto">{{ __('web.faq_subtitle') }}</p>
    </div>
</section>

{{-- Breadcrumb --}}
<div class="bg-white border-b border-surface-100">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
        <nav class="flex items-center gap-2 text-sm text-surface-400">
            <a href="{{ route('home') }}" class="hover:text-brand-500 transition-colors">{{ __('web.home') }}</a>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            <span class="text-surface-700 font-medium">{{ __('web.faq_title') }}</span>
        </nav>
    </div>
</div>

{{-- FAQ Accordion --}}
<section class="py-12 sm:py-16 bg-surface-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="space-y-3" x-data="{ open: 0 }">
            @foreach(__('web.faq') as $i => $item)
            <div class="bg-white rounded-2xl border border-surface-100 overflow-hidden transition-all duration-200"
                 :class="open === {{ $i }} ? 'shadow-lg border-brand-200 ring-1 ring-brand-100' : 'hover:shadow-sm'">
                <button @click="open = open === {{ $i }} ? null : {{ $i }}"
                        class="w-full flex items-center justify-between px-5 sm:px-6 py-4 sm:py-5 text-left gap-4 group">
                    <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                        <span class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold transition-colors"
                              :class="open === {{ $i }} ? 'bg-brand-500 text-white' : 'bg-surface-100 text-surface-500 group-hover:bg-brand-50 group-hover:text-brand-600'">
                            {{ $i + 1 }}
                        </span>
                        <span class="font-semibold text-surface-900 text-[15px] sm:text-base">{{ $item['q'] }}</span>
                    </div>
                    <svg class="w-5 h-5 text-surface-400 flex-shrink-0 transition-transform duration-200"
                         :class="open === {{ $i }} ? 'rotate-180 text-brand-500' : ''"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open === {{ $i }}"
                     x-collapse
                     x-cloak>
                    <div class="px-5 sm:px-6 pb-5 sm:pb-6 ml-11 sm:ml-12">
                        <div class="text-surface-600 leading-relaxed text-[15px] border-l-2 border-brand-200 pl-4">
                            {{ $item['a'] }}
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
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
        <h2 class="text-2xl md:text-3xl font-bold text-white mb-3">
            {{ app()->getLocale() === 'ru' ? 'Не нашли ответ?' : 'Javob topmadingizmi?' }}
        </h2>
        <p class="text-brand-100 text-base md:text-lg mb-8 max-w-lg mx-auto">
            {{ app()->getLocale() === 'ru' ? 'Наша команда поддержки всегда готова помочь вам' : 'Bizning qo\'llab-quvvatlash jamoamiz doimo yordam berishga tayyor' }}
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="https://t.me/kadrgo_support" target="_blank"
               class="inline-flex items-center gap-2.5 bg-white text-brand-600 hover:bg-brand-50 px-8 py-3.5 rounded-xl font-bold text-base transition-all shadow-lg hover:shadow-xl hover:-translate-y-0.5">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                {{ app()->getLocale() === 'ru' ? 'Написать в поддержку' : 'Qo\'llab-quvvatlash xizmatiga yozish' }}
            </a>
            <a href="{{ route('home') }}"
               class="inline-flex items-center gap-2 text-white/80 hover:text-white font-medium px-6 py-3.5 rounded-xl transition-colors border border-white/20 hover:border-white/40">
                &larr; {{ __('web.home') }}
            </a>
        </div>
    </div>
</section>

@endsection
