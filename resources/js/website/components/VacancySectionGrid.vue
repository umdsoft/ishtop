<template>
  <section class="py-10 lg:py-14" :class="sectionBg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-2.5">
          <!-- TOP badge -->
          <span v-if="badge === 'top'" class="inline-flex items-center gap-1 bg-gradient-to-r from-warning-500 to-warning-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-md uppercase">
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            TOP
          </span>
          <!-- Urgent badge -->
          <span v-if="badge === 'urgent'" class="inline-flex items-center bg-danger-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-md uppercase">
            <svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </span>
          <h2 class="text-xl md:text-2xl font-bold text-surface-900">{{ title }}</h2>
        </div>
        <router-link v-if="showViewAll" to="/vacancies" class="text-brand-500 hover:text-brand-600 font-medium text-sm hidden sm:inline-flex items-center gap-1">
          {{ $t('view_all') }}
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </router-link>
      </div>

      <!-- Loading -->
      <div v-if="loading" :class="gridClass">
        <div v-for="i in 4" :key="i" class="h-40 bg-surface-100 rounded-xl animate-pulse"></div>
      </div>

      <!-- Empty -->
      <div v-else-if="!vacancies?.length" class="text-center py-8 text-surface-400 text-sm">
        {{ $t('no_results') }}
      </div>

      <!-- Cards -->
      <div v-else :class="gridClass">
        <VacancyCard v-for="v in vacancies" :key="v.id" :vacancy="v" />
      </div>

      <!-- Mobile view all -->
      <div v-if="showViewAll && !loading" class="text-center mt-5 sm:hidden">
        <router-link to="/vacancies" class="text-brand-500 font-medium text-sm">{{ $t('view_all') }} &rarr;</router-link>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue';
import VacancyCard from '@website/components/VacancyCard.vue';

const props = defineProps({
  title: { type: String, required: true },
  vacancies: { type: Array, default: () => [] },
  loading: { type: Boolean, default: true },
  badge: { type: String, default: '' },
  showViewAll: { type: Boolean, default: false },
});

const sectionBg = computed(() => {
  if (props.badge === 'urgent') return 'bg-white';
  return 'bg-surface-50';
});

const gridClass = computed(() => {
  if (props.badge === 'urgent') {
    return 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3';
  }
  return 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3';
});
</script>
