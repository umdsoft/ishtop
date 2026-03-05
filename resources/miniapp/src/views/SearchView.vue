<template>
  <div class="search-view">
    <!-- Search Bar (Sticky) -->
    <div class="sticky top-0 bg-tg-bg z-10 p-4 border-b border-gray-200">
      <div class="input flex items-center gap-2">
        <svg class="w-5 h-5 text-tg-hint" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Kasbini yoki kompaniyani kiriting..."
          class="flex-1 outline-none bg-transparent"
          @input="debouncedSearch"
        />
        <button v-if="searchQuery" @click="clearSearch" class="text-tg-hint">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Filter Buttons -->
      <div class="flex gap-2 mt-3 overflow-x-auto">
        <button class="btn-secondary whitespace-nowrap" @click="showFilters = !showFilters">
          🎛 Filtrlar
          <span v-if="activeFiltersCount > 0" class="ml-1 bg-tg-button text-tg-button-text rounded-full px-2 text-xs">
            {{ activeFiltersCount }}
          </span>
        </button>
        <button class="btn-secondary whitespace-nowrap" @click="searchNearby">
          📍 Yaqinida
        </button>
      </div>
    </div>

    <!-- Filters Panel -->
    <div v-if="showFilters" class="p-4 bg-tg-secondary-bg border-b border-gray-200">
      <div class="space-y-3">
        <!-- Category -->
        <div>
          <label class="block text-sm font-medium mb-1">Kategoriya</label>
          <select v-model="filters.category" class="input">
            <option value="">Barchasi</option>
            <option v-for="cat in categories" :key="cat.slug" :value="cat.slug">
              {{ cat.name_uz }}
            </option>
          </select>
        </div>

        <!-- City -->
        <div>
          <label class="block text-sm font-medium mb-1">Shahar</label>
          <select v-model="filters.city" class="input">
            <option value="">Barchasi</option>
            <option v-for="city in cities" :key="city.name_uz" :value="city.name_uz">
              {{ city.name_uz }}
            </option>
          </select>
        </div>

        <!-- Work Type -->
        <div>
          <label class="block text-sm font-medium mb-1">Ish turi</label>
          <select v-model="filters.work_type" class="input">
            <option value="">Barchasi</option>
            <option value="full_time">To'liq kun</option>
            <option value="part_time">Yarim kun</option>
            <option value="remote">Masofaviy</option>
            <option value="temporary">Vaqtinchalik</option>
          </select>
        </div>

        <!-- Salary Range -->
        <div>
          <label class="block text-sm font-medium mb-1">Maosh (mln so'm)</label>
          <div class="flex gap-2">
            <input
              v-model.number="filters.salary_min"
              type="number"
              placeholder="Dan"
              class="input flex-1"
            />
            <input
              v-model.number="filters.salary_max"
              type="number"
              placeholder="Gacha"
              class="input flex-1"
            />
          </div>
        </div>

        <!-- Buttons -->
        <div class="flex gap-2">
          <button class="btn-primary flex-1" @click="applyFilters">Qo'llash</button>
          <button class="btn-secondary" @click="resetFilters">Tozalash</button>
        </div>
      </div>
    </div>

    <!-- Results -->
    <div class="p-4 pb-20">
      <!-- Results Count -->
      <div v-if="!vacancyStore.loading && vacancyStore.vacancies.length > 0" class="mb-3 text-sm text-tg-hint">
        {{ vacancyStore.pagination.total }} ta vakansiya topildi
      </div>

      <!-- Loading -->
      <LoadingSpinner v-if="vacancyStore.loading" />

      <!-- No Results -->
      <div v-else-if="vacancyStore.vacancies.length === 0" class="text-center py-12">
        <p class="text-lg mb-2">🔍</p>
        <p class="text-tg-hint">Vakansiya topilmadi</p>
      </div>

      <!-- Vacancy List -->
      <div v-else class="space-y-3">
        <VacancyCard v-for="vacancy in vacancyStore.vacancies" :key="vacancy.id" :vacancy="vacancy" />
      </div>

      <!-- Load More -->
      <div v-if="hasMore" class="mt-4 text-center">
        <button class="btn-secondary" @click="loadMore" :disabled="vacancyStore.loading">
          Ko'proq yuklash
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useVacancyStore } from '@/stores/vacancy'
import { useGeolocation } from '@/composables/useGeolocation'
import { useTelegram } from '@/composables/useTelegram'
import VacancyCard from '@/components/VacancyCard.vue'
import LoadingSpinner from '@/components/LoadingSpinner.vue'
import api from '@/utils/api'

const route = useRoute()
const vacancyStore = useVacancyStore()
const { getLocation } = useGeolocation()
const telegram = useTelegram()

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
    categories.value = catRes.data.categories || []
    cities.value = cityRes.data.cities || []
  } catch (error) {
    console.error('Failed to load filters:', error)
  }

  // Check if nearby search requested
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
    telegram.showAlert('Geolokatsiyaga ruxsat berilmadi')
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
