<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <BreadcrumbNav :items="breadcrumbItems" />

    <div class="mt-4 lg:flex lg:gap-6">
      <!-- Sidebar filters (desktop) -->
      <aside class="hidden lg:block lg:w-72 xl:w-80 flex-shrink-0">
        <VacancyFilters
          v-model:filters="filters"
          :categories="meta?.categories"
          :regions="meta?.regions"
          @search="onSearch"
          @category-change="onCategoryChange"
          @region-change="onRegionChange"
        />
      </aside>

      <!-- Main content -->
      <div class="flex-1 min-w-0">
        <!-- Mobile filter button -->
        <div class="lg:hidden mb-4">
          <button
            @click="showMobileFilters = true"
            class="flex items-center justify-between w-full bg-white border border-surface-200 rounded-xl px-4 py-3 shadow-sm"
          >
            <span class="flex items-center gap-2 text-sm font-medium text-surface-700">
              <svg class="w-5 h-5 text-brand-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
              </svg>
              {{ $t('filters') }}
              <span v-if="activeFilterCount > 0" class="bg-brand-500 text-white text-[10px] font-bold w-5 h-5 rounded-full flex items-center justify-center">
                {{ activeFilterCount }}
              </span>
            </span>
            <svg class="w-5 h-5 text-surface-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>
        </div>

        <!-- Results header + filter chips -->
        <div class="flex items-center justify-between mb-5">
          <p class="text-sm text-surface-500 flex items-center gap-1.5" v-if="total !== null">
            <svg class="w-4 h-4 text-surface-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2zM16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/>
            </svg>
            {{ $t('results_count', { count: total }) }}
          </p>

          <!-- Active filter chips -->
          <FilterChips
            :filters="filters"
            :selected-subs="selectedSubs"
            :selected-cities="selectedCities"
            :categories="meta?.categories"
            @remove="removeFilter"
            @reset="resetFilters"
          />
        </div>

        <!-- Loading skeleton -->
        <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
          <PageSkeleton v-for="i in 6" :key="i" />
        </div>

        <!-- Vacancy list -->
        <div v-else-if="vacancies.length" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
          <VacancyCard v-for="v in vacancies" :key="v.id" :vacancy="v" />
        </div>

        <!-- Empty state -->
        <div v-else class="text-center py-20 bg-white rounded-2xl border border-surface-100">
          <div class="w-20 h-20 bg-surface-50 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-10 h-10 text-surface-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
          </div>
          <h3 class="text-lg font-semibold text-surface-700 mb-2">{{ $t('no_results') }}</h3>
          <p class="text-surface-400 text-sm mb-6">{{ $t('no_results_hint') }}</p>
          <button
            @click="resetFilters"
            class="inline-flex items-center gap-2 bg-brand-50 text-brand-600 hover:bg-brand-100 font-medium text-sm px-5 py-2.5 rounded-xl transition-colors"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            {{ $t('reset_filters') }}
          </button>
        </div>

        <!-- Pagination -->
        <div v-if="lastPage > 1" class="mt-8 flex justify-center">
          <nav class="flex items-center gap-1.5">
            <!-- Prev -->
            <button
              @click="goToPage(currentPage - 1)"
              :disabled="currentPage === 1"
              class="p-2 rounded-lg text-sm transition-colors border border-surface-200 bg-white text-surface-500 hover:bg-surface-100 disabled:opacity-30 disabled:cursor-not-allowed"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
              </svg>
            </button>

            <!-- First page -->
            <button v-if="visiblePages[0] > 1" @click="goToPage(1)" class="px-3 py-1.5 rounded-lg text-sm font-medium bg-white text-surface-600 hover:bg-surface-100 border border-surface-200">1</button>
            <span v-if="visiblePages[0] > 2" class="px-1 text-surface-400">...</span>

            <!-- Page numbers -->
            <button
              v-for="page in visiblePages"
              :key="page"
              @click="goToPage(page)"
              :class="[
                'px-3 py-1.5 rounded-lg text-sm font-medium transition-colors',
                page === currentPage
                  ? 'bg-brand-500 text-white shadow-sm'
                  : 'bg-white text-surface-600 hover:bg-surface-100 border border-surface-200'
              ]"
            >
              {{ page }}
            </button>

            <!-- Last page -->
            <span v-if="visiblePages[visiblePages.length - 1] < lastPage - 1" class="px-1 text-surface-400">...</span>
            <button v-if="visiblePages[visiblePages.length - 1] < lastPage" @click="goToPage(lastPage)" class="px-3 py-1.5 rounded-lg text-sm font-medium bg-white text-surface-600 hover:bg-surface-100 border border-surface-200">{{ lastPage }}</button>

            <!-- Next -->
            <button
              @click="goToPage(currentPage + 1)"
              :disabled="currentPage === lastPage"
              class="p-2 rounded-lg text-sm transition-colors border border-surface-200 bg-white text-surface-500 hover:bg-surface-100 disabled:opacity-30 disabled:cursor-not-allowed"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
              </svg>
            </button>
          </nav>
        </div>
      </div>
    </div>

    <!-- Mobile filters modal -->
    <teleport to="body">
      <transition name="slide-up">
        <div v-if="showMobileFilters" class="fixed inset-0 z-50 lg:hidden">
          <div class="absolute inset-0 bg-black/50" @click="showMobileFilters = false"></div>
          <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-2xl max-h-[85vh] overflow-y-auto p-4">
            <div class="flex items-center justify-between mb-4">
              <h3 class="font-semibold text-lg">{{ $t('filters') }}</h3>
              <button @click="showMobileFilters = false" class="p-1 text-surface-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            <VacancyFilters
              v-model:filters="filters"
              :categories="meta?.categories"
              :regions="meta?.regions"
              @search="onSearch(); showMobileFilters = false"
              @category-change="onCategoryChange"
              @region-change="onRegionChange"
            />
          </div>
        </div>
      </transition>
    </teleport>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useApi } from '@website/composables/useApi';
import { useSeo } from '@website/composables/useSeo';
import BreadcrumbNav from '@website/components/BreadcrumbNav.vue';
import VacancyCard from '@website/components/VacancyCard.vue';
import VacancyFilters from '@website/components/VacancyFilters.vue';
import FilterChips from '@website/components/FilterChips.vue';
import PageSkeleton from '@website/components/PageSkeleton.vue';

const route = useRoute();
const router = useRouter();
const { t } = useI18n();
const { getVacancies } = useApi();
const seo = useSeo();

const vacancies = ref([]);
const meta = ref(null);
const loading = ref(true);
const total = ref(null);
const currentPage = ref(1);
const lastPage = ref(1);
const showMobileFilters = ref(false);

// Selected subcategories (separate from main filters)
const selectedSubs = ref([]);
// Selected cities for multi-select (like selectedSubs for categories)
const selectedCities = ref([]);

const filters = reactive({
  q: '',
  category: '',
  region: '',
  work_type: '',
  salary_min: '',
  salary_max: '',
  sort: '',
});

// Breadcrumb with active category
const breadcrumbItems = computed(() => {
  const items = [
    { label: t('home'), to: '/' },
    { label: t('vacancies') },
  ];
  if (filters.category && meta.value?.categories) {
    const cat = meta.value.categories.find(c => c.slug === filters.category);
    if (cat) {
      items[1] = { label: t('vacancies'), to: '/vacancies' };
      items.push({ label: cat.name });
    }
  }
  return items;
});

// Active filter count for mobile badge
const activeFilterCount = computed(() => {
  return ['q', 'category', 'region', 'work_type', 'salary_min', 'salary_max']
    .filter(k => filters[k]).length
    + (selectedSubs.value.length > 0 ? 1 : 0)
    + (selectedCities.value.length > 0 ? 1 : 0);
});

onMounted(() => {
  syncFiltersFromUrl();
  fetchVacancies();
  seo.set({ title: 'Vakansiyalar — KadrGo', description: 'KadrGo dagi barcha vakansiyalar' });
});

function syncFiltersFromUrl() {
  const q = route.query;
  filters.q = q.q || '';
  filters.region = q.region || '';
  filters.work_type = q.work_type || '';

  // Handle city - can be string or array (composite keys with ::type)
  const cityParam = q.city || q['city[]'];
  if (Array.isArray(cityParam)) {
    selectedCities.value = cityParam;
  } else if (cityParam) {
    selectedCities.value = [cityParam];
  } else {
    selectedCities.value = [];
  }
  filters.salary_min = q.salary_min || '';
  filters.salary_max = q.salary_max || '';
  filters.sort = q.sort || '';
  currentPage.value = parseInt(q.page) || 1;

  // Handle category - can be string or array
  const catParam = q.category || q['category[]'];
  if (Array.isArray(catParam)) {
    // Subcategory array from URL: ?category[]=slug1&category[]=slug2
    selectedSubs.value = catParam;
    // Find which root this belongs to (will be set after meta loads)
    filters.category = '';
  } else if (catParam) {
    filters.category = catParam;
    selectedSubs.value = [];
  } else {
    filters.category = '';
    selectedSubs.value = [];
  }
}

function onCategoryChange({ category, selectedSubs: subs }) {
  selectedSubs.value = subs || [];
  currentPage.value = 1;
}

function onRegionChange({ region, selectedCities: cities }) {
  selectedCities.value = cities || [];
  currentPage.value = 1;
}

function onSearch() {
  currentPage.value = 1;
  fetchVacancies();
}

async function fetchVacancies() {
  loading.value = true;
  try {
    const params = { page: currentPage.value, per_page: 21 };

    // Add simple string filters
    ['q', 'region', 'work_type', 'salary_min', 'salary_max', 'sort'].forEach(k => {
      if (filters[k]) params[k] = filters[k];
    });

    // Handle city (array from selectedCities)
    if (selectedCities.value.length > 0) {
      // Extract unique city names (strip ::type suffix for composite keys)
      const cityNames = [...new Set(selectedCities.value.map(k => k.split('::')[0]))];
      params['city[]'] = cityNames;
      // When specific cities selected, don't send region (API checks city first)
      delete params.region;
    }

    // Handle category (string or array)
    if (selectedSubs.value.length > 0) {
      // Send as array: category[]=slug1&category[]=slug2
      params['category[]'] = selectedSubs.value;
    } else if (filters.category) {
      params.category = filters.category;
    }

    // Build URL query for router
    const urlQuery = { ...params };
    if (urlQuery['category[]']) {
      const subs = urlQuery['category[]'];
      delete urlQuery['category[]'];
      if (Array.isArray(subs)) {
        subs.forEach((s, i) => {
          urlQuery[`category[${i}]`] = s;
        });
      }
    }
    if (urlQuery['city[]']) {
      const cities = urlQuery['city[]'];
      delete urlQuery['city[]'];
      if (Array.isArray(cities)) {
        cities.forEach((c, i) => {
          urlQuery[`city[${i}]`] = c;
        });
      }
    }
    router.replace({ query: urlQuery });

    // Build API params
    const apiParams = {};
    Object.keys(params).forEach(k => {
      if (k === 'category[]') {
        apiParams['category'] = params[k];
      } else if (k === 'city[]') {
        apiParams['city'] = params[k];
      } else {
        apiParams[k] = params[k];
      }
    });

    const res = await getVacancies(apiParams);
    vacancies.value = res.data.data;
    meta.value = res.data.meta;
    total.value = res.data.total;
    lastPage.value = res.data.last_page;

    // After meta loads, sync expandedRoot for subcategory URL
    if (selectedSubs.value.length > 0 && meta.value?.categories) {
      for (const cat of meta.value.categories) {
        const childSlugs = cat.children?.map(c => c.slug) || [];
        if (selectedSubs.value.some(s => childSlugs.includes(s))) {
          filters.category = cat.slug;
          break;
        }
      }
    }
  } catch (e) {
    console.error('Vacancies fetch error:', e);
  } finally {
    loading.value = false;
  }
}

function goToPage(page) {
  currentPage.value = page;
  fetchVacancies();
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function removeFilter(key) {
  if (key === 'category') {
    filters.category = '';
    selectedSubs.value = [];
  } else if (key === 'city') {
    selectedCities.value = [];
  } else if (key === 'region') {
    filters.region = '';
    selectedCities.value = [];
  } else {
    filters[key] = '';
  }
  currentPage.value = 1;
  fetchVacancies();
}

function resetFilters() {
  Object.keys(filters).forEach(k => filters[k] = '');
  selectedSubs.value = [];
  selectedCities.value = [];
  currentPage.value = 1;
  fetchVacancies();
}

const visiblePages = computed(() => {
  const pages = [];
  const start = Math.max(1, currentPage.value - 2);
  const end = Math.min(lastPage.value, currentPage.value + 2);
  for (let i = start; i <= end; i++) pages.push(i);
  return pages;
});
</script>

<style scoped>
.slide-up-enter-active,
.slide-up-leave-active {
  transition: all 0.3s ease;
}
.slide-up-enter-from,
.slide-up-leave-to {
  opacity: 0;
  transform: translateY(100%);
}
</style>
