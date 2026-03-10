<template>
  <div class="search-view" style="background-color: var(--tg-theme-bg-color);">
    <!-- Search Header (Sticky) -->
    <div class="search-header sticky top-0 z-10">
      <div class="px-4 pt-3 pb-2.5">
        <!-- Search Input -->
        <div class="search-input-wrap">
          <svg class="w-[18px] h-[18px] flex-shrink-0" style="color: var(--tg-theme-hint-color); opacity: 0.6;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            v-model="searchQuery"
            type="text"
            :placeholder="t('search.placeholder')"
            class="flex-1 outline-none bg-transparent text-[14px]"
            style="color: var(--tg-theme-text-color);"
            @input="debouncedSearch"
          />
          <button v-if="searchQuery" @click="clearSearch" class="p-0.5 rounded-full active:scale-90 transition-transform" style="color: var(--tg-theme-hint-color);">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Filter Chips -->
        <div class="flex gap-2 mt-2.5 overflow-x-auto no-scrollbar">
          <button
            class="filter-chip"
            :class="{ 'filter-chip-active': showFilters }"
            @click="showFilters = !showFilters"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" />
            </svg>
            {{ t('search.filters') }}
            <span v-if="activeFiltersCount > 0" class="filter-badge">
              {{ activeFiltersCount }}
            </span>
          </button>
          <button
            class="filter-chip"
            @click="searchNearby"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
            </svg>
            {{ t('search.nearby') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Filters Panel -->
    <transition name="slide">
      <div v-if="showFilters" class="filters-panel">
        <div class="mx-4 mb-3 filters-card">
          <!-- Category -->
          <div class="filter-group-compact">
            <label class="filter-label">{{ t('search.category') }}</label>
            <div class="select-wrap">
              <select v-model="filters.category" class="filter-select">
                <option value="">{{ t('common.all') }}</option>
                <option v-for="cat in referenceStore.categories" :key="cat.slug" :value="cat.slug">
                  {{ lang === 'ru' ? (cat.name_ru || cat.name_uz) : (cat.name_uz || cat.name_ru) }}
                </option>
              </select>
              <svg class="select-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
              </svg>
            </div>
          </div>

          <!-- Region + City -->
          <div class="filter-group-compact">
            <label class="filter-label">{{ t('search.location') }}</label>
            <div class="filter-row-2">
              <div class="picker-trigger" @click="openLocPicker('region')">
                <span :class="filterRegion ? 'pv' : 'pp'">{{ filterRegion || t('post.region') }}</span>
                <svg class="pchev" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                </svg>
              </div>
              <div
                class="picker-trigger"
                :class="{ 'picker-trigger-off': !filterRegion }"
                @click="filterRegion && openLocPicker('city')"
              >
                <span :class="filters.city ? 'pv' : 'pp'">{{ filters.city || t('post.city') }}</span>
                <svg class="pchev" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                </svg>
              </div>
            </div>
            <button
              v-if="filterRegion"
              type="button"
              class="loc-clear"
              @click="clearLocation"
            >
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
              {{ filterRegion }}{{ filters.city ? ' · ' + filters.city : '' }}
            </button>
          </div>

          <!-- Work Type chips -->
          <div class="filter-group-compact">
            <label class="filter-label">{{ t('search.work_type') }}</label>
            <div class="wt-chips">
              <button
                type="button"
                class="wt-chip"
                :class="{ 'wt-chip-on': filters.work_type === '' }"
                @click="filters.work_type = ''"
              >{{ t('common.all') }}</button>
              <button
                v-for="wt in workTypeOptions"
                :key="wt.value"
                type="button"
                class="wt-chip"
                :class="{ 'wt-chip-on': filters.work_type === wt.value }"
                @click="filters.work_type = wt.value"
              >{{ wt.label }}</button>
            </div>
          </div>

          <!-- Salary Range -->
          <div class="filter-group-compact">
            <label class="filter-label">{{ t('search.salary') }}</label>
            <div class="salary-row">
              <input
                v-model.number="filters.salary_min"
                type="number"
                inputmode="numeric"
                :placeholder="t('search.salary_from')"
                class="filter-input"
              />
              <span class="salary-dash">—</span>
              <input
                v-model.number="filters.salary_max"
                type="number"
                inputmode="numeric"
                :placeholder="t('search.salary_to')"
                class="filter-input"
              />
            </div>
          </div>

          <!-- Reset -->
          <button v-if="activeFiltersCount > 0" class="filter-reset-btn" @click="resetFilters">
            {{ t('common.reset') }}
          </button>
        </div>
      </div>
    </transition>

    <!-- Location Picker Bottom Sheet -->
    <transition name="sheet">
      <div v-if="locPickerType" class="sheet-backdrop" @click="locPickerType = null">
        <div class="sheet-panel" @click.stop>
          <div class="sheet-handle"></div>
          <div class="sheet-title">{{ locPickerType === 'region' ? t('post.region') : t('post.city') }}</div>

          <div v-if="locPickerItems.length > 8" class="sheet-search">
            <svg class="sheet-search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
            </svg>
            <input v-model="locSearch" class="sheet-search-input" :placeholder="t('home.search_placeholder')" />
          </div>

          <div class="sheet-options">
            <div
              v-for="item in locPickerFiltered"
              :key="item.value"
              class="sheet-option"
              :class="{ 'sheet-option-active': isLocActive(item) }"
              @click="onLocSelect(item)"
            >
              <div class="sheet-option-content">
                <span class="sheet-option-label">{{ item.label }}</span>
                <span v-if="item.badge" class="sheet-option-badge" :class="item.badgeClass">{{ item.badge }}</span>
              </div>
              <svg v-if="isLocActive(item)" class="sheet-option-check" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
              </svg>
            </div>
            <div v-if="locPickerFiltered.length === 0" class="sheet-empty">
              {{ t('search.no_results') }}
            </div>
          </div>
        </div>
      </div>
    </transition>

    <!-- Results -->
    <div class="px-4 pt-3 pb-20">
      <!-- Results Count -->
      <div v-if="!vacancyStore.loading && vacancyStore.vacancies.length > 0" class="mb-3">
        <span class="text-[12px] font-medium" style="color: var(--tg-theme-hint-color);">
          {{ vacancyStore.pagination.total }} {{ t('search.results_count') }}
        </span>
      </div>

      <!-- Loading -->
      <div v-if="vacancyStore.loading" class="py-16">
        <LoadingSpinner />
      </div>

      <!-- No Results -->
      <div v-else-if="vacancyStore.vacancies.length === 0" class="text-center py-16">
        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl flex items-center justify-center" style="background-color: var(--tg-theme-secondary-bg-color);">
          <svg class="w-8 h-8" style="color: var(--tg-theme-hint-color);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
          </svg>
        </div>
        <p class="text-[15px] font-semibold mb-1" style="color: var(--tg-theme-text-color);">{{ t('search.no_results') }}</p>
        <p class="text-[13px]" style="color: var(--tg-theme-hint-color);">{{ t('search.try_different') }}</p>
      </div>

      <!-- Vacancy List -->
      <div v-else class="space-y-2.5">
        <VacancyCard v-for="vacancy in vacancyStore.vacancies" :key="vacancy.id" :vacancy="vacancy" />
      </div>

      <!-- Load More -->
      <div v-if="hasMore" class="mt-4 text-center">
        <button class="load-more-btn" @click="loadMore" :disabled="vacancyStore.loading">
          {{ t('common.load_more') }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useVacancyStore } from '@/stores/vacancy'
import { useReferenceStore } from '@/stores/reference'
import { useGeolocation } from '@/composables/useGeolocation'
import { useTelegram } from '@/composables/useTelegram'
import { useLocale } from '@/composables/useLocale'
import VacancyCard from '@/components/VacancyCard.vue'
import LoadingSpinner from '@/components/LoadingSpinner.vue'

const route = useRoute()
const router = useRouter()
const vacancyStore = useVacancyStore()
const referenceStore = useReferenceStore()
const { getLocation } = useGeolocation()
const telegram = useTelegram()
const { t, lang } = useLocale()

const searchQuery = ref('')
const showFilters = ref(false)
const filters = ref({
  category: '',
  city: '',
  work_type: '',
  salary_min: null,
  salary_max: null,
})

// ── Location Picker ──
const filterRegion = ref('')
const locPickerType = ref(null) // 'region' | 'city'
const locSearch = ref('')

const regions = computed(() => {
  const set = new Set()
  for (const city of referenceStore.cities) {
    if (city.region) set.add(city.region)
  }
  return [...set].sort((a, b) => a.localeCompare(b))
})

const filteredCities = computed(() => {
  if (!filterRegion.value) return []
  return referenceStore.cities
    .filter(c => c.region === filterRegion.value)
    .sort((a, b) => a.name_uz.localeCompare(b.name_uz))
})

function cityName(city) {
  return lang.value === 'ru' ? (city.name_ru || city.name_uz) : (city.name_uz || city.name_ru)
}

function cityDisplayName(city) {
  const type = city.type === 'shahar' ? t('post.type_shahar') : t('post.type_tuman')
  return `${cityName(city)} ${type}`
}

const locPickerItems = computed(() => {
  if (locPickerType.value === 'region') {
    return regions.value.map(r => ({ value: r, label: r }))
  }
  return filteredCities.value.map(c => ({
    value: cityName(c),
    label: cityName(c),
    badge: c.type === 'shahar' ? t('post.type_shahar') : t('post.type_tuman'),
    badgeClass: c.type === 'shahar' ? 'badge-shahar' : 'badge-tuman',
  }))
})

const locPickerFiltered = computed(() => {
  const q = locSearch.value.toLowerCase()
  if (!q) return locPickerItems.value
  return locPickerItems.value.filter(i => i.label.toLowerCase().includes(q))
})

function isLocActive(item) {
  if (locPickerType.value === 'region') return filterRegion.value === item.value
  return filters.value.city === item.value
}

function openLocPicker(type) {
  locSearch.value = ''
  locPickerType.value = type
}

function onLocSelect(item) {
  telegram.hapticFeedback('soft')
  if (locPickerType.value === 'region') {
    filterRegion.value = item.value
    filters.value.city = ''
    locPickerType.value = null
  } else {
    filters.value.city = item.value
    locPickerType.value = null
  }
}

function clearLocation() {
  telegram.hapticFeedback('soft')
  filterRegion.value = ''
  filters.value.city = ''
}

const workTypeOptions = computed(() => [
  { value: 'full_time', label: t('work_type.full_time') },
  { value: 'part_time', label: t('work_type.part_time') },
  { value: 'remote', label: t('work_type.remote') },
  { value: 'temporary', label: t('work_type.temporary') },
])

const activeFiltersCount = computed(() => {
  let count = Object.values(filters.value).filter(v => v !== '' && v !== null).length
  if (filterRegion.value) count++
  return count
})

const hasMore = computed(() => {
  return vacancyStore.pagination.current_page < vacancyStore.pagination.last_page
})

let searchTimeout = null

onMounted(async () => {
  await referenceStore.loadAll()

  if (route.query.from === 'map') {
    // Vacancies already set by MapView — just display them
  } else if (route.query.nearby === 'true') {
    await loadNearby()
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

function buildSearchParams() {
  const params = {
    q: searchQuery.value,
    ...filters.value,
  }
  // Region-only filter: send all city names in that region (plain names to match DB)
  if (filterRegion.value && !filters.value.city) {
    params.city = filteredCities.value.map(c => cityName(c))
  }
  return params
}

async function performSearch() {
  await vacancyStore.searchVacancies(buildSearchParams())
}

function clearSearch() {
  searchQuery.value = ''
  performSearch()
}

// Auto-apply: watch all filter changes and search with debounce
let filterTimeout = null
function debouncedFilterSearch() {
  clearTimeout(filterTimeout)
  filterTimeout = setTimeout(() => performSearch(), 350)
}

watch(filters, debouncedFilterSearch, { deep: true })
watch(filterRegion, debouncedFilterSearch)

function resetFilters() {
  filters.value = {
    category: '',
    city: '',
    work_type: '',
    salary_min: null,
    salary_max: null,
  }
  filterRegion.value = ''
  vacancyStore.resetFilters()
}

function searchNearby() {
  telegram.hapticFeedback('medium')
  router.push('/map')
}

async function loadNearby() {
  try {
    const coords = await getLocation()
    await vacancyStore.nearbyVacancies(coords.lat, coords.lng, 10)
  } catch (error) {
    telegram.showAlert(t('search.geo_denied'))
  }
}

async function loadMore() {
  const nextPage = vacancyStore.pagination.current_page + 1
  const params = buildSearchParams()
  params.page = nextPage
  await vacancyStore.fetchVacancies(params, { append: true })
}
</script>

<style scoped>
.search-header {
  background-color: var(--tg-theme-bg-color);
  border-bottom: 1px solid var(--separator-color, rgba(128,128,128,0.06));
}

.search-input-wrap {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 14px;
  border-radius: 12px;
  background-color: var(--tg-theme-secondary-bg-color);
  transition: box-shadow 0.2s;
}
.search-input-wrap:focus-within {
  box-shadow: 0 0 0 2px rgba(var(--tg-button-rgb, 13,148,136), 0.2);
}

/* Filter Chips */
.filter-chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 7px 14px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  white-space: nowrap;
  background-color: var(--tg-theme-secondary-bg-color);
  color: var(--tg-theme-text-color);
  transition: all 0.15s;
  border: 1px solid transparent;
}
.filter-chip:active { transform: scale(0.96); }
.filter-chip-active {
  background-color: rgba(var(--tg-button-rgb, 13,148,136), 0.12);
  color: var(--tg-theme-button-color);
  border-color: rgba(var(--tg-button-rgb, 13,148,136), 0.2);
}
.filter-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 18px;
  height: 18px;
  padding: 0 5px;
  border-radius: 9px;
  font-size: 10px;
  font-weight: 700;
  background-color: var(--tg-theme-button-color);
  color: var(--tg-theme-button-text-color);
}

/* Filters Panel */
.filters-panel { padding-top: 6px; }
.filters-card {
  padding: 12px;
  border-radius: 14px;
  background-color: var(--tg-theme-secondary-bg-color);
}

/* 2-column row */
.filter-row-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
  margin-bottom: 10px;
}
.filter-col { min-width: 0; }

.filter-group-compact { margin-bottom: 10px; }

.filter-label {
  display: block;
  font-size: 9px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.4px;
  margin-bottom: 5px;
  color: var(--tg-theme-hint-color);
}

/* Select wrapper with chevron */
.select-wrap {
  position: relative;
}
.filter-select {
  width: 100%;
  padding: 8px 28px 8px 10px;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 500;
  background-color: var(--tg-theme-bg-color);
  color: var(--tg-theme-text-color);
  border: 1.5px solid var(--separator-color, rgba(128,128,128,0.12));
  outline: none;
  -webkit-appearance: none;
  appearance: none;
}
.filter-select:focus {
  border-color: rgba(var(--tg-button-rgb, 13,148,136), 0.35);
}
.select-chevron {
  position: absolute;
  right: 8px;
  top: 50%;
  transform: translateY(-50%);
  width: 14px;
  height: 14px;
  color: var(--tg-theme-hint-color);
  opacity: 0.4;
  pointer-events: none;
}

/* Work Type Chips */
.wt-chips {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}
.wt-chip {
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 600;
  border: 1.5px solid var(--separator-color, rgba(128,128,128,0.12));
  background-color: var(--tg-theme-bg-color);
  color: var(--tg-theme-hint-color);
  transition: all 0.15s;
  white-space: nowrap;
}
.wt-chip:active { transform: scale(0.95); }
.wt-chip-on {
  border-color: rgba(var(--tg-button-rgb, 13,148,136), 0.35);
  background-color: rgba(var(--tg-button-rgb, 13,148,136), 0.08);
  color: var(--tg-theme-button-color);
}

/* Salary Row */
.salary-row {
  display: flex;
  align-items: center;
  gap: 6px;
}
.salary-dash {
  font-size: 14px;
  color: var(--tg-theme-hint-color);
  opacity: 0.4;
  flex-shrink: 0;
}
.filter-input {
  flex: 1;
  min-width: 0;
  padding: 8px 10px;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 500;
  background-color: var(--tg-theme-bg-color);
  color: var(--tg-theme-text-color);
  border: 1.5px solid var(--separator-color, rgba(128,128,128,0.12));
  outline: none;
}
.filter-input:focus {
  border-color: rgba(var(--tg-button-rgb, 13,148,136), 0.35);
}
.filter-input::placeholder {
  color: var(--tg-theme-hint-color);
  opacity: 0.5;
}

/* Reset Button */
.filter-reset-btn {
  width: 100%;
  padding: 10px;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 600;
  background-color: var(--tg-theme-bg-color);
  color: var(--tg-theme-hint-color);
  border: 1.5px solid var(--separator-color, rgba(128,128,128,0.12));
  transition: transform 0.15s;
}
.filter-reset-btn:active { transform: scale(0.97); }

/* Picker Trigger */
.picker-trigger {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px 10px;
  border-radius: 10px;
  background-color: var(--tg-theme-bg-color);
  border: 1.5px solid var(--separator-color, rgba(128,128,128,0.12));
  cursor: pointer;
  -webkit-tap-highlight-color: transparent;
  transition: border-color 0.15s;
}
.picker-trigger:active { border-color: var(--tg-theme-button-color); }
.picker-trigger-off { opacity: 0.4; pointer-events: none; }
.pv { font-size: 13px; font-weight: 600; color: var(--tg-theme-text-color); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.pp { font-size: 13px; font-weight: 500; color: var(--tg-theme-hint-color); opacity: 0.5; }
.pchev { width: 14px; height: 14px; color: var(--tg-theme-hint-color); opacity: 0.4; flex-shrink: 0; }

/* Location clear tag */
.loc-clear {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  margin-top: 6px;
  padding: 4px 8px 4px 6px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 600;
  background-color: rgba(99,102,241,0.1);
  color: #6366f1;
}
.loc-clear:active { opacity: 0.7; }

/* Bottom Sheet */
.sheet-backdrop {
  position: fixed;
  inset: 0;
  z-index: 200;
  background: rgba(0,0,0,0.45);
  display: flex;
  align-items: flex-end;
}
.sheet-panel {
  width: 100%;
  max-height: 72vh;
  background-color: var(--tg-theme-bg-color);
  border-radius: 18px 18px 0 0;
  display: flex;
  flex-direction: column;
  padding-bottom: max(12px, env(safe-area-inset-bottom));
}
.sheet-handle {
  width: 36px; height: 4px; border-radius: 2px;
  background: rgba(128,128,128,0.25);
  margin: 10px auto 0;
}
.sheet-title {
  padding: 14px 20px 8px;
  font-size: 17px; font-weight: 700;
  color: var(--tg-theme-text-color);
}
.sheet-search { position: relative; padding: 0 16px 8px; }
.sheet-search-icon {
  position: absolute; left: 28px; top: 50%;
  transform: translateY(calc(-50% - 4px));
  width: 16px; height: 16px;
  color: var(--tg-theme-hint-color); opacity: 0.5;
}
.sheet-search-input {
  width: 100%; padding: 10px 14px 10px 36px;
  border-radius: 10px;
  background-color: var(--tg-theme-secondary-bg-color);
  border: none; outline: none;
  font-size: 14px; font-weight: 500;
  color: var(--tg-theme-text-color);
}
.sheet-search-input::placeholder { color: var(--tg-theme-hint-color); opacity: 0.5; }
.sheet-options { flex: 1; overflow-y: auto; -webkit-overflow-scrolling: touch; }
.sheet-option {
  display: flex; align-items: center; justify-content: space-between;
  gap: 12px; padding: 13px 20px; cursor: pointer; transition: background 0.1s;
}
.sheet-option:active { background-color: rgba(128,128,128,0.06); }
.sheet-option-content { display: flex; align-items: center; gap: 8px; min-width: 0; flex: 1; }
.sheet-option-label { font-size: 15px; font-weight: 500; color: var(--tg-theme-text-color); }
.sheet-option-active .sheet-option-label { color: var(--tg-theme-button-color); font-weight: 600; }
.sheet-option-badge {
  font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 5px; flex-shrink: 0;
}
.badge-shahar { background-color: rgba(13,148,136,0.1); color: #0D9488; }
.badge-tuman { background-color: rgba(245,158,11,0.1); color: #f59e0b; }
.sheet-option-check { width: 18px; height: 18px; color: var(--tg-theme-button-color); flex-shrink: 0; }
.sheet-empty { padding: 32px 20px; text-align: center; font-size: 14px; color: var(--tg-theme-hint-color); opacity: 0.6; }

/* Sheet Transitions */
.sheet-enter-active { transition: opacity 0.2s ease-out; }
.sheet-enter-active .sheet-panel { transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
.sheet-leave-active { transition: opacity 0.15s ease-in; }
.sheet-leave-active .sheet-panel { transition: transform 0.2s ease-in; }
.sheet-enter-from, .sheet-leave-to { opacity: 0; }
.sheet-enter-from .sheet-panel, .sheet-leave-to .sheet-panel { transform: translateY(100%); }

/* Load More */
.load-more-btn {
  padding: 10px 28px;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 600;
  background-color: var(--tg-theme-secondary-bg-color);
  color: var(--tg-theme-text-color);
  transition: transform 0.15s;
}
.load-more-btn:active { transform: scale(0.97); }
.load-more-btn:disabled { opacity: 0.5; }

/* Slide Transition */
.slide-enter-active,
.slide-leave-active {
  transition: all 0.25s ease;
  max-height: 400px;
  overflow: hidden;
}
.slide-enter-from,
.slide-leave-to {
  max-height: 0;
  opacity: 0;
  padding-top: 0;
}

.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
.no-scrollbar::-webkit-scrollbar { display: none; }
</style>
