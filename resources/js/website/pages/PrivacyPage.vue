<template>
  <div class="min-h-screen bg-gradient-to-b from-surface-50 to-white">
    <!-- Hero -->
    <div class="bg-white border-b border-surface-100">
      <div class="max-w-3xl mx-auto px-4 sm:px-6 py-12 text-center">
        <span class="inline-flex items-center gap-1.5 bg-brand-50 text-brand-600 text-xs font-semibold px-3 py-1 rounded-full mb-4">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
          </svg>
          {{ $t('privacy_badge') }}
        </span>
        <h1 class="text-2xl sm:text-3xl font-bold text-surface-900 mb-3">{{ $t('privacy_title') }}</h1>
        <p class="text-surface-400 text-sm">{{ $t('privacy_updated') }}</p>
      </div>
    </div>

    <!-- Trust banner -->
    <div class="max-w-3xl mx-auto px-4 sm:px-6 pt-10">
      <div class="bg-brand-50 border border-brand-100 rounded-xl p-5 flex items-start gap-3">
        <svg class="w-5 h-5 text-brand-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <div>
          <h3 class="text-sm font-semibold text-brand-800 mb-1">{{ $t('privacy_trust_title') }}</h3>
          <p class="text-xs text-brand-600">{{ $t('privacy_trust_desc') }}</p>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-8 pb-16">
      <div class="space-y-6">
        <div
          v-for="(section, i) in sections"
          :key="i"
          class="bg-white rounded-xl border border-surface-100 p-6"
        >
          <div class="flex items-start gap-3 mb-3">
            <span class="w-7 h-7 bg-brand-50 text-brand-600 rounded-lg flex items-center justify-center text-xs font-bold shrink-0">
              {{ i + 1 }}
            </span>
            <h2 class="text-base font-semibold text-surface-900">{{ section.title }}</h2>
          </div>
          <div class="pl-10 text-sm text-surface-600 leading-relaxed whitespace-pre-line">{{ section.content }}</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useSeo } from '@website/composables/useSeo';

const { t, tm } = useI18n();
const seo = useSeo();

seo.set({
  title: t('privacy_title') + ' — KadrGo',
  description: t('privacy_trust_desc'),
});

const sections = computed(() => tm('privacy_sections'));
</script>
