<template>
  <div class="min-h-screen bg-gradient-to-b from-surface-50 to-white">
    <!-- Hero -->
    <div class="bg-white border-b border-surface-100">
      <div class="max-w-3xl mx-auto px-4 sm:px-6 py-12 text-center">
        <span class="inline-flex items-center gap-1.5 bg-brand-50 text-brand-600 text-xs font-semibold px-3 py-1 rounded-full mb-4">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          {{ $t('faq_badge') }}
        </span>
        <h1 class="text-2xl sm:text-3xl font-bold text-surface-900 mb-3">{{ $t('faq_title') }}</h1>
        <p class="text-surface-500 text-sm sm:text-base max-w-xl mx-auto">{{ $t('faq_subtitle') }}</p>
      </div>
    </div>

    <!-- FAQ Accordion -->
    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-10">
      <div class="space-y-3">
        <div
          v-for="(item, i) in faqItems"
          :key="i"
          class="bg-white rounded-xl border border-surface-100 overflow-hidden transition-shadow"
          :class="{ 'shadow-md border-brand-200': openIndex === i }"
        >
          <button
            @click="toggle(i)"
            class="w-full flex items-center justify-between gap-3 px-5 py-4 text-left"
          >
            <div class="flex items-center gap-3 min-w-0">
              <span class="w-7 h-7 bg-brand-50 text-brand-600 rounded-lg flex items-center justify-center text-xs font-bold shrink-0">
                {{ i + 1 }}
              </span>
              <span class="text-sm font-medium text-surface-800">{{ item.q }}</span>
            </div>
            <svg
              class="w-5 h-5 text-surface-400 shrink-0 transition-transform duration-200"
              :class="{ 'rotate-180': openIndex === i }"
              fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>
          <div
            v-show="openIndex === i"
            class="px-5 pb-4"
          >
            <div class="pl-10 text-sm text-surface-600 leading-relaxed">{{ item.a }}</div>
          </div>
        </div>
      </div>

      <!-- CTA -->
      <div class="mt-12 bg-gradient-to-r from-brand-500 to-brand-600 rounded-2xl p-8 text-center text-white">
        <h3 class="text-lg font-bold mb-2">{{ $t('faq_cta_title') }}</h3>
        <p class="text-brand-100 text-sm mb-5">{{ $t('faq_cta_subtitle') }}</p>
        <a
          href="https://t.me/kadrgo_support"
          target="_blank"
          class="inline-flex items-center gap-2 bg-white text-brand-600 font-semibold text-sm px-6 py-2.5 rounded-lg hover:bg-brand-50 transition-colors"
        >
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
          {{ $t('faq_cta_btn') }}
        </a>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useSeo } from '@website/composables/useSeo';

const { t, tm } = useI18n();
const seo = useSeo();

seo.set({
  title: t('faq_title') + ' — KadrGo',
  description: t('faq_subtitle'),
});

const faqItems = computed(() => tm('faq'));
const openIndex = ref(0);

function toggle(i) {
  openIndex.value = openIndex.value === i ? -1 : i;
}
</script>
