<template>
  <section class="py-10 lg:py-14 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <h2 class="text-xl md:text-2xl font-bold text-surface-900 mb-2">{{ $t('vacancies_by_city') }}</h2>
      <p class="text-sm text-surface-400 mb-6">{{ $t('cities') }}</p>

      <!-- Loading -->
      <div v-if="loading" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2.5">
        <div v-for="i in 10" :key="i" class="h-12 bg-surface-100 rounded-xl animate-pulse"></div>
      </div>

      <!-- Regions -->
      <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2.5">
        <router-link
          v-for="(count, region) in regions"
          :key="region"
          :to="{ path: '/vacancies', query: { region } }"
          class="flex items-center justify-between px-4 py-3 rounded-xl border border-surface-100 hover:border-brand-200 hover:bg-brand-50 transition-all group"
        >
          <span class="text-sm font-medium text-surface-700 group-hover:text-brand-600">
            {{ regionName(region) }}
          </span>
          <span class="text-[11px] text-surface-400 bg-surface-100 group-hover:bg-brand-100 group-hover:text-brand-600 px-1.5 py-0.5 rounded-full font-medium">
            {{ count }}
          </span>
        </router-link>
      </div>
    </div>
  </section>
</template>

<script setup>
import { useI18n } from 'vue-i18n';

defineProps({
  regions: { type: Object, default: () => ({}) },
  loading: { type: Boolean, default: true },
});

const { tm } = useI18n();

function regionName(region) {
  const names = tm('region_names');
  return names?.[region] || region;
}
</script>
