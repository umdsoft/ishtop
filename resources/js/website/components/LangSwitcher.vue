<template>
  <div class="flex items-center gap-1">
    <button
      v-for="lang in ['uz', 'ru']"
      :key="lang"
      @click="setLocale(lang)"
      :class="[
        'px-3 py-1 rounded-lg text-sm font-medium transition-colors',
        locale === lang
          ? 'bg-brand-500 text-white'
          : 'bg-surface-100 text-surface-600 hover:bg-surface-200'
      ]"
    >
      {{ lang.toUpperCase() }}
    </button>
  </div>
</template>

<script setup>
import { useLocale } from '@website/composables/useLocale';

const { locale, setLocale } = useLocale();
</script>
