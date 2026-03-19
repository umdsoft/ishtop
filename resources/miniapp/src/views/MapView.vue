<template>
  <div class="map-view">
    <!-- Header -->
    <div class="sticky top-0 z-20 px-4 py-3 flex items-center justify-between" style="background-color: var(--tg-theme-bg-color); border-bottom: 1px solid var(--separator-color);">
      <h1 class="text-lg font-semibold" style="color: var(--tg-theme-text-color);">{{ t('map.title') }}</h1>
      <button
        @click="toggleFilters"
        class="filter-toggle-btn"
        :class="{ 'filter-toggle-active': showFilters || selectedCategories.length }"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" />
        </svg>
        <span v-if="selectedCategories.length" class="filter-badge">{{ selectedCategories.length }}</span>
      </button>
    </div>

    <!-- Filters Panel -->
    <transition name="slide">
      <div v-if="showFilters" class="filters-panel">
        <div class="mx-4 mb-3 filters-card">
          <!-- Category -->
          <div class="filter-group-compact">
            <label class="filter-label">{{ t('map.category') }}</label>
            <div class="cat-picker-trigger" @click="showCatPicker = true">
              <span :class="selectedCategories.length ? 'pv' : 'pp'">
                {{ selectedCategories.length ? selectedCategories.length + ' ' + t('map.selected') : t('map.allCategories') }}
              </span>
              <svg class="pchev" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
              </svg>
            </div>
            <!-- Selected category chips -->
            <div v-if="selectedCategories.length" class="cat-chips">
              <span
                v-for="slug in selectedCategories"
                :key="slug"
                class="cat-chip"
                @click="toggleCategory(slug)"
              >
                {{ getCatName(slug) }}
                <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </span>
            </div>
          </div>

          <!-- Radius -->
          <div class="filter-group-compact">
            <label class="filter-label">{{ t('map.radius') }}</label>
            <div class="radius-chips">
              <button
                v-for="r in [5, 10, 25, 50]"
                :key="r"
                type="button"
                class="radius-chip"
                :class="{ 'radius-chip-on': filters.radius === r }"
                @click="filters.radius = r"
              >{{ r }} km</button>
            </div>
          </div>

          <!-- Buttons row -->
          <div class="filter-btns-row">
            <button v-if="selectedCategories.length" class="filter-reset-btn" @click="resetFilters">
              {{ t('common.reset') }}
            </button>
            <button class="filter-apply-btn" @click="applyFilters">
              {{ t('map.apply') }}
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- Category Picker Bottom Sheet -->
    <transition name="sheet">
      <div v-if="showCatPicker" class="sheet-backdrop" @click="showCatPicker = false">
        <div class="sheet-panel" @click.stop>
          <div class="sheet-handle"></div>
          <div class="sheet-title">{{ t('map.category') }}</div>

          <!-- Search -->
          <div class="sheet-search">
            <svg class="sheet-search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
            </svg>
            <input v-model="catSearch" class="sheet-search-input" :placeholder="t('search.placeholder')" />
          </div>

          <!-- Category List -->
          <div class="sheet-options">
            <template v-for="cat in filteredCategories" :key="cat.slug">
              <!-- Parent category -->
              <div class="sheet-cat-parent">
                <div class="sheet-option" @click="toggleCategory(cat.slug)">
                  <div class="sheet-option-content">
                    <div
                      class="cat-checkbox"
                      :class="{ 'cat-checkbox-on': isCatSelected(cat.slug), 'cat-checkbox-partial': isCatPartial(cat) }"
                    >
                      <svg v-if="isCatSelected(cat.slug)" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                      </svg>
                      <div v-else-if="isCatPartial(cat)" class="cat-checkbox-dash"></div>
                    </div>
                    <span class="sheet-option-label">{{ lang === 'ru' ? (cat.name_ru || cat.name_uz) : (cat.name_uz || cat.name_ru) }}</span>
                    <span v-if="cat.children?.length" class="cat-count">{{ cat.children.length }}</span>
                  </div>
                  <button
                    v-if="cat.children?.length"
                    class="cat-expand-btn"
                    @click.stop="toggleExpand(cat.slug)"
                  >
                    <svg
                      class="w-4 h-4 transition-transform"
                      :class="{ 'rotate-180': expandedCats.has(cat.slug) }"
                      fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"
                    >
                      <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                  </button>
                </div>

                <!-- Subcategories -->
                <div v-if="cat.children?.length && expandedCats.has(cat.slug)" class="sheet-subcats">
                  <div
                    v-for="sub in cat.children"
                    :key="sub.slug"
                    class="sheet-option sheet-option-sub"
                    @click="toggleCategory(sub.slug)"
                  >
                    <div class="sheet-option-content">
                      <div class="cat-checkbox" :class="{ 'cat-checkbox-on': isCatSelected(sub.slug) }">
                        <svg v-if="isCatSelected(sub.slug)" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                      </div>
                      <span class="sheet-option-label">{{ lang === 'ru' ? (sub.name_ru || sub.name_uz) : (sub.name_uz || sub.name_ru) }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </template>

            <div v-if="filteredCategories.length === 0" class="sheet-empty">
              {{ t('search.no_results') }}
            </div>
          </div>

          <!-- Bottom actions -->
          <div class="sheet-actions">
            <button v-if="selectedCategories.length" class="sheet-clear-btn" @click="selectedCategories = []">
              {{ t('common.reset') }}
            </button>
            <button class="sheet-done-btn" @click="showCatPicker = false">
              {{ selectedCategories.length ? `${t('map.choose')} (${selectedCategories.length})` : t('map.allCategories') }}
            </button>
          </div>
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
              {{ localized(selectedVacancy, 'title') || selectedVacancy.title }}
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
              {{ selectedVacancy.district || selectedVacancy.city }}
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

    <!-- My Location Button -->
    <button
      v-if="!loading && userLocation"
      class="my-location-btn"
      @click="goToMyLocation"
    >
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 100-18 9 9 0 000 18z" />
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15a3 3 0 100-6 3 3 0 000 6z" />
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2m0 14v2M3 12h2m14 0h2" />
      </svg>
    </button>

    <!-- Tap Hint -->
    <div v-if="!loading && showTapHint && !selectedVacancy && !userLocation" class="tap-hint">
      <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
      </svg>
      {{ t('map.tap_hint') }}
    </div>

    <!-- Nearby List Button -->
    <button
      v-if="!loading && isNearbySearch && mapVacancies.length > 0 && !selectedVacancy"
      class="nearby-list-btn"
      @click="goToNearbyList"
    >
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
      </svg>
      {{ t('map.show_list') }} ({{ mapVacancies.length }})
    </button>

    <!-- Loading Overlay -->
    <div v-if="loading" class="loading-overlay">
      <LoadingSpinner />
    </div>

    <!-- No vacancies toast -->
    <div v-if="!loading && mapVacancies.length === 0 && !showFilters" class="empty-toast">
      <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
      </svg>
      {{ t('map.no_vacancies') }}
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useReferenceStore } from '@/stores/reference'
import { useGeolocation } from '@/composables/useGeolocation'
import { useTelegram } from '@/composables/useTelegram'
import { useLocale } from '@/composables/useLocale'
import { formatSalary as _formatSalary } from '@/utils/formatters'
import { useVacancyStore } from '@/stores/vacancy'
import api from '@/utils/api'
import LoadingSpinner from '@/components/LoadingSpinner.vue'

const router = useRouter()
const vacancyStore = useVacancyStore()
const referenceStore = useReferenceStore()
const { getLocation } = useGeolocation()
const telegram = useTelegram()
const { t, lang } = useLocale()

const mapContainer = ref(null)
let L = null
let leafletMap = null
let markersLayer = null
let userIcon = null
let customPinIcon = null
let customPinMarker = null
const selectedVacancy = ref(null)
const loading = ref(true)
const showFilters = ref(false)
const showTapHint = ref(true)
const userLocation = ref(null)
const mapVacancies = ref([])
const isNearbySearch = ref(false)
const selectedCategories = ref([])
const showCatPicker = ref(false)
const catSearch = ref('')
const expandedCats = ref(new Set())
const currentZoom = ref(6)

const filters = ref({
  radius: 10,
})

const filteredCategories = computed(() => {
  const cats = referenceStore.categories || []
  if (!catSearch.value) return cats
  const q = catSearch.value.toLowerCase()
  return cats.filter(cat => {
    const parentMatch = cat.name_uz?.toLowerCase().includes(q) || cat.name_ru?.toLowerCase().includes(q)
    const childMatch = cat.children?.some(c => c.name_uz?.toLowerCase().includes(q) || c.name_ru?.toLowerCase().includes(q))
    return parentMatch || childMatch
  })
})

function toggleCategory(slug) {
  const idx = selectedCategories.value.indexOf(slug)
  if (idx >= 0) {
    selectedCategories.value.splice(idx, 1)
  } else {
    selectedCategories.value.push(slug)
  }
  telegram.hapticFeedback('light')
}

function isCatSelected(slug) {
  return selectedCategories.value.includes(slug)
}

function isCatPartial(cat) {
  if (!cat.children?.length) return false
  const selected = cat.children.filter(c => selectedCategories.value.includes(c.slug))
  return selected.length > 0 && selected.length < cat.children.length && !selectedCategories.value.includes(cat.slug)
}

function toggleExpand(slug) {
  if (expandedCats.value.has(slug)) {
    expandedCats.value.delete(slug)
  } else {
    expandedCats.value.add(slug)
  }
  expandedCats.value = new Set(expandedCats.value)
}

function localized(obj, field) {
  const ru = obj?.[`${field}_ru`]
  const uz = obj?.[`${field}_uz`]
  return lang.value === 'ru' ? (ru || uz) : (uz || ru)
}

function getCatName(slug) {
  const nameField = lang.value === 'ru' ? 'name_ru' : 'name_uz'
  for (const cat of referenceStore.categories || []) {
    if (cat.slug === slug) return cat[nameField] || cat.name_uz
    for (const sub of cat.children || []) {
      if (sub.slug === slug) return sub[nameField] || sub.name_uz
    }
  }
  return slug
}

// Compact salary for map markers (e.g. "1.5M", "2-3M")
function compactSalary(vacancy) {
  const fmt = (n) => {
    if (n >= 1000000) return (n / 1000000).toFixed(n % 1000000 === 0 ? 0 : 1) + 'M'
    if (n >= 1000) return (n / 1000).toFixed(0) + 'K'
    return String(n)
  }
  if (vacancy.salary_min && vacancy.salary_max) {
    if (vacancy.salary_min === vacancy.salary_max) return fmt(vacancy.salary_min)
    return `${fmt(vacancy.salary_min)}-${fmt(vacancy.salary_max)}`
  }
  if (vacancy.salary_min) return fmt(vacancy.salary_min) + '+'
  if (vacancy.salary_max) return fmt(vacancy.salary_max)
  return null
}

// When parent category is selected, expand to include all children slugs
// e.g. selecting "sales" also sends "sales-shop", "sales-consultant", etc.
function expandCategorySlugs(slugs) {
  if (!slugs.length) return []
  const expanded = new Set(slugs)
  const cats = referenceStore.categories || []
  for (const slug of slugs) {
    const parent = cats.find(c => c.slug === slug)
    if (parent?.children?.length) {
      parent.children.forEach(child => expanded.add(child.slug))
    }
  }
  return [...expanded]
}

async function loadLeaflet() {
  if (L) return
  const leaflet = await import('leaflet')
  await import('leaflet/dist/leaflet.css')
  L = leaflet.default

  // markercluster extends L directly after import
  await import('leaflet.markercluster')
  await import('leaflet.markercluster/dist/MarkerCluster.css')
  await import('leaflet.markercluster/dist/MarkerCluster.Default.css')

  userIcon = L.divIcon({
    className: 'user-marker',
    html: '<div class="user-pin"></div>',
    iconSize: [16, 16],
    iconAnchor: [8, 8],
  })

  customPinIcon = L.divIcon({
    className: 'custom-pin-marker',
    html: '<div style="width:34px;height:34px;border-radius:50% 50% 50% 0;background:#ef4444;transform:rotate(-45deg);position:relative;box-shadow:0 2px 10px rgba(239,68,68,0.4)"><div style="width:16px;height:16px;border-radius:50%;background:#fff;position:absolute;top:9px;left:9px"></div></div>',
    iconSize: [34, 44],
    iconAnchor: [17, 44],
  })
}

onMounted(async () => {
  await Promise.all([
    referenceStore.loadCategories(),
    loadLeaflet(),
  ])

  await initMap()
})

onUnmounted(() => {
  if (leafletMap) {
    leafletMap.remove()
    leafletMap = null
  }
})

async function initMap() {
  loading.value = true

  // Try to get user location (non-blocking)
  try {
    const coords = await getLocation()
    userLocation.value = coords
  } catch (error) {
    console.log('Location unavailable, showing all vacancies')
  }

  // Init map centered on user or default (Tashkent)
  const centerLat = userLocation.value?.lat ?? 41.2995
  const centerLng = userLocation.value?.lng ?? 69.2401
  setupMap(centerLat, centerLng)

  // Doim barcha vakansiyalarni yuklash (xaritada hamma viloyatlar ko'rinsin)
  await loadVacancies()
  loading.value = false
}

function createClusterIcon(cluster) {
  const count = cluster.getChildCount()
  let cls = 'map-pin'
  let sz = [32, 42]
  if (count >= 50) {
    cls += ' map-pin-lg'
    sz = [44, 54]
  } else if (count >= 10) {
    cls += ' map-pin-md'
    sz = [38, 48]
  }
  return L.divIcon({
    html: `<div class="${cls}"><span>${count}</span></div>`,
    className: 'vacancy-marker',
    iconSize: L.point(sz[0], sz[1]),
    iconAnchor: L.point(sz[0] / 2, sz[1]),
  })
}

function setupMap(lat, lng) {
  if (leafletMap) {
    leafletMap.remove()
  }

  leafletMap = L.map(mapContainer.value, {
    center: [lat, lng],
    zoom: userLocation.value ? 12 : 6,
    zoomControl: false,
    attributionControl: false,
  })

  L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
  }).addTo(leafletMap)

  L.control.zoom({ position: 'bottomright' }).addTo(leafletMap)

  // User location marker
  if (userLocation.value) {
    L.marker([lat, lng], { icon: userIcon }).addTo(leafletMap)
  }

  // Use MarkerClusterGroup for grouping nearby markers
  markersLayer = L.markerClusterGroup({
    maxClusterRadius: 50,
    spiderfyOnMaxZoom: true,
    showCoverageOnHover: false,
    zoomToBoundsOnClick: true,
    iconCreateFunction: createClusterIcon,
  })
  leafletMap.addLayer(markersLayer)

  // Tap on map to place custom pin
  leafletMap.on('click', onMapTap)

  // Track zoom for salary/count toggle
  currentZoom.value = leafletMap.getZoom()
  leafletMap.on('zoomend', () => {
    const newZoom = leafletMap.getZoom()
    const wasDetailed = currentZoom.value >= 12
    const isDetailed = newZoom >= 12
    currentZoom.value = newZoom
    // Re-render markers when crossing the threshold
    if (wasDetailed !== isDetailed && mapVacancies.value.length) {
      updateMarkers()
    }
  })
}

async function loadVacancies() {
  try {
    const params = { per_page: 200 }
    if (selectedCategories.value.length) {
      params.category = expandCategorySlugs(selectedCategories.value)
    }
    const response = await api.get('/vacancies', { params })
    const all = response.data.data || []
    // Keep only vacancies with coordinates
    mapVacancies.value = all.filter(v => v.latitude && v.longitude)
    updateMarkers()
  } catch (error) {
    console.error('Error loading vacancies:', error)
  }
}

function makeMarkerIcon(vacancy) {
  const zoomed = currentZoom.value >= 12
  if (zoomed) {
    const salary = compactSalary(vacancy)
    if (salary) {
      return L.divIcon({
        className: 'vacancy-marker',
        html: `<div class="map-salary-pin"><span>${salary}</span></div>`,
        iconSize: [60, 30],
        iconAnchor: [30, 30],
      })
    }
  }
  // Default: numbered pin
  return L.divIcon({
    className: 'vacancy-marker',
    html: '<div class="map-pin"><span>1</span></div>',
    iconSize: [32, 42],
    iconAnchor: [16, 42],
    popupAnchor: [0, -42],
  })
}

function updateMarkers() {
  if (!markersLayer) return
  markersLayer.clearLayers()

  mapVacancies.value.forEach(vacancy => {
    const marker = L.marker([vacancy.latitude, vacancy.longitude], {
      icon: makeMarkerIcon(vacancy),
    })
    marker.on('click', () => {
      selectedVacancy.value = vacancy
      telegram.hapticFeedback('medium')
    })
    markersLayer.addLayer(marker)
  })

  // If user location available — stay centered on user, don't zoom out to all markers
  // If no user location — fit bounds to show all markers
  if (!userLocation.value && mapVacancies.value.length > 0) {
    const allPoints = mapVacancies.value.map(v => [v.latitude, v.longitude])
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
    await loadVacancies()
  } finally {
    loading.value = false
  }
}

function resetFilters() {
  selectedCategories.value = []
  filters.value.radius = 10
  telegram.hapticFeedback('light')
  applyFilters()
}

function viewDetails(vacancyId) {
  router.push(`/vacancies/${vacancyId}`)
}

async function onMapTap(e) {
  const { lat, lng } = e.latlng
  telegram.hapticFeedback('medium')
  showTapHint.value = false
  selectedVacancy.value = null

  // Remove previous custom pin
  if (customPinMarker) {
    leafletMap.removeLayer(customPinMarker)
  }

  // Place new custom pin (draggable)
  customPinMarker = L.marker([lat, lng], {
    icon: customPinIcon,
    draggable: true,
  }).addTo(leafletMap)

  // On drag end — search nearby at new position
  customPinMarker.on('dragend', async () => {
    const pos = customPinMarker.getLatLng()
    await loadNearbyVacancies(pos.lat, pos.lng)
  })

  // Search nearby vacancies around tapped location
  await loadNearbyVacancies(lat, lng)
}

async function loadNearbyVacancies(lat, lng) {
  loading.value = true
  isNearbySearch.value = true
  try {
    const params = { lat, lng, radius: filters.value.radius, per_page: 100 }
    if (selectedCategories.value.length) {
      params.category = expandCategorySlugs(selectedCategories.value)
    }
    const response = await api.get('/vacancies/nearby', { params })
    const all = response.data.data || []
    mapVacancies.value = all.filter(v => v.latitude && v.longitude)
    updateMarkers()
    // Zoom to show results around pin (no animation to avoid timing issues)
    if (mapVacancies.value.length > 0) {
      const allPoints = mapVacancies.value.map(v => [v.latitude, v.longitude])
      allPoints.push([lat, lng])
      leafletMap.fitBounds(allPoints, { padding: [50, 50], maxZoom: 14, animate: false })
    } else {
      leafletMap.setView([lat, lng], 12, { animate: false })
    }
  } catch (error) {
    console.error('Nearby vacancies error:', error)
    leafletMap.setView([lat, lng], 12, { animate: false })
  } finally {
    loading.value = false
  }
}

async function goToMyLocation() {
  if (!leafletMap) return
  telegram.hapticFeedback('medium')

  // Remove custom pin
  if (customPinMarker) {
    leafletMap.removeLayer(customPinMarker)
    customPinMarker = null
  }

  // Reset nearby mode, reload all vacancies
  isNearbySearch.value = false
  loading.value = true
  await loadVacancies()
  loading.value = false

  if (userLocation.value) {
    leafletMap.setView([userLocation.value.lat, userLocation.value.lng], 12, { animate: true })
  }
}

function goToNearbyList() {
  telegram.hapticFeedback('medium')
  // Set loaded vacancies into vacancy store for search list view
  vacancyStore.vacancies = mapVacancies.value
  vacancyStore.pagination = {
    current_page: 1,
    last_page: 1,
    per_page: mapVacancies.value.length,
    total: mapVacancies.value.length,
  }
  router.push('/search?from=map')
}

function formatSalary(vacancy) {
  return _formatSalary(vacancy, t)
}
</script>

<style scoped>
.map-view {
  display: flex;
  flex-direction: column;
  height: calc(100vh - 60px);
  height: calc(100dvh - 60px);
  position: relative;
  background-color: var(--tg-theme-bg-color);
}

.map-container {
  flex: 1;
  width: 100%;
  z-index: 1;
}

/* Vacancy & cluster marker pins */
:deep(.vacancy-marker) {
  background: none;
  border: none;
}

:deep(.map-pin) {
  width: 32px;
  height: 32px;
  border-radius: 50% 50% 50% 0;
  background: #0D9488;
  transform: rotate(-45deg);
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}
:deep(.map-pin span) {
  transform: rotate(45deg);
  color: #fff;
  font-size: 12px;
  font-weight: 700;
  line-height: 1;
}
:deep(.map-pin-md) {
  width: 38px;
  height: 38px;
  background: #f59e0b;
}
:deep(.map-pin-md span) { font-size: 13px; }
:deep(.map-pin-lg) {
  width: 44px;
  height: 44px;
  background: #ef4444;
}
:deep(.map-pin-lg span) { font-size: 15px; }

/* Salary label pin (zoomed in) */
:deep(.map-salary-pin) {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 4px 8px;
  border-radius: 16px;
  background: #0D9488;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
  white-space: nowrap;
}
:deep(.map-salary-pin span) {
  color: #fff;
  font-size: 11px;
  font-weight: 700;
  line-height: 1;
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
  background-color: #0D9488;
  border: 3px solid white;
  box-shadow: 0 0 0 2px rgba(13, 148, 136, 0.3), 0 2px 4px rgba(0, 0, 0, 0.2);
  animation: pulse-ring 2s ease-out infinite;
}

@keyframes pulse-ring {
  0% { box-shadow: 0 0 0 2px rgba(13, 148, 136, 0.3), 0 2px 4px rgba(0, 0, 0, 0.2); }
  50% { box-shadow: 0 0 0 8px rgba(13, 148, 136, 0.1), 0 2px 4px rgba(0, 0, 0, 0.2); }
  100% { box-shadow: 0 0 0 2px rgba(13, 148, 136, 0.3), 0 2px 4px rgba(0, 0, 0, 0.2); }
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

/* Filter toggle button */
.filter-toggle-btn {
  position: relative;
  width: 36px;
  height: 36px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: var(--tg-theme-secondary-bg-color);
  color: var(--tg-theme-text-color);
  transition: all 0.15s;
}
.filter-toggle-btn:active { transform: scale(0.95); }
.filter-toggle-active {
  background-color: var(--tg-theme-button-color);
  color: var(--tg-theme-button-text-color);
}
.filter-badge {
  position: absolute;
  top: -4px;
  right: -4px;
  min-width: 18px;
  height: 18px;
  padding: 0 5px;
  border-radius: 9px;
  font-size: 10px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #ef4444;
  color: #fff;
}

/* Filters Panel */
.filters-panel {
  position: absolute;
  top: 53px;
  left: 0;
  right: 0;
  z-index: 18;
  padding-top: 6px;
}
.filters-card {
  padding: 12px;
  border-radius: 14px;
  background-color: var(--tg-theme-secondary-bg-color);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
}
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

/* Category picker trigger */
.cat-picker-trigger {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px 10px;
  border-radius: 10px;
  background-color: var(--tg-theme-bg-color);
  border: 1.5px solid var(--separator-color, rgba(128,128,128,0.12));
  cursor: pointer;
  transition: border-color 0.15s;
}
.cat-picker-trigger:active { border-color: var(--tg-theme-button-color); }
.pv { font-size: 13px; font-weight: 600; color: var(--tg-theme-text-color); }
.pp { font-size: 13px; font-weight: 500; color: var(--tg-theme-hint-color); opacity: 0.5; }
.pchev { width: 14px; height: 14px; color: var(--tg-theme-hint-color); opacity: 0.4; flex-shrink: 0; }

/* Selected category chips */
.cat-chips {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
  margin-top: 6px;
}
.cat-chip {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 8px 4px 10px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 600;
  background-color: rgba(var(--tg-button-rgb, 13,148,136), 0.1);
  color: var(--tg-theme-button-color);
  cursor: pointer;
  transition: opacity 0.1s;
}
.cat-chip:active { opacity: 0.6; }

/* Radius chips */
.radius-chips {
  display: flex;
  gap: 6px;
}
.radius-chip {
  flex: 1;
  padding: 6px 0;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 600;
  text-align: center;
  border: 1.5px solid var(--separator-color, rgba(128,128,128,0.12));
  background-color: var(--tg-theme-bg-color);
  color: var(--tg-theme-hint-color);
  transition: all 0.15s;
}
.radius-chip:active { transform: scale(0.95); }
.radius-chip-on {
  border-color: rgba(var(--tg-button-rgb, 13,148,136), 0.35);
  background-color: rgba(var(--tg-button-rgb, 13,148,136), 0.08);
  color: var(--tg-theme-button-color);
}

/* Filter buttons row */
.filter-btns-row {
  display: flex;
  gap: 8px;
}
.filter-apply-btn {
  flex: 1;
  padding: 10px;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 600;
  background-color: var(--tg-theme-button-color);
  color: var(--tg-theme-button-text-color);
  transition: transform 0.15s;
}
.filter-apply-btn:active { transform: scale(0.97); }
.filter-reset-btn {
  padding: 10px 16px;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 600;
  background-color: rgba(239, 68, 68, 0.1);
  color: #ef4444;
  transition: transform 0.15s;
}
.filter-reset-btn:active { transform: scale(0.97); }

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
}

/* Loading overlay */
.loading-overlay {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  z-index: 20;
}

/* Empty toast */
.empty-toast {
  position: absolute;
  bottom: 16px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 15;
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 10px 16px;
  border-radius: 12px;
  font-size: 13px;
  font-weight: 500;
  background-color: var(--tg-theme-bg-color);
  color: var(--tg-theme-hint-color);
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.15);
  white-space: nowrap;
}

/* Nearby list floating button */
.nearby-list-btn {
  position: absolute;
  bottom: 16px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 15;
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 12px 20px;
  border-radius: 14px;
  font-size: 14px;
  font-weight: 600;
  background-color: var(--tg-theme-button-color, #0D9488);
  color: var(--tg-theme-button-text-color, #fff);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
  white-space: nowrap;
  transition: transform 0.15s;
}
.nearby-list-btn:active {
  transform: translateX(-50%) scale(0.95);
}

/* My location button */
.my-location-btn {
  position: absolute;
  top: 70px;
  right: 12px;
  z-index: 15;
  width: 40px;
  height: 40px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: var(--tg-theme-bg-color);
  color: var(--tg-theme-button-color, #0D9488);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
  transition: transform 0.15s;
}
.my-location-btn:active {
  transform: scale(0.9);
}

/* Tap hint */
.tap-hint {
  position: absolute;
  top: 70px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 15;
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  border-radius: 10px;
  font-size: 12px;
  font-weight: 500;
  background-color: var(--tg-theme-bg-color);
  color: var(--tg-theme-hint-color);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
  white-space: nowrap;
  pointer-events: none;
}

/* Custom pin marker */
:deep(.custom-pin-marker) {
  background: none;
  border: none;
}

/* Override default markercluster styles */
:deep(.marker-cluster) {
  background: none !important;
}

:deep(.marker-cluster div) {
  background: none !important;
}

/* Hide Leaflet default attribution */
:deep(.leaflet-control-attribution) {
  display: none !important;
}

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
  max-height: 80vh;
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
  gap: 12px; padding: 11px 20px; cursor: pointer; transition: background 0.1s;
}
.sheet-option:active { background-color: rgba(128,128,128,0.06); }
.sheet-option-sub { padding-left: 44px; }
.sheet-option-content { display: flex; align-items: center; gap: 10px; min-width: 0; flex: 1; }
.sheet-option-label { font-size: 14px; font-weight: 500; color: var(--tg-theme-text-color); }
.sheet-empty { padding: 32px 20px; text-align: center; font-size: 14px; color: var(--tg-theme-hint-color); opacity: 0.6; }

/* Category checkbox */
.cat-checkbox {
  width: 20px; height: 20px; border-radius: 6px; flex-shrink: 0;
  border: 2px solid var(--separator-color, rgba(128,128,128,0.25));
  display: flex; align-items: center; justify-content: center;
  transition: all 0.15s;
}
.cat-checkbox-on {
  background-color: var(--tg-theme-button-color);
  border-color: var(--tg-theme-button-color);
  color: var(--tg-theme-button-text-color, #fff);
}
.cat-checkbox-partial {
  border-color: var(--tg-theme-button-color);
}
.cat-checkbox-dash {
  width: 10px; height: 2px; border-radius: 1px;
  background-color: var(--tg-theme-button-color);
}

/* Category count badge */
.cat-count {
  font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 5px;
  background-color: rgba(128,128,128,0.1); color: var(--tg-theme-hint-color);
  flex-shrink: 0;
}

/* Expand button */
.cat-expand-btn {
  width: 28px; height: 28px; border-radius: 8px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  color: var(--tg-theme-hint-color);
  transition: all 0.15s;
}
.cat-expand-btn:active { background-color: rgba(128,128,128,0.1); }

/* Subcategories container */
.sheet-subcats {
  border-left: 2px solid rgba(var(--tg-button-rgb, 13,148,136), 0.15);
  margin-left: 30px;
}

/* Sheet actions */
.sheet-actions {
  display: flex; gap: 8px; padding: 8px 16px 0;
  border-top: 1px solid var(--separator-color, rgba(128,128,128,0.12));
}
.sheet-clear-btn {
  padding: 10px 16px; border-radius: 10px;
  font-size: 13px; font-weight: 600;
  background-color: var(--tg-theme-secondary-bg-color);
  color: var(--tg-theme-hint-color);
}
.sheet-clear-btn:active { transform: scale(0.97); }
.sheet-done-btn {
  flex: 1; padding: 10px; border-radius: 10px;
  font-size: 13px; font-weight: 600;
  background-color: var(--tg-theme-button-color);
  color: var(--tg-theme-button-text-color);
}
.sheet-done-btn:active { transform: scale(0.97); }

/* Sheet transitions */
.sheet-enter-active { transition: opacity 0.2s ease-out; }
.sheet-enter-active .sheet-panel { transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
.sheet-leave-active { transition: opacity 0.15s ease-in; }
.sheet-leave-active .sheet-panel { transition: transform 0.2s ease-in; }
.sheet-enter-from, .sheet-leave-to { opacity: 0; }
.sheet-enter-from .sheet-panel, .sheet-leave-to .sheet-panel { transform: translateY(100%); }

.rotate-180 { transform: rotate(180deg); }
</style>
