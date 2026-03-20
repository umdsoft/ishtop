<template>
  <div class="pb-20" style="background-color: var(--tg-theme-bg-color);">
    <!-- Header -->
    <div class="px-5 pt-6 pb-4 flex items-center justify-between">
      <h1 class="text-[22px] font-bold tracking-tight" style="color: var(--tg-theme-text-color);">
        {{ t('my_vacancies.title') }}
      </h1>
      <button
        class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-[13px] font-semibold active:scale-[0.96] transition-transform"
        style="background-color: var(--tg-theme-button-color); color: var(--tg-theme-button-text-color);"
        @click="addVacancy"
      >
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        {{ t('my_vacancies.add_btn') }}
      </button>
    </div>

    <!-- Filter Tabs -->
    <div class="px-5 mb-4">
      <div class="flex gap-1.5 overflow-x-auto no-scrollbar">
        <button
          v-for="tab in tabs"
          :key="tab.value"
          class="flex-shrink-0 px-3.5 py-1.5 rounded-lg text-[13px] font-medium transition-all active:scale-[0.96]"
          :style="activeTab === tab.value
            ? { backgroundColor: 'var(--tg-theme-button-color)', color: 'var(--tg-theme-button-text-color)' }
            : { backgroundColor: 'var(--tg-theme-secondary-bg-color)', color: 'var(--tg-theme-hint-color)' }"
          @click="switchTab(tab.value)"
        >
          {{ tab.label }}
          <span
            v-if="tab.count > 0"
            class="ml-1 text-[11px]"
            :style="{ opacity: activeTab === tab.value ? 0.8 : 0.6 }"
          >{{ tab.count }}</span>
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex items-center justify-center py-20">
      <LoadingSpinner />
    </div>

    <!-- Empty State -->
    <div v-else-if="filteredVacancies.length === 0" class="flex flex-col items-center justify-center py-16 px-6">
      <div
        class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4"
        style="background-color: var(--tg-theme-secondary-bg-color);"
      >
        <svg class="w-8 h-8" style="color: var(--tg-theme-hint-color);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0M12 12.75h.008v.008H12v-.008z" />
        </svg>
      </div>
      <p class="text-[16px] font-semibold mb-1" style="color: var(--tg-theme-text-color);">{{ t('my_vacancies.empty_title') }}</p>
      <p class="text-[13px] text-center mb-5" style="color: var(--tg-theme-hint-color);">{{ t('my_vacancies.empty_hint') }}</p>
      <button
        class="px-6 py-2.5 rounded-xl text-[14px] font-semibold active:scale-[0.97] transition-transform"
        style="background-color: var(--tg-theme-button-color); color: var(--tg-theme-button-text-color);"
        @click="addVacancy"
      >
        {{ t('my_vacancies.add_first') }}
      </button>
    </div>

    <!-- Vacancies List -->
    <div v-else class="px-5 space-y-2">
      <div
        v-for="vacancy in filteredVacancies"
        :key="vacancy.id"
        class="vacancy-card rounded-xl overflow-hidden cursor-pointer active:scale-[0.98] transition-transform"
        style="background-color: var(--tg-theme-secondary-bg-color);"
        @click="viewVacancy(vacancy)"
      >
        <!-- Status bar top accent -->
        <div class="h-[2px]" :style="{ backgroundColor: getStatusColor(vacancy.status) }"></div>

        <div class="px-3.5 py-3">
          <!-- Row 1: Title + Status -->
          <div class="flex items-center justify-between gap-2 mb-1.5">
            <h3 class="text-[14px] font-semibold leading-tight flex-1 min-w-0 truncate" style="color: var(--tg-theme-text-color);">
              {{ lang === 'ru' ? (vacancy.title_ru || vacancy.title_uz) : (vacancy.title_uz || vacancy.title_ru) }}
            </h3>
            <span
              class="flex-shrink-0 px-2 py-0.5 rounded text-[10px] font-bold uppercase"
              :style="getStatusStyle(vacancy.status)"
            >
              {{ getStatusLabel(vacancy.status) }}
            </span>
          </div>

          <!-- Row 2: Category + Salary inline -->
          <div class="flex items-center gap-1.5 text-[12px] mb-2">
            <span v-if="getCategoryName(vacancy.category)" style="color: var(--tg-theme-hint-color);">{{ getCategoryName(vacancy.category) }}</span>
            <span v-if="getCategoryName(vacancy.category) && (vacancy.salary_min || vacancy.salary_max)" style="color: var(--tg-theme-hint-color);">·</span>
            <span v-if="vacancy.salary_min || vacancy.salary_max" class="font-semibold" style="color: var(--tg-theme-button-color);">
              {{ formatSalary(vacancy) }} {{ t('common.som') }}
            </span>
          </div>

          <!-- Row 3: Meta + Stats inline -->
          <div class="flex items-center justify-between text-[12px]" style="color: var(--tg-theme-hint-color);">
            <div class="flex items-center gap-2.5">
              <span v-if="vacancy.city" class="flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                </svg>
                {{ formatLocationShort(vacancy.city, vacancy.district) }}
              </span>
              <span v-if="vacancy.work_type">
                {{ getWorkTypeLabel(vacancy.work_type) }}
              </span>
              <span class="flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                </svg>
                {{ vacancy.applications_count || 0 }}
              </span>
            </div>
            <span>{{ timeAgo(vacancy.created_at) }}</span>
          </div>

          <!-- Activation button for PENDING vacancies -->
          <div v-if="vacancy.status === 'pending'" class="mt-2.5 pt-2.5" style="border-top: 1px solid var(--separator-color, rgba(128,128,128,0.12));">
            <div class="flex items-center justify-between">
              <div class="text-[12px]" style="color: var(--tg-theme-hint-color);">
                <template v-if="freePostsLeft > 0">
                  <span class="font-bold" style="color: #10b981;">{{ t('my_vacancies.free') }}</span>
                  <span class="ml-1">{{ t('my_vacancies.free_hint') }}</span>
                </template>
                <template v-else>
                  {{ t('my_vacancies.price_label') }} <span class="font-bold" style="color: var(--tg-theme-text-color);">{{ formatPrice(pricing.vacancy) }} {{ t('common.som') }}</span>
                </template>
              </div>
              <button
                class="px-4 py-1.5 rounded-lg text-[12px] font-bold active:scale-[0.95] transition-transform"
                style="background-color: #10b981; color: #fff;"
                :disabled="activatingId === vacancy.id"
                @click.stop="activateVacancy(vacancy)"
              >
                <span v-if="activatingId === vacancy.id">{{ t('my_vacancies.activating') }}</span>
                <span v-else>{{ t('my_vacancies.activate') }}</span>
              </button>
            </div>
          </div>

          <!-- Recommended Candidates for ACTIVE vacancies -->
          <div v-if="vacancy.status === 'active' && candidatesMap[vacancy.id]"
               class="mt-2.5 pt-2.5"
               style="border-top: 1px solid var(--separator-color, rgba(128,128,128,0.12));">

            <!-- Loading -->
            <div v-if="candidatesMap[vacancy.id]?.loading" class="flex items-center gap-2 py-2">
              <div class="w-3.5 h-3.5 border-2 border-t-transparent rounded-full animate-spin"
                   style="border-color: var(--tg-theme-hint-color); border-top-color: transparent;"></div>
              <span class="text-[11px]" style="color: var(--tg-theme-hint-color);">
                {{ t('my_vacancies.candidates_loading') }}
              </span>
            </div>

            <!-- Candidates list -->
            <template v-else-if="candidatesMap[vacancy.id]?.candidates?.length > 0 || candidatesMap[vacancy.id]?.total_count > 0">
              <div class="text-[12px] font-semibold mb-1.5" style="color: var(--tg-theme-text-color);">
                {{ t('my_vacancies.candidates_title') }}
              </div>

              <div class="space-y-1">
                <div v-for="candidate in candidatesMap[vacancy.id].candidates.slice(0, 3)"
                     :key="candidate.id"
                     class="flex items-center gap-2 p-2 rounded-lg cursor-pointer active:opacity-70 transition-opacity"
                     style="background-color: var(--tg-theme-bg-color);"
                     @click.stop="viewCandidate(candidate)">
                  <!-- Avatar -->
                  <div class="w-7 h-7 rounded-full flex-shrink-0 flex items-center justify-center overflow-hidden"
                       style="background-color: var(--tg-theme-secondary-bg-color);">
                    <img v-if="candidate.photo_url" :src="candidate.photo_url" class="w-full h-full object-cover" />
                    <svg v-else class="w-3.5 h-3.5" style="color: var(--tg-theme-hint-color);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0" />
                    </svg>
                  </div>
                  <!-- Info -->
                  <div class="flex-1 min-w-0">
                    <div class="text-[12px] font-medium truncate" style="color: var(--tg-theme-text-color);">
                      {{ candidate.full_name }}
                    </div>
                    <div class="text-[10px] truncate" style="color: var(--tg-theme-hint-color);">
                      {{ candidate.specialty || '' }}
                      <template v-if="candidate.experience_years"> · {{ candidate.experience_years }} {{ t('my_vacancies.candidates_exp') }}</template>
                      <template v-if="candidate.city"> · {{ formatLocationShort(candidate.city, candidate.district) }}</template>
                    </div>
                  </div>
                  <!-- Match score + chevron -->
                  <div class="flex-shrink-0 px-1.5 py-0.5 rounded text-[10px] font-bold"
                       :style="{
                         backgroundColor: candidate.match_score >= 70 ? 'rgba(16,185,129,0.12)' : candidate.match_score >= 40 ? 'rgba(245,158,11,0.12)' : 'rgba(156,163,175,0.12)',
                         color: candidate.match_score >= 70 ? '#10b981' : candidate.match_score >= 40 ? '#f59e0b' : '#9ca3af'
                       }">
                    {{ candidate.match_score }}%
                  </div>
                  <svg class="w-3.5 h-3.5 flex-shrink-0" style="color: var(--tg-theme-hint-color);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                  </svg>
                </div>
              </div>

              <!-- See all candidates inside vacancy detail -->
              <div v-if="candidatesMap[vacancy.id].candidates.length > 3 || candidatesMap[vacancy.id].total_count > 3"
                   class="mt-1.5 text-center"
                   @click.stop="viewVacancy(vacancy)">
                <button class="text-[12px] font-semibold py-1.5 px-4 rounded-lg active:opacity-70 transition-opacity"
                        style="color: var(--tg-theme-button-color);">
                  {{ t('my_vacancies.candidates_see_all').replace('{count}', candidatesMap[vacancy.id].total_count) }}
                  <svg class="w-3 h-3 inline-block ml-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                  </svg>
                </button>
              </div>
            </template>

            <!-- No candidates -->
            <div v-else class="py-1.5 text-[11px]" style="color: var(--tg-theme-hint-color);">
              {{ t('my_vacancies.candidates_empty') }}
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import { useTelegram } from '@/composables/useTelegram'
import { useLocale } from '@/composables/useLocale'
import { useAuthStore } from '@/stores/auth'
import { useReferenceStore } from '@/stores/reference'
import { formatSalary as _formatSalary, timeAgo as _timeAgo, formatNumber, formatLocationShort } from '@/utils/formatters'
import LoadingSpinner from '@/components/LoadingSpinner.vue'
import api from '@/utils/api'

const router = useRouter()
const telegram = useTelegram()
const { t, lang } = useLocale()
const authStore = useAuthStore()
const referenceStore = useReferenceStore()

const vacancies = ref([])
const loading = ref(false)
const activeTab = ref('all')
const pricing = ref({ vacancy: 35000, top: 15000, urgent: 10000 })
const freePostsLeft = ref(0)
const activatingId = ref(null)
const candidatesMap = ref({})
const pendingListeners = ref([])

onBeforeUnmount(() => {
  pendingListeners.value.forEach(fn => document.removeEventListener('visibilitychange', fn))
  pendingListeners.value = []
})

const paymentMethod = 'click'

const tabs = computed(() => {
  const counts = {
    all: vacancies.value.length,
    active: vacancies.value.filter(v => v.status === 'active').length,
    pending: vacancies.value.filter(v => v.status === 'pending').length,
    paused: vacancies.value.filter(v => v.status === 'paused' || v.status === 'draft').length,
    closed: vacancies.value.filter(v => v.status === 'closed' || v.status === 'expired').length,
  }

  return [
    { value: 'all', label: t('my_vacancies.tab_all'), count: counts.all },
    { value: 'active', label: t('my_vacancies.tab_active'), count: counts.active },
    { value: 'pending', label: t('my_vacancies.tab_pending'), count: counts.pending },
    { value: 'paused', label: t('my_vacancies.tab_paused'), count: counts.paused },
    { value: 'closed', label: t('my_vacancies.tab_closed'), count: counts.closed },
  ]
})

const filteredVacancies = computed(() => {
  if (activeTab.value === 'all') return vacancies.value
  if (activeTab.value === 'paused') return vacancies.value.filter(v => v.status === 'paused' || v.status === 'draft')
  if (activeTab.value === 'closed') return vacancies.value.filter(v => v.status === 'closed' || v.status === 'expired')
  return vacancies.value.filter(v => v.status === activeTab.value)
})

onMounted(async () => {
  await loadData()
})

async function loadData() {
  loading.value = true
  try {
    const [vacRes, , priceRes] = await Promise.all([
      api.get('/vacancies/my'),
      referenceStore.loadCategories(),
      api.get('/vacancies/pricing').catch(() => null),
    ])
    vacancies.value = vacRes.data.vacancies || []

    if (priceRes?.data?.pricing) {
      pricing.value = priceRes.data.pricing
    }
    if (priceRes?.data?.free_posts_left !== undefined) {
      freePostsLeft.value = priceRes.data.free_posts_left
    }
    // Load candidates for active vacancies
    const activeVacs = vacancies.value.filter(v => v.status === 'active')
    activeVacs.forEach(v => loadCandidates(v.id))
  } catch (error) {
    telegram.showAlert(t('my_vacancies.load_error'))
  } finally {
    loading.value = false
  }
}

async function loadCandidates(vacancyId) {
  const existing = candidatesMap.value[vacancyId]
  if (existing?.loading) return

  // Preserve existing state during reload (don't reset locked/candidates)
  candidatesMap.value[vacancyId] = existing
    ? { ...existing, loading: true }
    : { loading: true, candidates: [], total_count: 0, locked: true, unlock_price: null, unlocking: false }

  try {
    const res = await api.get(`/vacancies/${vacancyId}/candidates`)
    candidatesMap.value[vacancyId] = { ...res.data, loading: false, unlocking: false }
  } catch {
    candidatesMap.value[vacancyId] = { ...candidatesMap.value[vacancyId], loading: false }
  }
}

async function unlockCandidates(vacancy) {
  telegram.hapticFeedback('light')
  const vacancyId = vacancy.id
  candidatesMap.value[vacancyId] = { ...candidatesMap.value[vacancyId], unlocking: true }

  try {
    await api.post(`/vacancies/${vacancyId}/unlock-candidates`)
    telegram.hapticFeedback('success')

    // Mark as unlocked immediately (payment succeeded)
    candidatesMap.value[vacancyId] = { ...candidatesMap.value[vacancyId], locked: false, unlocking: false }

    // Reload full candidates list (20 instead of 3)
    loadCandidates(vacancyId)

    // Refresh balance separately (non-blocking)
    authStore.fetchUser().catch(() => {})
  } catch (error) {
    const msg = error.response?.data?.message || t('common.error')
    telegram.showAlert(msg)
    telegram.hapticFeedback('error')
    candidatesMap.value[vacancyId] = { ...candidatesMap.value[vacancyId], unlocking: false }
  }
}

function getCategoryName(slug) {
  return referenceStore.getCategoryName(slug, lang.value)
}

function switchTab(value) {
  telegram.hapticFeedback('soft')
  activeTab.value = value
}

function addVacancy() {
  telegram.hapticFeedback('light')
  router.push('/post/new')
}

function viewVacancy(vacancy) {
  telegram.hapticFeedback('soft')
  router.push(`/vacancies/${vacancy.id}`)
}

function viewCandidate(candidate) {
  telegram.hapticFeedback('soft')
  router.push({ path: `/candidates/${candidate.id}`, query: { score: candidate.match_score } })
}

function getStatusLabel(status) {
  const labels = {
    draft: t('my_vacancies.status_draft'),
    pending: t('my_vacancies.status_pending'),
    active: t('my_vacancies.status_active'),
    paused: t('my_vacancies.status_paused'),
    closed: t('my_vacancies.status_closed'),
    expired: t('my_vacancies.status_expired'),
  }
  return labels[status] || status
}

function getStatusColor(status) {
  const colors = {
    draft: '#9ca3af',
    pending: '#f59e0b',
    active: '#10b981',
    paused: '#0D9488',
    closed: '#ef4444',
    expired: '#ef4444',
  }
  return colors[status] || '#9ca3af'
}

function getStatusStyle(status) {
  const styles = {
    draft: { backgroundColor: 'rgba(156, 163, 175, 0.12)', color: '#9ca3af' },
    pending: { backgroundColor: 'rgba(245, 158, 11, 0.12)', color: '#f59e0b' },
    active: { backgroundColor: 'rgba(16, 185, 129, 0.12)', color: '#10b981' },
    paused: { backgroundColor: 'rgba(13, 148, 136, 0.12)', color: '#0D9488' },
    closed: { backgroundColor: 'rgba(239, 68, 68, 0.12)', color: '#ef4444' },
    expired: { backgroundColor: 'rgba(239, 68, 68, 0.12)', color: '#ef4444' },
  }
  return styles[status] || { backgroundColor: 'rgba(128,128,128,0.1)', color: 'var(--tg-theme-hint-color)' }
}

function getWorkTypeLabel(type) {
  const labels = {
    full_time: t('my_vacancies.wt_full'),
    part_time: t('my_vacancies.wt_part'),
    remote: t('my_vacancies.wt_remote'),
    internship: t('my_vacancies.wt_intern'),
  }
  return labels[type] || type
}

function formatPrice(amount) {
  return formatNumber(amount) || '0'
}

async function activateVacancy(vacancy) {
  telegram.hapticFeedback('light')
  activatingId.value = vacancy.id

  try {
    const body = freePostsLeft.value > 0 ? {} : { method: paymentMethod }
    const response = await api.post(`/vacancies/${vacancy.id}/activate`, body)

    if (response.data.free) {
      // Bepul faollashtirildi
      telegram.hapticFeedback('success')
      telegram.showAlert(response.data.message || t('my_vacancies.activate_success'))
      await loadData()
    } else if (response.data.checkout_url) {
      telegram.openLink(response.data.checkout_url)
      // Re-fetch when user returns from payment
      const handleReturn = () => {
        if (!document.hidden) {
          document.removeEventListener('visibilitychange', handleReturn)
          pendingListeners.value = pendingListeners.value.filter(fn => fn !== handleReturn)
          setTimeout(() => loadData(), 2000)
        }
      }
      document.addEventListener('visibilitychange', handleReturn)
      pendingListeners.value.push(handleReturn)
    }
  } catch (error) {
    const msg = error.response?.data?.message || t('common.error')
    telegram.showAlert(msg)
    telegram.hapticFeedback('error')
  } finally {
    activatingId.value = null
  }
}

function formatSalary(vacancy) {
  return _formatSalary(vacancy, t, { short: true })
}

function timeAgo(date) {
  return _timeAgo(date, t)
}
</script>

<style scoped>
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
.no-scrollbar::-webkit-scrollbar {
  display: none;
}

.meta-chip {
  display: inline-flex;
  align-items: center;
  gap: 3px;
  padding: 3px 8px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 500;
  background-color: rgba(var(--tg-button-rgb, 13,148,136), 0.08);
  color: var(--tg-theme-hint-color);
}

.stat-item {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 12px;
  font-weight: 500;
  color: var(--tg-theme-hint-color);
}
</style>
