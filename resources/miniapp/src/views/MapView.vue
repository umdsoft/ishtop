<template>
  <div class="map-view">
    <!-- Header -->
    <div class="sticky top-0 z-20 px-4 py-3 flex items-center justify-between" style="background-color: var(--tg-theme-bg-color); border-bottom: 1px solid var(--separator-color);">
      <h1 class="text-lg font-semibold" style="color: var(--tg-theme-text-color);">{{ t('map.title') }}</h1>
      <button
        @click="toggleFilters"
        class="w-9 h-9 rounded-xl flex items-center justify-center active:scale-95 transition-transform"
        :style="showFilters
          ? { backgroundColor: 'var(--tg-theme-button-color)', color: 'var(--tg-theme-button-text-color)' }
          : { backgroundColor: 'var(--tg-theme-secondary-bg-color)', color: 'var(--tg-theme-text-color)' }"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" />
        </svg>
      </button>
    </div>

    <!-- Filters Panel -->
    <transition name="slide">
      <div v-if="showFilters" class="z-10 px-4 pt-2 pb-4" style="background-color: var(--tg-theme-bg-color);">
        <div class="rounded-2xl p-4 space-y-4" style="background-color: var(--tg-theme-secondary-bg-color);">
          <!-- Radius -->
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wide mb-2" style="color: var(--tg-theme-hint-color);">
              {{ t('map.radius') }}: {{ filters.radius }} km
            </label>
            <input
              v-model.number="filters.radius"
              type="range"
              min="1"
              max="50"
              step="1"
              class="range-input w-full"
            />
          </div>

          <!-- Category -->
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wide mb-1.5" style="color: var(--tg-theme-hint-color);">
              {{ t('map.category') }}
            </label>
            <select v-model="filters.category" class="input">
              <option value="">{{ t('map.allCategories') }}</option>
              <option v-for="cat in categories" :key="cat.slug" :value="cat.slug">
                {{ cat.name_uz }}
              </option>
            </select>
          </div>

          <!-- Apply -->
          <button class="btn-primary w-full" @click="applyFilters">
            {{ t('map.apply') }}
          </button>
        </div>
      </div>
    </transition>

    <!-- Map Container -->
    <div ref="mapContainer" class="map-container"></div>

    <!-- Vacancy Card (shown when marker clicked) -->
    <transition name="card-slide">
      <div v-if="selectedVacancy" class="vacancy-card">
        <div class="p-4">
          <!-- Drag indicator -->
          <div class="w-10 h-1 rounded-full mx-auto mb-3" style="background-color: var(--separator-color);"></div>

          <div class="flex justify-between items-start mb-2">
            <h3 class="font-semibold text-[15px] flex-1 pr-2" style="color: var(--tg-theme-text-color);">
              {{ selectedVacancy.title_uz || selectedVacancy.title_ru || selectedVacancy.title }}
            </h3>
            <button
              @click="selectedVacancy = null"
              class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0"
              style="background-color: var(--tg-theme-secondary-bg-color); color: var(--tg-theme-hint-color);"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="space-y-1.5 mb-3">
            <div class="flex items-center text-[13px]" style="color: var(--tg-theme-hint-color);">
              <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
              </svg>
              {{ selectedVacancy.employer?.company_name }}
            </div>

            <div class="flex items-center text-[13px]" style="color: var(--tg-theme-hint-color);">
              <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
              </svg>
              {{ selectedVacancy.city }}
              <span v-if="selectedVacancy.distance"> &bull; {{ selectedVacancy.distance }} km</span>
            </div>

            <div v-if="selectedVacancy.salary_min || selectedVacancy.salary_max || selectedVacancy.formatted_salary" class="flex items-center text-[14px] font-semibold" style="color: var(--tg-theme-button-color);">
              <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
              </svg>
              {{ selectedVacancy.formatted_salary || formatSalary(selectedVacancy) }}
            </div>
          </div>

          <button
            @click="viewDetails(selectedVacancy.id)"
            class="btn-primary w-full"
          >
            {{ t('map.viewDetails') }}
          </button>
        </div>
      </div>
    </transition>

    <!-- Loading Overlay -->
    <div v-if="loading" class="loading-overlay">
      <LoadingSpinner />
    </div>

    <!-- Error / No Location -->
    <div v-if="locationError" class="error-overlay">
      <div class="text-center p-6">
        <svg class="w-12 h-12 mx-auto mb-3" style="color: var(--tg-theme-hint-color);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
          <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
        </svg>
        <p class="font-medium mb-1" style="color: var(--tg-theme-text-color);">{{ t('search.geo_denied') }}</p>
        <p class="text-sm" style="color: var(--tg-theme-hint-color);">Geolokatsiyani yoqing va qayta urinib ko'ring</p>
        <button class="btn-primary mt-4" @click="retryLocation">Qayta urinish</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useVacancyStore } from '@/stores/vacancy'
import { useGeolocation } from '@/composables/useGeolocation'
import { useTelegram } from '@/composables/useTelegram'
import { useLocale } from '@/composables/useLocale'
import LoadingSpinner from '@/components/LoadingSpinner.vue'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import api from '@/utils/api'

const router = useRouter()
const vacancyStore = useVacancyStore()
const { getLocation } = useGeolocation()
const telegram = useTelegram()
const { t } = useLocale()

const mapContainer = ref(null)
let leafletMap = null
let markersLayer = null
const selectedVacancy = ref(null)
const loading = ref(true)
const locationError = ref(false)
const showFilters = ref(false)
const userLocation = ref(null)
const categories = ref([])

const filters = ref({
  radius: 10,
  category: '',
})

// Custom marker icon
const vacancyIcon = L.divIcon({
  className: 'vacancy-marker',
  html: '<div class="marker-pin"></div>',
  iconSize: [30, 40],
  iconAnchor: [15, 40],
  popupAnchor: [0, -40],
})

const userIcon = L.divIcon({
  className: 'user-marker',
  html: '<div class="user-pin"></div>',
  iconSize: [16, 16],
  iconAnchor: [8, 8],
})

onMounted(async () => {
  // Load categories
  try {
    const catRes = await api.get('/categories')
    categories.value = catRes.data.categories || catRes.data || []
  } catch (e) {
    console.error('Failed to load categories:', e)
  }

  await initMapWithLocation()
})

onUnmounted(() => {
  if (leafletMap) {
    leafletMap.remove()
    leafletMap = null
  }
})

async function initMapWithLocation() {
  loading.value = true
  locationError.value = false

  try {
    const coords = await getLocation()
    userLocation.value = coords
    initMap(coords.lat, coords.lng)
    await loadVacancies()
  } catch (error) {
    console.error('Location error:', error)
    // Show map at default location (Tashkent) without user marker
    initMap(41.2995, 69.2401)
    locationError.value = true
  } finally {
    loading.value = false
  }
}

function initMap(lat, lng) {
  if (leafletMap) {
    leafletMap.remove()
  }

  leafletMap = L.map(mapContainer.value, {
    center: [lat, lng],
    zoom: 13,
    zoomControl: false,
    attributionControl: false,
  })

  // OpenStreetMap tiles
  L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
  }).addTo(leafletMap)

  // Zoom control (right side)
  L.control.zoom({ position: 'bottomright' }).addTo(leafletMap)

  // Add user location marker
  if (userLocation.value) {
    L.marker([lat, lng], { icon: userIcon })
      .addTo(leafletMap)

    // Add radius circle
    L.circle([lat, lng], {
      radius: filters.value.radius * 1000,
      color: 'var(--tg-theme-button-color)',
      fillColor: 'rgba(59, 130, 246, 0.1)',
      fillOpacity: 0.3,
      weight: 1.5,
      dashArray: '6, 6',
    }).addTo(leafletMap)
  }

  // Create markers layer group
  markersLayer = L.layerGroup().addTo(leafletMap)
}

async function loadVacancies() {
  if (!userLocation.value) return

  try {
    await vacancyStore.nearbyVacancies(
      userLocation.value.lat,
      userLocation.value.lng,
      filters.value.radius
    )
    updateMarkers()
  } catch (error) {
    console.error('Error loading vacancies:', error)
  }
}

function updateMarkers() {
  if (!markersLayer) return

  markersLayer.clearLayers()

  const vacancies = vacancyStore.vacancies.filter(v => v.latitude && v.longitude)

  vacancies.forEach(vacancy => {
    const marker = L.marker([vacancy.latitude, vacancy.longitude], {
      icon: vacancyIcon,
    })

    marker.on('click', () => {
      selectedVacancy.value = vacancy
      telegram.hapticFeedback('medium')
    })

    markersLayer.addLayer(marker)
  })

  // Fit bounds if we have markers
  if (vacancies.length > 0 && userLocation.value) {
    const allPoints = vacancies.map(v => [v.latitude, v.longitude])
    allPoints.push([userLocation.value.lat, userLocation.value.lng])
    leafletMap.fitBounds(allPoints, { padding: [40, 40] })
  }
}

function toggleFilters() {
  showFilters.value = !showFilters.value
  telegram.hapticFeedback('light')
}

async function applyFilters() {
  showFilters.value = false
  loading.value = true
  telegram.hapticFeedback('medium')

  try {
    // Update radius circle
    if (leafletMap && userLocation.value) {
      leafletMap.eachLayer(layer => {
        if (layer instanceof L.Circle) {
          leafletMap.removeLayer(layer)
        }
      })
      L.circle([userLocation.value.lat, userLocation.value.lng], {
        radius: filters.value.radius * 1000,
        color: 'var(--tg-theme-button-color)',
        fillColor: 'rgba(59, 130, 246, 0.1)',
        fillOpacity: 0.3,
        weight: 1.5,
        dashArray: '6, 6',
      }).addTo(leafletMap)
    }

    await loadVacancies()
  } finally {
    loading.value = false
  }
}

function viewDetails(vacancyId) {
  router.push(`/vacancies/${vacancyId}`)
}

function formatSalary(vacancy) {
  const format = (n) => new Intl.NumberFormat('uz-UZ').format(n)
  if (vacancy.salary_min && vacancy.salary_max) {
    return `${format(vacancy.salary_min)} - ${format(vacancy.salary_max)} ${t('common.som')}`
  } else if (vacancy.salary_min) {
    return `${format(vacancy.salary_min)} ${t('common.som_from')}`
  } else if (vacancy.salary_max) {
    return `${format(vacancy.salary_max)} ${t('common.som_to')}`
  }
  return t('common.negotiable')
}

async function retryLocation() {
  await initMapWithLocation()
}
</script>

<style scoped>
.map-view {
  display: flex;
  flex-direction: column;
  height: 100vh;
  height: 100dvh;
  position: relative;
  background-color: var(--tg-theme-bg-color);
}

.map-container {
  flex: 1;
  width: 100%;
  z-index: 1;
}

/* Custom vacancy marker */
:deep(.vacancy-marker) {
  background: none;
  border: none;
}

:deep(.marker-pin) {
  width: 30px;
  height: 30px;
  border-radius: 50% 50% 50% 0;
  background-color: var(--tg-theme-button-color, #3b82f6);
  transform: rotate(-45deg);
  position: relative;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

:deep(.marker-pin::after) {
  content: '';
  width: 14px;
  height: 14px;
  border-radius: 50%;
  background: white;
  position: absolute;
  top: 8px;
  left: 8px;
}

/* User location marker */
:deep(.user-marker) {
  background: none;
  border: none;
}

:deep(.user-pin) {
  width: 16px;
  height: 16px;
  border-radius: 50%;
  background-color: #3b82f6;
  border: 3px solid white;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.3), 0 2px 4px rgba(0, 0, 0, 0.2);
  animation: pulse-ring 2s ease-out infinite;
}

@keyframes pulse-ring {
  0% { box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.3), 0 2px 4px rgba(0, 0, 0, 0.2); }
  50% { box-shadow: 0 0 0 8px rgba(59, 130, 246, 0.1), 0 2px 4px rgba(0, 0, 0, 0.2); }
  100% { box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.3), 0 2px 4px rgba(0, 0, 0, 0.2); }
}

/* Vacancy card overlay */
.vacancy-card {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 15;
  background-color: var(--tg-theme-bg-color);
  border-top-left-radius: 20px;
  border-top-right-radius: 20px;
  box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.12);
}

/* Card slide animation */
.card-slide-enter-active,
.card-slide-leave-active {
  transition: transform 0.3s ease;
}
.card-slide-enter-from,
.card-slide-leave-to {
  transform: translateY(100%);
}

/* Filter slide animation */
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
  padding-bottom: 0;
}

/* Loading overlay */
.loading-overlay {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  z-index: 20;
}

/* Error overlay */
.error-overlay {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  z-index: 20;
  width: 80%;
  max-width: 300px;
  border-radius: 20px;
  background-color: var(--tg-theme-bg-color);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

/* Range input */
.range-input {
  -webkit-appearance: none;
  appearance: none;
  height: 6px;
  border-radius: 3px;
  background: var(--separator-color);
  outline: none;
}

.range-input::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 22px;
  height: 22px;
  border-radius: 50%;
  background: var(--tg-theme-button-color, #3b82f6);
  cursor: pointer;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.2);
}

.range-input::-moz-range-thumb {
  width: 22px;
  height: 22px;
  border-radius: 50%;
  background: var(--tg-theme-button-color, #3b82f6);
  cursor: pointer;
  border: none;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.2);
}

/* Hide Leaflet default attribution */
:deep(.leaflet-control-attribution) {
  display: none !important;
}
</style>
