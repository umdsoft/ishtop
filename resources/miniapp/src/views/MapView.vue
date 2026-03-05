<template>
  <div class="map-view">
    <!-- Header -->
    <div class="sticky top-0 z-10 bg-[var(--tg-theme-bg-color)] border-b border-[var(--tg-theme-section-separator-color)]">
      <div class="px-4 py-3 flex items-center justify-between">
        <h1 class="text-lg font-semibold">{{ $t('map.title') }}</h1>
        <button
          @click="toggleFilters"
          class="btn btn-sm btn-secondary"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Filters Panel -->
    <div
      v-show="showFilters"
      class="bg-[var(--tg-theme-secondary-bg-color)] p-4 border-b border-[var(--tg-theme-section-separator-color)]"
    >
      <div class="space-y-3">
        <div>
          <label class="block text-sm font-medium mb-1">{{ $t('map.radius') }}</label>
          <input
            v-model.number="filters.radius"
            type="range"
            min="1"
            max="50"
            step="1"
            class="w-full"
          />
          <div class="text-xs text-[var(--tg-theme-hint-color)] text-center">
            {{ filters.radius }} km
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">{{ $t('map.category') }}</label>
          <select
            v-model="filters.category"
            class="input w-full"
          >
            <option value="">{{ $t('map.allCategories') }}</option>
            <option v-for="cat in categories" :key="cat.value" :value="cat.value">
              {{ cat.label }}
            </option>
          </select>
        </div>

        <button
          @click="applyFilters"
          class="btn btn-primary w-full"
        >
          {{ $t('map.apply') }}
        </button>
      </div>
    </div>

    <!-- Map Container -->
    <div ref="mapContainer" class="map-container"></div>

    <!-- Vacancy Card (shown when marker clicked) -->
    <div
      v-if="selectedVacancy"
      class="vacancy-card"
    >
      <div class="p-4">
        <div class="flex justify-between items-start mb-2">
          <h3 class="font-semibold">{{ selectedVacancy.title }}</h3>
          <button
            @click="selectedVacancy = null"
            class="text-[var(--tg-theme-hint-color)] hover:text-[var(--tg-theme-text-color)]"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="text-sm space-y-1 mb-3">
          <div class="flex items-center text-[var(--tg-theme-hint-color)]">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            {{ selectedVacancy.employer.company_name }}
          </div>

          <div class="flex items-center text-[var(--tg-theme-hint-color)]">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            {{ selectedVacancy.city }} • {{ selectedVacancy.distance }}km
          </div>

          <div v-if="selectedVacancy.formatted_salary" class="flex items-center font-semibold text-[var(--tg-theme-link-color)]">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ selectedVacancy.formatted_salary }}
          </div>
        </div>

        <div class="flex gap-2">
          <button
            @click="viewDetails(selectedVacancy.id)"
            class="btn btn-primary flex-1"
          >
            {{ $t('map.viewDetails') }}
          </button>
          <button
            @click="buildRoute(selectedVacancy)"
            class="btn btn-secondary"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Loading -->
    <LoadingSpinner v-if="loading" />
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useVacancyStore } from '@/stores/vacancy'
import { useGeolocation } from '@/composables/useGeolocation'
import { useTelegram } from '@/composables/useTelegram'
import LoadingSpinner from '@/components/LoadingSpinner.vue'

const router = useRouter()
const vacancyStore = useVacancyStore()
const { getCurrentPosition } = useGeolocation()
const { hapticFeedback } = useTelegram()

const mapContainer = ref(null)
const map = ref(null)
const objectManager = ref(null)
const userPlacemark = ref(null)
const selectedVacancy = ref(null)
const loading = ref(true)
const showFilters = ref(false)

const filters = ref({
  radius: 10,
  category: '',
})

const categories = computed(() => [
  { value: 'IT', label: 'IT' },
  { value: 'sales', label: 'Savdo' },
  { value: 'driver', label: 'Haydovchi' },
  { value: 'construction', label: 'Qurilish' },
  { value: 'restaurant', label: 'Restoran' },
  { value: 'education', label: 'Ta\'lim' },
  // Add more categories
])

const userLocation = ref(null)

onMounted(async () => {
  try {
    // Get user location
    userLocation.value = await getCurrentPosition()

    // Load Yandex Maps API
    await loadYandexMaps()

    // Initialize map
    initMap()

    // Load nearby vacancies
    await loadVacancies()
  } catch (error) {
    console.error('Map initialization error:', error)
  } finally {
    loading.value = false
  }
})

onUnmounted(() => {
  if (map.value) {
    map.value.destroy()
  }
})

function loadYandexMaps() {
  return new Promise((resolve, reject) => {
    if (window.ymaps) {
      resolve()
      return
    }

    const script = document.createElement('script')
    script.src = `https://api-maps.yandex.ru/2.1/?apikey=${import.meta.env.VITE_YANDEX_MAPS_KEY}&lang=ru_RU`
    script.async = true
    script.onload = () => {
      window.ymaps.ready(() => resolve())
    }
    script.onerror = reject
    document.head.appendChild(script)
  })
}

function initMap() {
  const { ymaps } = window

  map.value = new ymaps.Map(mapContainer.value, {
    center: [userLocation.value.latitude, userLocation.value.longitude],
    zoom: 13,
    controls: ['zoomControl', 'geolocationControl'],
  })

  // Add user location marker
  userPlacemark.value = new ymaps.Placemark(
    [userLocation.value.latitude, userLocation.value.longitude],
    {
      hintContent: 'Sizning manzilingiz',
    },
    {
      preset: 'islands#blueDotIcon',
    }
  )

  map.value.geoObjects.add(userPlacemark.value)

  // Initialize ObjectManager for vacancy markers
  objectManager.value = new ymaps.ObjectManager({
    clusterize: true,
    gridSize: 64,
    clusterDisableClickZoom: false,
  })

  // Customize cluster appearance
  objectManager.value.clusters.options.set({
    preset: 'islands#greenClusterIcons',
  })

  // Handle marker click
  objectManager.value.objects.events.add('click', (e) => {
    const objectId = e.get('objectId')
    const vacancy = vacancyStore.vacancies.find(v => v.id === objectId)
    if (vacancy) {
      selectedVacancy.value = vacancy
      hapticFeedback('medium')
    }
  })

  map.value.geoObjects.add(objectManager.value)
}

async function loadVacancies() {
  if (!userLocation.value) return

  try {
    const params = {
      lat: userLocation.value.latitude,
      lon: userLocation.value.longitude,
      radius: filters.value.radius,
    }

    if (filters.value.category) {
      params.category = filters.value.category
    }

    await vacancyStore.searchNearby(params)

    // Add vacancy markers to map
    updateMarkers()
  } catch (error) {
    console.error('Error loading vacancies:', error)
  }
}

function updateMarkers() {
  if (!objectManager.value) return

  const { ymaps } = window

  // Clear existing markers
  objectManager.value.removeAll()

  // Create GeoJSON collection
  const features = vacancyStore.vacancies
    .filter(v => v.latitude && v.longitude)
    .map(vacancy => ({
      type: 'Feature',
      id: vacancy.id,
      geometry: {
        type: 'Point',
        coordinates: [vacancy.latitude, vacancy.longitude],
      },
      properties: {
        hintContent: vacancy.title,
        balloonContentHeader: `<strong>${vacancy.title}</strong>`,
        balloonContentBody: `
          <div style="padding: 8px;">
            <p>${vacancy.employer?.company_name || ''}</p>
            <p style="color: #007aff; font-weight: bold;">${vacancy.formatted_salary || 'Kelishiladi'}</p>
          </div>
        `,
      },
      options: {
        preset: 'islands#greenDotIcon',
      },
    }))

  objectManager.value.add({
    type: 'FeatureCollection',
    features,
  })
}

function toggleFilters() {
  showFilters.value = !showFilters.value
  hapticFeedback('light')
}

async function applyFilters() {
  showFilters.value = false
  loading.value = true
  hapticFeedback('medium')

  try {
    await loadVacancies()
  } finally {
    loading.value = false
  }
}

function viewDetails(vacancyId) {
  router.push(`/vacancy/${vacancyId}`)
}

function buildRoute(vacancy) {
  const { ymaps } = window

  if (!userLocation.value || !vacancy.latitude || !vacancy.longitude) return

  // Create route using Yandex Maps routing
  const multiRoute = new ymaps.multiRouter.MultiRoute(
    {
      referencePoints: [
        [userLocation.value.latitude, userLocation.value.longitude],
        [vacancy.latitude, vacancy.longitude],
      ],
      params: {
        routingMode: 'auto',
      },
    },
    {
      boundsAutoApply: true,
      wayPointStartIconColor: '#1E88E5',
      wayPointFinishIconColor: '#43A047',
    }
  )

  map.value.geoObjects.add(multiRoute)

  hapticFeedback('medium')
}
</script>

<style scoped>
.map-view {
  display: flex;
  flex-direction: column;
  height: 100vh;
  background-color: var(--tg-theme-bg-color);
}

.map-container {
  flex: 1;
  width: 100%;
  position: relative;
}

.vacancy-card {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background-color: var(--tg-theme-bg-color);
  border-top-left-radius: 16px;
  border-top-right-radius: 16px;
  box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.1);
  animation: slideUp 0.3s ease-out;
}

@keyframes slideUp {
  from {
    transform: translateY(100%);
  }
  to {
    transform: translateY(0);
  }
}

.btn {
  padding: 8px 16px;
  border-radius: 8px;
  font-weight: 500;
  transition: all 0.2s;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.btn-primary {
  background-color: var(--tg-theme-button-color);
  color: var(--tg-theme-button-text-color);
}

.btn-secondary {
  background-color: var(--tg-theme-secondary-bg-color);
  color: var(--tg-theme-text-color);
}

.btn-sm {
  padding: 6px 12px;
  font-size: 0.875rem;
}

.input {
  padding: 8px 12px;
  border-radius: 8px;
  border: 1px solid var(--tg-theme-section-separator-color);
  background-color: var(--tg-theme-bg-color);
  color: var(--tg-theme-text-color);
  font-size: 14px;
}

.input:focus {
  outline: none;
  border-color: var(--tg-theme-button-color);
}

input[type="range"] {
  -webkit-appearance: none;
  appearance: none;
  height: 6px;
  border-radius: 3px;
  background: var(--tg-theme-section-separator-color);
  outline: none;
}

input[type="range"]::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: var(--tg-theme-button-color);
  cursor: pointer;
}

input[type="range"]::-moz-range-thumb {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: var(--tg-theme-button-color);
  cursor: pointer;
  border: none;
}
</style>
