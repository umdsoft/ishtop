<template>
  <div v-if="activeFilters.length" class="hidden sm:flex items-center gap-2 flex-wrap">
    <span
      v-for="filter in activeFilters"
      :key="filter.key"
      class="inline-flex items-center gap-1 bg-brand-50 text-brand-600 text-xs px-2.5 py-1 rounded-lg hover:bg-brand-100 transition-colors cursor-pointer"
      @click="$emit('remove', filter.key)"
    >
      <svg v-if="filter.icon === 'search'" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
      </svg>
      <svg v-if="filter.icon === 'location'" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
      </svg>
      {{ filter.label }}
      <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
      </svg>
    </span>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps({
  filters: { type: Object, required: true },
  selectedSubs: { type: Array, default: () => [] },
  selectedCities: { type: Array, default: () => [] },
  categories: { type: Array, default: () => [] },
});

defineEmits(['remove', 'reset']);

const { t, tm } = useI18n();

function regionName(region) {
  const names = tm('region_names');
  return names?.[region] || region;
}

function getCategoryName(slug) {
  if (!props.categories) return slug;
  const cat = props.categories.find(c => c.slug === slug);
  return cat?.name || slug;
}

const activeFilters = computed(() => {
  const chips = [];

  if (props.filters.q) {
    chips.push({ key: 'q', label: `"${props.filters.q}"`, icon: 'search' });
  }

  if (props.filters.category) {
    chips.push({ key: 'category', label: getCategoryName(props.filters.category) });
  }

  if (props.selectedCities.length > 0) {
    // Show unique city names (strip ::type suffix)
    const names = props.selectedCities.map(k => k.split('::')[0]);
    const unique = [...new Set(names)];
    chips.push({ key: 'city', label: unique.join(', '), icon: 'location' });
  } else if (props.filters.region) {
    chips.push({ key: 'region', label: regionName(props.filters.region), icon: 'location' });
  }

  if (props.filters.work_type) {
    chips.push({ key: 'work_type', label: t(props.filters.work_type) });
  }

  return chips;
});
</script>
