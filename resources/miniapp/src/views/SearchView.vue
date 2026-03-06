<template>
  <div class="search-view">
    <!-- Search Bar (Sticky) -->
    <div class="sticky top-0 z-10 p-4 pb-3" style="background-color: var(--tg-theme-bg-color);">
      <div class="input flex items-center gap-2">
        <svg class="w-5 h-5 flex-shrink-0" style="color: var(--tg-theme-hint-color);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <input
          v-model="searchQuery"
          type="text"
          :placeholder="t('search.placeholder')"
          class="flex-1 outline-none bg-transparent"
          style="color: var(--tg-theme-text-color);"
          @input="debouncedSearch"
        />
        <button v-if="searchQuery" @click="clearSearch" style="color: var(--tg-theme-hint-color);">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Filter Buttons -->
      <div class="flex gap-2 mt-3 overflow-x-auto no-scrollbar">
        <button
          class="whitespace-nowrap flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-medium active:scale-[0.97] transition-transform"
          :style="showFilters
            ? { backgroundColor: 'var(--tg-theme-button-color)', color: 'var(--tg-theme-button-text-color)' }
            : { backgroundColor: 'var(--tg-theme-secondary-bg-color)', color: 'var(--tg-theme-text-color)' }"
          @click="showFilters = !showFilters"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
          </svg>
          {{ t('search.filters') }}
          <span
            v-if="activeFiltersCount > 0"
            class="w-5 h-5 flex items-center justify-center rounded-full text-xs font-bold"
            style="background-color: var(--tg-theme-button-text-color, #fff); color: var(--tg-theme-button-color, #3b82f6);"
          >
            {{ activeFiltersCount }}
          </span>
        </button>
        <button
          class="whitespace-nowrap flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-medium active:scale-[0.97] transition-transform"
          :style="{ backgroundColor: 'var(--tg-theme-secondary-bg-color)', color: 'var(--tg-theme-text-color)' }"
          @click="searchNearby"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
          {{ t('search.nearby') }}
        </button>
      </div>
    </div>

    <!-- Filters Panel -->
    <transition name="slide">
      <div v-if="showFilters" class="px-4 pt-2 pb-4" style="background-color: var(--tg-theme-bg-color);">
        <div class="card space-y-4 p-4">
          <!-- Category -->
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wide mb-1.5" style="color: var(--tg-theme-hint-color);">
              {{ t('search.category') }}
            </label>
            <select v-model="filters.category" class="input">
              <option value="">{{ t('common.all') }}</option>
              <option v-for="cat in categories" :key="cat.slug" :value="cat.slug">
                {{ cat.name_uz }}
              </option>
            </select>
          </div>

          <!-- City -->
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wide mb-1.5" style="color: var(--tg-theme-hint-color);">
              {{ t('search.city') }}
            </label>
            <select v-model="filters.city" class="input">
              <option value="">{{ t('common.all') }}</option>
              <option v-for="city in cities" :key="city.name_uz" :value="city.name_uz">
                {{ city.name_uz }}
              </option>
            </select>
          </div>

          <!-- Work Type -->
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wide mb-1.5" style="color: var(--tg-theme-hint-color);">
              {{ t('search.work_type') }}
            </label>
            <select v-model="filters.work_type" class="input">
              <option value="">{{ t('common.all') }}</option>
              <option value="full_time">{{ t('work_type.full_time') }}</option>
              <option value="part_time">{{ t('work_type.part_time') }}</option>
              <option value="remote">{{ t('work_type.remote') }}</option>
              <option value="temporary">{{ t('work_type.temporary') }}</option>
            </select>
          </div>

          <!-- Salary Range -->
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wide mb-1.5" style="color: var(--tg-theme-hint-color);">
              {{ t('search.salary') }}
            </label>
            <div class="flex gap-2">
              <input
                v-model.number="filters.salary_min"
                type="number"
                :placeholder="t('search.salary_from')"
                class="input flex-1"
              />
              <input
                v-model.number="filters.salary_max"
                type="number"
                :placeholder="t('search.salary_to')"
                class="input flex-1"
              />
            </div>
          </div>

          <!-- Buttons -->
          <div class="flex gap-3 pt-1">
            <button class="btn-primary flex-1" @click="applyFilters">
              {{ t('common.apply') }}
            </button>
            <button class="btn-secondary flex-1" @click="resetFilters">
              {{ t('common.reset') }}
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- Results -->
    <div class="p-4 pb-20">
      <!-- Results Count -->
      <div v-if="!vacancyStore.loading && vacancyStore.vacancies.length > 0" class="mb-3 text-sm" style="color: var(--tg-theme-hint-color);">
        {{ vacancyStore.pagination.total }} {{ t('search.results_count') }}
      </div>

      <!-- Loading -->
      <LoadingSpinner v-if="vacancyStore.loading" />

      <!-- No Results -->
      <div v-else-if="vacancyStore.vacancies.length === 0" class="text-center py-16">
        <p class="text-4xl mb-3">🔍</p>
        <p class="text-base font-medium mb-1">{{ t('search.no_results') }}</p>
        <p class="text-sm" style="color: var(--tg-theme-hint-color);">{{ t('search.try_different') }}</p>
      </div>

      <!-- Vacancy List -->
      <div v-else class="space-y-3">
        <VacancyCard v-for="vacancy in vacancyStore.vacancies" :key="vacancy.id" :vacancy="vacancy" />
      </div>

      <!-- Load More -->
      <div v-if="hasMore" class="mt-4 text-center">
        <button class="btn-secondary px-8" @click="loadMore" :disabled="vacancyStore.loading">
          {{ t('common.load_more') }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useVacancyStore } from '@/stores/vacancy'
import { useGeolocation } from '@/composables/useGeolocation'
import { useTelegram } from '@/composables/useTelegram'
import { useLocale } from '@/composables/useLocale'
import VacancyCard from '@/components/VacancyCard.vue'
import LoadingSpinner from '@/components/LoadingSpinner.vue'
import api from '@/utils/api'

const route = useRoute()
const router = useRouter()
const vacancyStore = useVacancyStore()
const { getLocation } = useGeolocation()
const telegram = useTelegram()
const { t } = useLocale()

const searchQuery = ref('')
const showFilters = ref(false)
const filters = ref({
  category: '',
  city: '',
  work_type: '',
  salary_min: null,
  salary_max: null,
})
const categories = ref([])
const cities = ref([])

const activeFiltersCount = computed(() => {
  return Object.values(filters.value).filter(v => v !== '' && v !== null).length
})

const hasMore = computed(() => {
  return vacancyStore.pagination.current_page < vacancyStore.pagination.last_page
})

let searchTimeout = null

onMounted(async () => {
  // Load categories and cities
  try {
    const [catRes, cityRes] = await Promise.all([
      api.get('/categories'),
      api.get('/cities'),
    ])
    categories.value = catRes.data.categories || catRes.data || []
    cities.value = cityRes.data.cities || cityRes.data || []
  } catch (error) {
    console.error('Failed to load filters:', error)
  }

  if (route.query.nearby === 'true') {
    await searchNearby()
  } else if (route.query.is_top === 'true') {
    await vacancyStore.fetchVacancies({ is_top: true })
  } else {
    await vacancyStore.fetchVacancies()
  }
})

function debouncedSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    performSearch()
  }, 500)
}

async function performSearch() {
  await vacancyStore.searchVacancies({
    q: searchQuery.value,
    ...filters.value,
  })
}

function clearSearch() {
  searchQuery.value = ''
  performSearch()
}

async function applyFilters() {
  telegram.hapticFeedback('soft')
  showFilters.value = false
  await performSearch()
}

function resetFilters() {
  filters.value = {
    category: '',
    city: '',
    work_type: '',
    salary_min: null,
    salary_max: null,
  }
  vacancyStore.resetFilters()
  performSearch()
}

async function searchNearby() {
  try {
    telegram.hapticFeedback('medium')
    const coords = await getLocation()
    await vacancyStore.nearbyVacancies(coords.lat, coords.lng, 10)
  } catch (error) {
    telegram.showAlert(t('search.geo_denied'))
  }
}

async function loadMore() {
  const nextPage = vacancyStore.pagination.current_page + 1
  await vacancyStore.fetchVacancies({
    page: nextPage,
    q: searchQuery.value,
    ...filters.value,
  })
}
</script>

<style scoped>
.slide-enter-active,
.slide-leave-active {
  transition: all 0.25s ease;
  max-height: 500px;
  overflow: hidden;
}

.slide-enter-from,
.slide-leave-to {
  max-height: 0;
  opacity: 0;
  padding-top: 0;
  padding-bottom: 0;
}

.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

.no-scrollbar::-webkit-scrollbar {
  display: none;
}
</style>
