<template>
  <div class="home-view" style="height: 100vh; display: flex; flex-direction: column;">
    <!-- Sticky Header -->
    <div class="flex-shrink-0 px-5 pt-6 pb-3" style="background-color: var(--tg-theme-bg-color); z-index: 10;">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h1 class="text-[22px] font-bold tracking-tight" style="color: var(--tg-theme-text-color);">
            {{ greeting }}
          </h1>
          <p class="text-sm mt-0.5" style="color: var(--tg-theme-hint-color);">
            {{ t('home.subtitle') }}
          </p>
        </div>
        <div
          class="w-10 h-10 rounded-full flex items-center justify-center cursor-pointer active:scale-95 transition-transform"
          style="background-color: var(--tg-theme-button-color); color: var(--tg-theme-button-text-color);"
          @click="router.push('/profile')"
        >
          <span class="text-sm font-bold">{{ userInitial }}</span>
        </div>
      </div>

      <!-- Search Bar + Resume CTA -->
      <div class="flex items-center gap-2.5 mb-3">
        <!-- Search -->
        <div
          class="flex-1 flex items-center gap-3 px-4 py-3 rounded-2xl cursor-pointer active:scale-[0.98] transition-transform"
          style="background-color: var(--tg-theme-secondary-bg-color); border: 1px solid var(--separator-color);"
          @click="router.push('/search')"
        >
          <svg class="w-5 h-5 flex-shrink-0" style="color: var(--tg-theme-hint-color);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="7" />
            <path stroke-linecap="round" d="M20 20l-4-4" />
          </svg>
          <span class="text-[15px]" style="color: var(--tg-theme-hint-color);">{{ t('home.search_placeholder') }}</span>
        </div>

        <!-- Resume button -->
        <div
          class="relative flex-shrink-0 px-3.5 py-3 rounded-2xl flex items-center gap-2 cursor-pointer active:scale-[0.96] transition-transform"
          :style="{
            backgroundColor: hasCompleteProfile
              ? 'var(--tg-theme-secondary-bg-color)'
              : 'var(--tg-theme-button-color)',
            color: hasCompleteProfile
              ? 'var(--tg-theme-hint-color)'
              : 'var(--tg-theme-button-text-color)',
            border: hasCompleteProfile ? '1px solid var(--separator-color)' : 'none',
          }"
          @click="router.push('/profile/worker/edit')"
        >
          <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
          </svg>
          <span class="text-[13px] font-semibold whitespace-nowrap">{{ hasCompleteProfile ? t('home.resume_btn') : t('home.resume_add_btn') }}</span>
          <!-- Pulsing dot when incomplete -->
          <span
            v-if="!hasCompleteProfile"
            class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 rounded-full animate-pulse"
            style="background-color: #ef4444; box-shadow: 0 0 6px rgba(239,68,68,0.5);"
          ></span>
        </div>
      </div>

      <!-- Tabs -->
      <div class="flex gap-1.5 overflow-x-auto no-scrollbar">
        <button
          v-for="tab in tabs"
          :key="tab.key"
          class="flex-shrink-0 px-3.5 py-1.5 rounded-lg text-[13px] font-medium transition-all active:scale-[0.96]"
          :style="activeTab === tab.key
            ? { backgroundColor: 'var(--tg-theme-button-color)', color: 'var(--tg-theme-button-text-color)' }
            : { backgroundColor: 'var(--tg-theme-secondary-bg-color)', color: 'var(--tg-theme-hint-color)' }"
          @click="switchTab(tab.key)"
        >
          {{ tab.label }}
          <span
            v-if="tab.count > 0"
            class="ml-1 text-[11px]"
            :style="{ opacity: activeTab === tab.key ? 0.8 : 0.6 }"
          >{{ tab.count }}</span>
        </button>
      </div>
    </div>

    <!-- Scrollable Content Area -->
    <div ref="scrollContainer" class="flex-1 overflow-y-auto pb-20" @scroll="handleScroll">
      <!-- Banner -->
      <div class="px-5 mb-3">
        <BannerSlot placement="home_top" />
      </div>

      <!-- Tab: Nearby -->
      <div v-if="activeTab === 'nearby'" class="px-5">
        <div v-if="nearbyLoading" class="space-y-2.5">
          <SkeletonCard v-for="i in 4" :key="i" />
        </div>
        <template v-else-if="nearbyVacancies.length > 0">
          <div class="flex items-center justify-between mb-3">
            <p class="text-[11px]" style="color: var(--tg-theme-hint-color);">
              {{ t('home.nearby_radius') }}
            </p>
            <RouterLink
              to="/map"
              class="text-[13px] font-medium flex items-center gap-1"
              style="color: var(--tg-theme-link-color);"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z" />
              </svg>
              {{ t('home.nearby_map') }}
            </RouterLink>
          </div>
          <div class="space-y-2.5">
            <VacancyCard
              v-for="vacancy in nearbyVacancies"
              :key="vacancy.id"
              :vacancy="vacancy"
              :distance="vacancy.distance_km"
            />
          </div>
        </template>
        <div v-else class="flex flex-col items-center justify-center py-16">
          <svg class="w-12 h-12 mb-3" style="color: var(--tg-theme-hint-color); opacity: 0.4;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
          </svg>
          <p class="text-[13px]" style="color: var(--tg-theme-hint-color);">{{ t('home.nearby_radius') }}</p>
        </div>
      </div>

      <!-- Tab: Recommended -->
      <div v-if="activeTab === 'recommended'" class="px-5">
        <div v-if="recommendedLoading" class="space-y-2.5">
          <SkeletonCard v-for="i in 4" :key="i" />
        </div>
        <!-- Profile incomplete: prompt to fill -->
        <div v-else-if="profileIncomplete" class="flex flex-col items-center justify-center py-16 px-4">
          <div
            class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4"
            style="background-color: rgba(var(--tg-button-rgb, 13,148,136), 0.1);"
          >
            <svg class="w-8 h-8" style="color: var(--tg-theme-button-color);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
            </svg>
          </div>
          <p class="text-[15px] font-semibold text-center mb-1.5" style="color: var(--tg-theme-text-color);">
            {{ t('home.profile_incomplete_title') }}
          </p>
          <p class="text-[13px] text-center mb-5" style="color: var(--tg-theme-hint-color);">
            {{ t('home.profile_incomplete_desc') }}
          </p>
          <button
            class="px-6 py-2.5 rounded-xl text-[14px] font-semibold active:scale-[0.96] transition-transform"
            style="background-color: var(--tg-theme-button-color); color: var(--tg-theme-button-text-color);"
            @click="router.push('/profile/worker/edit')"
          >
            {{ t('home.fill_profile_btn') }}
          </button>
        </div>
        <div v-else-if="enrichedRecommended.length > 0" class="space-y-2.5">
          <VacancyCard
            v-for="vacancy in enrichedRecommended"
            :key="vacancy.id"
            :vacancy="vacancy"
            :distance="vacancy._distance"
            :matchScore="vacancy.match_score"
          />
        </div>
        <div v-else class="flex flex-col items-center justify-center py-16">
          <svg class="w-12 h-12 mb-3" style="color: var(--tg-theme-hint-color); opacity: 0.4;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
          </svg>
          <p class="text-[13px]" style="color: var(--tg-theme-hint-color);">{{ t('home.no_recommendations') }}</p>
        </div>
      </div>

      <!-- Tab: Latest -->
      <div v-if="activeTab === 'latest'" class="px-5">
        <div v-if="latestLoading && latestVacancies.length === 0" class="space-y-2.5">
          <SkeletonCard v-for="i in 5" :key="i" />
        </div>
        <div v-else-if="enrichedLatest.length > 0" class="space-y-2.5">
          <VacancyCard
            v-for="vacancy in enrichedLatest"
            :key="vacancy.id"
            :vacancy="vacancy"
            :distance="vacancy._distance"
            :matchScore="vacancy._matchScore"
          />
          <!-- Infinite scroll loading indicator -->
          <div v-if="latestLoadingMore" class="flex items-center justify-center py-4">
            <LoadingSpinner />
          </div>
          <div v-else-if="!latestHasMore" class="text-center py-4">
            <p class="text-[11px]" style="color: var(--tg-theme-hint-color);">{{ t('home.no_more') }}</p>
          </div>
        </div>
        <div v-else class="flex flex-col items-center justify-center py-16">
          <svg class="w-12 h-12 mb-3" style="color: var(--tg-theme-hint-color); opacity: 0.4;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0" />
          </svg>
          <p class="text-[13px]" style="color: var(--tg-theme-hint-color);">{{ t('home.latest') }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useVacancyStore } from '@/stores/vacancy'
import { useAuthStore } from '@/stores/auth'
import { useGeolocation } from '@/composables/useGeolocation'
import { useTelegram } from '@/composables/useTelegram'
import { useLocale } from '@/composables/useLocale'
import { calculateDistance, calculateMatchPercent } from '@/utils/formatters'
import api from '@/utils/api'
import BannerSlot from '@/components/BannerSlot.vue'
import VacancyCard from '@/components/VacancyCard.vue'
import LoadingSpinner from '@/components/LoadingSpinner.vue'
import SkeletonCard from '@/components/SkeletonCard.vue'

const router = useRouter()
const vacancyStore = useVacancyStore()
const authStore = useAuthStore()
const { getLocation } = useGeolocation()
const telegram = useTelegram()
const { t } = useLocale()

const activeTab = ref('nearby')
const recommended = ref([])
const recommendedLoading = ref(false)
const profileIncomplete = ref(false)
const nearbyVacancies = ref([])
const nearbyLoading = ref(false)
const userCoords = ref(null)
const scrollContainer = ref(null)

// Latest tab: own pagination (15 per page)
const latestVacancies = ref([])
const latestLoading = ref(false)
const latestLoadingMore = ref(false)
const latestPage = ref(1)
const latestHasMore = ref(true)
const latestGeoParams = ref(null)

// Worker profile from auth response (for client-side match calculation)
const workerProfile = computed(() => authStore.user?.worker_profile)

const tabs = computed(() => [
  {
    key: 'nearby',
    label: t('home.tab_nearby'),
    count: nearbyVacancies.value.length,
  },
  {
    key: 'recommended',
    label: t('home.tab_recommended'),
    count: recommended.value.length,
  },
  {
    key: 'latest',
    label: t('home.tab_latest'),
    count: latestVacancies.value.length,
  },
])

// Enriched recommended: sort by match_score (highest first)
const enrichedRecommended = computed(() => {
  const items = recommended.value.map(v => ({
    ...v,
    _distance: v.distance_km != null
      ? v.distance_km
      : (userCoords.value ? calculateDistance(userCoords.value.lat, userCoords.value.lng, v.latitude, v.longitude) : null),
  }))
  // Sort by match_score desc, then distance asc (nearest first when same score)
  return items.sort((a, b) => {
    const scoreDiff = (b.match_score || 0) - (a.match_score || 0)
    if (scoreDiff !== 0) return scoreDiff
    const distA = a._distance ?? Infinity
    const distB = b._distance ?? Infinity
    return distA - distB
  })
})

// Enriched latest: add client-side distance + match score
const enrichedLatest = computed(() => {
  return latestVacancies.value.map(v => ({
    ...v,
    _distance: v.distance_km != null
      ? v.distance_km
      : (userCoords.value ? calculateDistance(userCoords.value.lat, userCoords.value.lng, v.latitude, v.longitude) : null),
    _matchScore: calculateMatchPercent(v, workerProfile.value),
  }))
})

// Resume/profile completeness check
const hasCompleteProfile = computed(() => {
  const wp = authStore.user?.worker_profile
  if (!wp) return false
  return !!(wp.full_name && wp.city)
})

const userInitial = computed(() => {
  const name = telegram.user.value?.first_name || authStore.user?.first_name || ''
  return name ? name.charAt(0).toUpperCase() : 'U'
})

const greeting = computed(() => {
  const name = telegram.user.value?.first_name || authStore.user?.first_name || ''
  const hour = new Date().getHours()
  let greet = ''
  if (hour >= 5 && hour < 12) greet = t('home.greeting_morning')
  else if (hour >= 12 && hour < 18) greet = t('home.greeting_day')
  else greet = t('home.greeting_evening')

  return name ? `${greet}, ${name}!` : `${greet}!`
})

function switchTab(key) {
  telegram.hapticFeedback('soft')
  activeTab.value = key
}

// Infinite scroll handler
function handleScroll() {
  if (activeTab.value !== 'latest' || !latestHasMore.value || latestLoadingMore.value) return
  const el = scrollContainer.value
  if (!el) return
  // Trigger when 200px from bottom
  if (el.scrollHeight - el.scrollTop - el.clientHeight < 200) {
    loadMoreLatest()
  }
}

async function loadMoreLatest() {
  if (latestLoadingMore.value || !latestHasMore.value) return
  latestLoadingMore.value = true
  try {
    latestPage.value++
    const params = { per_page: 15, page: latestPage.value, ...(latestGeoParams.value || {}) }
    const response = await api.get('/vacancies', { params })
    const newItems = response.data.data || []
    if (newItems.length === 0) {
      latestHasMore.value = false
    } else {
      latestVacancies.value = [...latestVacancies.value, ...newItems]
      if (response.data.current_page >= response.data.last_page) {
        latestHasMore.value = false
      }
    }
  } catch (e) {
    console.error('Load more failed:', e)
  } finally {
    latestLoadingMore.value = false
  }
}

async function loadLatest(geoParams = null) {
  latestLoading.value = true
  latestPage.value = 1
  latestHasMore.value = true
  latestGeoParams.value = geoParams
  try {
    const params = { per_page: 15, ...(geoParams || {}) }
    const response = await api.get('/vacancies', { params })
    latestVacancies.value = response.data.data || []
    if (response.data.current_page >= response.data.last_page) {
      latestHasMore.value = false
    }
  } catch (e) {
    console.error('Latest fetch failed:', e)
  } finally {
    latestLoading.value = false
  }
}

async function loadNearby() {
  nearbyLoading.value = true
  try {
    const coords = await getLocation()
    userCoords.value = coords
    const response = await api.get('/vacancies/nearby', {
      params: { lat: coords.lat, lng: coords.lng, radius: 30, per_page: 15 },
    })
    nearbyVacancies.value = response.data.data || []

    // Geo-koordinatlar keldi — Latest ni viloyat bo'yicha qayta yuklash (~100km radius)
    loadLatest({ lat: coords.lat, lng: coords.lng, radius: 100 })
  } catch (e) {
    console.log('Nearby vacancies unavailable:', e.message || e)
  } finally {
    nearbyLoading.value = false
  }
}

onMounted(() => {
  loadNearby()

  // Dastlab barcha vakansiyalarni yuklash (geo tayyorgacha)
  loadLatest()

  if (authStore.isAuthenticated) {
    recommendedLoading.value = true
    api.get('/vacancies/recommended')
      .then(res => {
        if (res.data.profile_incomplete) {
          profileIncomplete.value = true
          recommended.value = []
        } else {
          profileIncomplete.value = false
          recommended.value = res.data.vacancies || []
        }
      })
      .catch(e => console.error('Failed to load recommendations:', e))
      .finally(() => { recommendedLoading.value = false })
  }
})
</script>

<style scoped>
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
</style>
