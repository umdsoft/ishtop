<template>
  <section class="py-10 lg:py-14 bg-surface-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <h2 class="text-xl md:text-2xl font-bold text-surface-900 text-center mb-8">{{ $t('why_kadrgo') }}</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="bg-white rounded-xl border border-surface-100 p-6 text-center">
          <div class="w-12 h-12 bg-brand-50 rounded-xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-6 h-6 text-brand-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
          </div>
          <h3 class="text-base font-semibold text-surface-900 mb-1.5">{{ $t('why1_title') }}</h3>
          <p class="text-sm text-surface-500 leading-relaxed">{{ $t('why1_desc') }}</p>
        </div>
        <div class="bg-white rounded-xl border border-surface-100 p-6 text-center">
          <div class="w-12 h-12 bg-success-50 rounded-xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-6 h-6 text-success-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
          </div>
          <h3 class="text-base font-semibold text-surface-900 mb-1.5">{{ $t('why2_title') }}</h3>
          <p class="text-sm text-surface-500 leading-relaxed">{{ $t('why2_desc') }}</p>
        </div>
        <div class="bg-white rounded-xl border border-surface-100 p-6 text-center">
          <div class="w-12 h-12 bg-warning-50 rounded-xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-6 h-6 text-warning-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <h3 class="text-base font-semibold text-surface-900 mb-1.5">{{ $t('why3_title') }}</h3>
          <p class="text-sm text-surface-500 leading-relaxed">{{ $t('why3_desc') }}</p>
        </div>
      </div>
    </div>
  </section>
</template>
