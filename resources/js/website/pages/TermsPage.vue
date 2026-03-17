<template>
  <div class="min-h-screen bg-gradient-to-b from-surface-50 to-white">
    <!-- Hero -->
    <div class="bg-white border-b border-surface-100">
      <div class="max-w-3xl mx-auto px-4 sm:px-6 py-12 text-center">
        <span class="inline-flex items-center gap-1.5 bg-brand-50 text-brand-600 text-xs font-semibold px-3 py-1 rounded-full mb-4">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          {{ $t('terms_badge') }}
        </span>
        <h1 class="text-2xl sm:text-3xl font-bold text-surface-900 mb-3">{{ $t('terms_title') }}</h1>
        <p class="text-surface-400 text-sm">{{ $t('terms_updated') }}</p>
      </div>
    </div>

    <!-- Content -->
    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-10">
      <div class="space-y-6">
        <div
          v-for="(section, i) in sections"
          :key="i"
          class="bg-white rounded-xl border p-6"
          :class="section.warning ? 'border-warning-300 bg-warning-50/30' : 'border-surface-100'"
        >
          <div class="flex items-start gap-3 mb-3">
            <span
              class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold shrink-0"
              :class="section.warning ? 'bg-warning-100 text-warning-700' : 'bg-brand-50 text-brand-600'"
            >
              {{ i + 1 }}
            </span>
            <h2
              class="text-base font-semibold"
              :class="section.warning ? 'text-warning-800' : 'text-surface-900'"
            >
              {{ section.title }}
            </h2>
          </div>
          <div class="pl-10 text-sm text-surface-600 leading-relaxed whitespace-pre-line">{{ section.content }}</div>
          <div v-if="section.warning" class="mt-4 ml-10 flex items-start gap-2 bg-warning-100 rounded-lg p-3">
            <svg class="w-4 h-4 text-warning-600 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 6a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 6zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
            </svg>
            <p class="text-xs text-warning-700 font-medium">{{ section.warning_text || $t('terms_warning_note') }}</p>
          </div>
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
  title: t('terms_title') + ' — KadrGo',
  description: t('terms_title') + ' — KadrGo platformasining foydalanish shartlari',
});

const sections = computed(() => tm('terms_sections'));
</script>
