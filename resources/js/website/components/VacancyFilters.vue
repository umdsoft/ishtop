<template>
  <div class="space-y-4">

    <!-- ══ Search box ══ -->
    <div class="bg-white rounded-2xl border border-surface-100 shadow-sm p-4">
      <div class="relative mb-3">
        <svg class="w-4 h-4 text-surface-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input
          v-model="filters.q"
          type="text"
          :placeholder="$t('search_placeholder')"
          class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-surface-200 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm bg-surface-50"
          @keyup.enter="emitSearch"
        >
      </div>
      <button
        @click="emitSearch"
        class="w-full bg-brand-500 hover:bg-brand-600 text-white py-2.5 rounded-xl font-semibold text-sm transition-colors flex items-center justify-center gap-2"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        {{ $t('search') }}
      </button>
    </div>

    <!-- ══ Categories with subcategory checkboxes ══ -->
    <div v-if="categories?.length" class="bg-white rounded-2xl border border-surface-100 shadow-sm overflow-hidden">
      <div class="px-4 py-3 border-b border-surface-100 flex items-center gap-2">
        <svg class="w-4 h-4 text-brand-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
        </svg>
        <span class="text-xs font-semibold text-surface-700 uppercase tracking-wide">{{ $t('category') }}</span>
      </div>
      <div class="p-2 max-h-[420px] overflow-y-auto">
        <!-- All categories -->
        <button
          @click="selectCategory(null)"
          :class="[
            'flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition-colors w-full text-left',
            !filters.category && !selectedSubs.length
              ? 'bg-brand-50 text-brand-600 font-semibold'
              : 'text-surface-600 hover:bg-surface-50'
          ]"
        >
          <svg :class="['w-4 h-4', !filters.category && !selectedSubs.length ? 'text-brand-500' : 'text-surface-400']" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
          </svg>
          {{ $t('all_categories') }}
        </button>

        <div v-for="cat in categories" :key="cat.slug">
          <!-- Root category -->
          <button
            @click="selectCategory(cat.slug)"
            :class="[
              'flex items-center justify-between px-3 py-2 rounded-lg text-sm transition-colors w-full text-left',
              expandedRoot === cat.slug
                ? 'bg-brand-50 text-brand-600 font-semibold'
                : 'text-surface-600 hover:bg-surface-50'
            ]"
          >
            <span class="truncate">{{ cat.name }}</span>
            <span
              v-if="cat.vacancies_count > 0"
              :class="['text-xs tabular-nums', expandedRoot === cat.slug ? 'text-brand-400' : 'text-surface-400']"
            >{{ cat.vacancies_count }}</span>
          </button>

          <!-- Subcategories with checkboxes -->
          <div
            v-if="cat.children?.length && expandedRoot === cat.slug"
            class="ml-4 pl-3 border-l-2 border-brand-100 mb-1 space-y-0.5"
          >
            <label
              v-for="child in cat.children"
              :key="child.slug"
              class="flex items-center gap-2.5 px-2 py-1.5 rounded-md text-xs cursor-pointer hover:bg-surface-50 transition-colors"
            >
              <input
                type="checkbox"
                :value="child.slug"
                :checked="selectedSubs.includes(child.slug)"
                @change="toggleSubCategory(child.slug, $event.target.checked)"
                class="rounded border-surface-300 text-brand-500 focus:ring-brand-500 focus:ring-offset-0 w-3.5 h-3.5 cursor-pointer"
              >
              <span :class="['truncate flex-1', selectedSubs.includes(child.slug) ? 'text-brand-600 font-medium' : 'text-surface-500']">
                {{ child.name }}
              </span>
              <span v-if="child.vacancies_count > 0" class="text-[11px] text-surface-400 tabular-nums">
                {{ child.vacancies_count }}
              </span>
            </label>
          </div>
        </div>
      </div>
    </div>

    <!-- ══ Region filter with expandable cities/tumans ══ -->
    <div v-if="regions?.length" class="bg-white rounded-2xl border border-surface-100 shadow-sm overflow-hidden">
      <div class="px-4 py-3 border-b border-surface-100 flex items-center gap-2">
        <svg class="w-4 h-4 text-brand-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        <span class="text-xs font-semibold text-surface-700 uppercase tracking-wide">{{ $t('region') }}</span>
      </div>
      <div class="p-2 max-h-[420px] overflow-y-auto">
        <!-- All regions -->
        <button
          @click="clearRegion"
          :class="[
            'flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition-colors w-full text-left',
            !filters.region ? 'bg-brand-50 text-brand-600 font-semibold' : 'text-surface-600 hover:bg-surface-50'
          ]"
        >
          <svg :class="['w-4 h-4', !filters.region ? 'text-brand-500' : 'text-surface-400']" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          {{ $t('all_regions') }}
        </button>

        <div v-for="reg in regions" :key="reg.name">
          <!-- Region header -->
          <button
            @click="toggleRegion(reg.name)"
            :class="[
              'flex items-center justify-between px-3 py-2 rounded-lg text-sm transition-colors w-full text-left',
              filters.region === reg.name
                ? 'bg-brand-50 text-brand-600 font-semibold'
                : 'text-surface-600 hover:bg-surface-50'
            ]"
          >
            <span class="truncate">{{ regionName(reg.name) }}</span>
            <span class="flex items-center gap-1.5">
              <span :class="['text-xs tabular-nums', filters.region === reg.name ? 'text-brand-400' : 'text-surface-400']">{{ reg.count }}</span>
              <svg
                v-if="reg.cities?.length"
                :class="['w-3.5 h-3.5 transition-transform', expandedRegion === reg.name ? 'rotate-90 text-brand-400' : 'text-surface-300']"
                fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
              </svg>
            </span>
          </button>

          <!-- Cities/Tumans list -->
          <div
            v-if="reg.cities?.length && expandedRegion === reg.name"
            class="ml-4 pl-3 border-l-2 border-brand-100 mb-1 space-y-0.5"
          >
            <label
              v-for="city in reg.cities"
              :key="getCityKey(city, reg.cities)"
              :class="[
                'flex items-center justify-between w-full px-2 py-1.5 rounded-md text-xs transition-colors cursor-pointer',
                isCityChecked(city, reg.cities)
                  ? 'bg-brand-50 text-brand-600 font-medium'
                  : 'text-surface-500 hover:bg-surface-50'
              ]"
            >
              <span class="flex items-center gap-2 truncate">
                <input
                  type="checkbox"
                  :checked="isCityChecked(city, reg.cities)"
                  class="rounded border-surface-300 text-brand-500 focus:ring-brand-500 focus:ring-offset-0 w-3.5 h-3.5 cursor-pointer flex-shrink-0"
                  @change="toggleCityCheck(city, reg.cities, $event.target.checked)"
                >
                <span :class="['truncate', isCityChecked(city, reg.cities) ? 'text-brand-600 font-medium' : '']">
                  {{ cityDisplayName(city, reg.cities) }}
                </span>
              </span>
              <span v-if="city.count > 0" class="text-[11px] text-surface-400 tabular-nums ml-1">{{ city.count }}</span>
            </label>
          </div>
        </div>
      </div>
    </div>

    <!-- ══ Work type + Salary + Sort ══ -->
    <div class="bg-white rounded-2xl border border-surface-100 shadow-sm overflow-hidden">
      <div class="px-4 py-3 border-b border-surface-100 flex items-center gap-2">
        <svg class="w-4 h-4 text-brand-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
        </svg>
        <span class="text-xs font-semibold text-surface-700 uppercase tracking-wide">{{ $t('additional_filters') }}</span>
      </div>
      <div class="p-4 space-y-4">
        <!-- Work type -->
        <div>
          <label class="flex items-center gap-1.5 text-xs font-semibold text-surface-500 uppercase tracking-wide mb-2">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ $t('work_type') }}
          </label>
          <select
            v-model="filters.work_type"
            @change="emitSearch"
            class="w-full py-2.5 px-3 rounded-xl border border-surface-200 focus:ring-2 focus:ring-brand-500 text-sm bg-surface-50 appearance-none"
          >
            <option value="">{{ $t('all_types') }}</option>
            <option value="full_time">{{ $t('full_time') }}</option>
            <option value="part_time">{{ $t('part_time') }}</option>
            <option value="remote">{{ $t('remote') }}</option>
            <option value="temporary">{{ $t('temporary') }}</option>
          </select>
        </div>

        <!-- Salary range -->
        <div>
          <label class="flex items-center gap-1.5 text-xs font-semibold text-surface-500 uppercase tracking-wide mb-2">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ $t('salary') }} ({{ $t('som') }})
          </label>
          <div class="flex items-center gap-2">
            <input
              v-model="filters.salary_min"
              type="number"
              :placeholder="$t('salary_from')"
              class="w-full py-2.5 px-3 rounded-xl border border-surface-200 focus:ring-2 focus:ring-brand-500 text-sm bg-surface-50"
              @keyup.enter="emitSearch"
            >
            <span class="text-surface-300 font-medium">&mdash;</span>
            <input
              v-model="filters.salary_max"
              type="number"
              :placeholder="$t('salary_to')"
              class="w-full py-2.5 px-3 rounded-xl border border-surface-200 focus:ring-2 focus:ring-brand-500 text-sm bg-surface-50"
              @keyup.enter="emitSearch"
            >
          </div>
        </div>

        <!-- Sort -->
        <div>
          <label class="flex items-center gap-1.5 text-xs font-semibold text-surface-500 uppercase tracking-wide mb-2">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/>
            </svg>
            {{ $t('sort') }}
          </label>
          <select
            v-model="filters.sort"
            @change="emitSearch"
            class="w-full py-2.5 px-3 rounded-xl border border-surface-200 focus:ring-2 focus:ring-brand-500 text-sm bg-surface-50 appearance-none"
          >
            <option value="">{{ $t('sort_newest') }}</option>
            <option value="salary_asc">{{ $t('sort_salary_asc') }}</option>
            <option value="salary_desc">{{ $t('sort_salary_desc') }}</option>
          </select>
        </div>

        <!-- Action buttons -->
        <div class="flex gap-2 pt-1">
          <button
            @click="emitSearch"
            class="flex-1 bg-brand-500 hover:bg-brand-600 text-white py-2.5 rounded-xl font-semibold text-sm transition-colors flex items-center justify-center gap-1.5"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
            </svg>
            {{ $t('apply_filters') }}
          </button>
          <button
            v-if="hasActiveFilters"
            @click="resetAll"
            class="px-4 py-2.5 rounded-xl border border-surface-200 text-sm text-surface-600 hover:bg-surface-50 transition-colors flex items-center gap-1"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            {{ $t('reset_filters') }}
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const { tm } = useI18n();

const props = defineProps({
  filters: { type: Object, required: true },
  categories: { type: Array, default: () => [] },
  regions: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:filters', 'search', 'categoryChange', 'regionChange']);

// Track which root category is expanded (shows subcategory checkboxes)
const expandedRoot = ref('');
// Track selected subcategory slugs
const selectedSubs = ref([]);
// Track which region is expanded (shows cities/tumans)
const expandedRegion = ref('');

// Sync expandedRoot from filters on init and when filters change externally
watch(() => props.filters.category, (val) => {
  if (!val) {
    expandedRoot.value = '';
    selectedSubs.value = [];
    return;
  }
  // If category is a string (root slug), expand it
  if (typeof val === 'string') {
    expandedRoot.value = val;
    selectedSubs.value = [];
  }
}, { immediate: true });

// Check if selectedSubs is set externally (from URL)
watch(() => props.filters.selectedSubs, (val) => {
  if (Array.isArray(val) && val.length) {
    selectedSubs.value = [...val];
    // Find which root these subs belong to
    if (props.categories?.length) {
      for (const cat of props.categories) {
        const childSlugs = cat.children?.map(c => c.slug) || [];
        if (val.some(s => childSlugs.includes(s))) {
          expandedRoot.value = cat.slug;
          break;
        }
      }
    }
  }
}, { immediate: true });

function regionName(region) {
  const names = tm('region_names');
  return names?.[region] || region;
}

// Selected city keys for multi-select (like selectedSubs for categories)
const selectedCities = ref([]);

// Unique key for city (handles duplicate names within region)
function getCityKey(city, allCities) {
  const dupes = allCities.filter(c => c.name === city.name);
  return dupes.length > 1 ? `${city.name}::${city.type}` : city.name;
}

// Display name for city (shows type suffix for duplicates)
function cityDisplayName(city, allCities) {
  const dupes = allCities.filter(c => c.name === city.name);
  return dupes.length > 1 ? `${city.name} ${city.type}` : city.name;
}

function isCityChecked(city, allCities) {
  return selectedCities.value.includes(getCityKey(city, allCities));
}

const hasActiveFilters = computed(() => {
  return props.filters.q || props.filters.category || props.filters.region ||
    props.filters.work_type || props.filters.salary_min ||
    props.filters.salary_max || selectedSubs.value.length > 0 || selectedCities.value.length > 0;
});

function selectCategory(slug) {
  selectedSubs.value = [];
  if (!slug) {
    // "All categories" selected
    expandedRoot.value = '';
    props.filters.category = '';
  } else {
    expandedRoot.value = slug;
    props.filters.category = slug;
  }
  emit('categoryChange', { category: props.filters.category, selectedSubs: [] });
  emitSearch();
}

function clearRegion() {
  expandedRegion.value = '';
  selectedCities.value = [];
  props.filters.region = '';
  emit('regionChange', { region: '', selectedCities: [] });
  emitSearch();
}

function toggleRegion(regionName) {
  if (expandedRegion.value === regionName) {
    expandedRegion.value = '';
  } else {
    expandedRegion.value = regionName;
    selectedCities.value = [];
    props.filters.region = regionName;
    emit('regionChange', { region: regionName, selectedCities: [] });
    emitSearch();
  }
}

function toggleCityCheck(city, allCities, checked) {
  const key = getCityKey(city, allCities);
  if (checked) {
    selectedCities.value = [...selectedCities.value, key];
  } else {
    selectedCities.value = selectedCities.value.filter(k => k !== key);
  }
  applyCitySelection();
}

function applyCitySelection() {
  const region = expandedRegion.value;
  if (!region) return;

  const reg = props.regions?.find(r => r.name === region);
  if (!reg) return;

  const allCityKeys = reg.cities?.map(c => getCityKey(c, reg.cities)) || [];
  const allChecked = selectedCities.value.length === allCityKeys.length;
  const noneChecked = selectedCities.value.length === 0;

  if (noneChecked || allChecked) {
    props.filters.region = region;
    emit('regionChange', { region, selectedCities: [] });
  } else {
    props.filters.region = region;
    emit('regionChange', { region, selectedCities: [...selectedCities.value] });
  }
  emitSearch();
}

function toggleSubCategory(slug, checked) {
  if (checked) {
    selectedSubs.value = [...selectedSubs.value, slug];
  } else {
    selectedSubs.value = selectedSubs.value.filter(s => s !== slug);
  }
  applySubCategories();
}

function applySubCategories() {
  const rootCat = props.categories?.find(c => c.slug === expandedRoot.value);
  if (!rootCat) return;

  const allChildSlugs = rootCat.children?.map(c => c.slug) || [];
  const allChecked = selectedSubs.value.length === allChildSlugs.length;
  const noneChecked = selectedSubs.value.length === 0;

  if (noneChecked || allChecked) {
    // All or none → just use root category
    props.filters.category = expandedRoot.value;
    emit('categoryChange', { category: expandedRoot.value, selectedSubs: [] });
  } else {
    // Specific subcategories
    props.filters.category = expandedRoot.value;
    emit('categoryChange', { category: expandedRoot.value, selectedSubs: [...selectedSubs.value] });
  }
  emitSearch();
}

function emitSearch() {
  emit('search');
}

// Sync expandedRegion from filters
watch(() => props.filters.region, (val) => {
  if (val) {
    expandedRegion.value = val;
  }
}, { immediate: true });

// Sync selectedCities from external (URL)
watch(() => props.filters.selectedCities, (val) => {
  if (Array.isArray(val) && val.length) {
    selectedCities.value = [...val];
    if (props.regions?.length) {
      for (const reg of props.regions) {
        const regionCityKeys = reg.cities?.map(c => getCityKey(c, reg.cities)) || [];
        if (val.some(k => regionCityKeys.includes(k))) {
          expandedRegion.value = reg.name;
          break;
        }
      }
    }
  }
}, { immediate: true });

function resetAll() {
  Object.keys(props.filters).forEach(k => {
    if (k !== 'selectedSubs' && k !== 'selectedCities') props.filters[k] = '';
  });
  expandedRoot.value = '';
  expandedRegion.value = '';
  selectedSubs.value = [];
  selectedCities.value = [];
  emit('categoryChange', { category: '', selectedSubs: [] });
  emit('regionChange', { region: '', selectedCities: [] });
  emitSearch();
}
</script>
