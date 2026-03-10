<template>
  <div class="pb-20" style="background-color: var(--tg-theme-bg-color);">
    <!-- Header -->
    <div class="px-5 pt-6 pb-4">
      <h1 class="text-[22px] font-bold tracking-tight" style="color: var(--tg-theme-text-color);">
        {{ t('apps.title') }}
      </h1>
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
    <div v-else-if="filteredApplications.length === 0" class="flex flex-col items-center justify-center py-16 px-6">
      <div
        class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4"
        style="background-color: var(--tg-theme-secondary-bg-color);"
      >
        <svg class="w-8 h-8" style="color: var(--tg-theme-hint-color);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
        </svg>
      </div>
      <p class="text-[16px] font-semibold mb-1" style="color: var(--tg-theme-text-color);">{{ t('apps.empty_title') }}</p>
      <p class="text-[13px] text-center mb-5" style="color: var(--tg-theme-hint-color);">{{ t('apps.empty_hint') }}</p>
      <button
        class="px-6 py-2.5 rounded-xl text-[14px] font-semibold active:scale-[0.97] transition-transform"
        style="background-color: var(--tg-theme-button-color); color: var(--tg-theme-button-text-color);"
        @click="router.push('/search')"
      >
        {{ t('apps.search_btn') }}
      </button>
    </div>

    <!-- Applications List -->
    <div v-else class="px-5 space-y-2.5">
      <div
        v-for="app in filteredApplications"
        :key="app.id"
        class="rounded-xl p-3.5 cursor-pointer active:scale-[0.98] transition-transform"
        style="background-color: var(--tg-theme-secondary-bg-color);"
        @click="viewApplication(app)"
      >
        <!-- Top row: Company + Stage badge -->
        <div class="flex items-center justify-between mb-2.5">
          <div class="flex items-center gap-2.5 flex-1 min-w-0">
            <img
              v-if="app.vacancy?.employer?.logo_url"
              :src="app.vacancy.employer.logo_url"
              class="w-10 h-10 rounded-lg object-cover flex-shrink-0"
            />
            <div
              v-else
              class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0"
              style="background-color: rgba(var(--tg-button-rgb, 13,148,136), 0.1);"
            >
              <span class="text-sm font-bold" style="color: var(--tg-theme-button-color);">
                {{ getInitial(app.vacancy?.employer?.company_name) }}
              </span>
            </div>
            <div class="flex-1 min-w-0">
              <h3 class="text-[14px] font-semibold leading-tight truncate" style="color: var(--tg-theme-text-color);">
                {{ app.vacancy?.title }}
              </h3>
              <p class="text-[12px] truncate" style="color: var(--tg-theme-hint-color);">
                {{ app.vacancy?.employer?.company_name || app.vacancy?.category }}
              </p>
            </div>
          </div>
          <span
            class="flex-shrink-0 ml-2 px-2.5 py-1 rounded-md text-[11px] font-semibold"
            :style="getStageStyle(app.stage)"
          >
            {{ getStageLabel(app.stage) }}
          </span>
        </div>

        <!-- Info row: salary + city -->
        <div class="flex items-center gap-2 text-[12px] mb-2" style="color: var(--tg-theme-hint-color);">
          <span v-if="app.vacancy?.salary_min || app.vacancy?.salary_max" class="font-semibold" style="color: var(--tg-theme-button-color);">
            {{ formatSalary(app.vacancy) }}
          </span>
          <span v-if="app.vacancy?.city && (app.vacancy?.salary_min || app.vacancy?.salary_max)">·</span>
          <span v-if="app.vacancy?.city" class="flex items-center gap-0.5">
            <svg class="w-3 h-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
            </svg>
            {{ app.vacancy.city }}
          </span>
        </div>

        <!-- Scores row -->
        <div v-if="app.questionnaire_score || app.matching_score" class="flex items-center gap-3 mb-2">
          <div v-if="app.questionnaire_score" class="flex items-center gap-1">
            <svg class="w-3.5 h-3.5" style="color: var(--tg-theme-button-color);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
            </svg>
            <span class="text-[12px] font-medium" style="color: var(--tg-theme-text-color);">{{ app.questionnaire_score }}%</span>
            <span class="text-[11px]" style="color: var(--tg-theme-hint-color);">{{ t('apps.score') }}</span>
          </div>
          <div v-if="app.matching_score" class="flex items-center gap-1">
            <svg class="w-3.5 h-3.5" style="color: #10b981;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-[12px] font-medium" style="color: var(--tg-theme-text-color);">{{ app.matching_score }}%</span>
            <span class="text-[11px]" style="color: var(--tg-theme-hint-color);">{{ t('apps.match') }}</span>
          </div>
        </div>

        <!-- Footer: Date + Viewed -->
        <div class="flex items-center justify-between text-[11px]" style="color: var(--tg-theme-hint-color);">
          <span>{{ t('apps.submitted') }} {{ formatDate(app.created_at) }}</span>
          <span v-if="app.viewed_at" class="flex items-center gap-0.5">
            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
            </svg>
            {{ t('apps.viewed') }}
          </span>
        </div>

        <!-- Rejection reason -->
        <div
          v-if="app.stage === 'rejected' && app.rejected_reason"
          class="mt-2.5 px-3 py-2 rounded-lg text-[12px]"
          style="background-color: rgba(239, 68, 68, 0.08); color: #ef4444;"
        >
          {{ app.rejected_reason }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useTelegram } from '@/composables/useTelegram'
import { useLocale } from '@/composables/useLocale'
import { formatSalary as _formatSalary, formatDate as _formatDate, getInitial } from '@/utils/formatters'
import LoadingSpinner from '@/components/LoadingSpinner.vue'
import api from '@/utils/api'

const router = useRouter()
const telegram = useTelegram()
const { t } = useLocale()

const applications = ref([])
const loading = ref(false)
const activeTab = ref('all')

const tabs = computed(() => {
  const counts = {
    all: applications.value.length,
    new: applications.value.filter(a => a.stage === 'new').length,
    in_progress: applications.value.filter(a => ['reviewed', 'shortlisted', 'interview'].includes(a.stage)).length,
    offered: applications.value.filter(a => a.stage === 'offered').length,
    hired: applications.value.filter(a => a.stage === 'hired').length,
    rejected: applications.value.filter(a => a.stage === 'rejected').length,
  }

  return [
    { value: 'all', label: t('apps.tab_all'), count: counts.all },
    { value: 'new', label: t('apps.tab_new'), count: counts.new },
    { value: 'in_progress', label: t('apps.tab_in_progress'), count: counts.in_progress },
    { value: 'offered', label: t('apps.tab_offered'), count: counts.offered },
    { value: 'hired', label: t('apps.tab_hired'), count: counts.hired },
    { value: 'rejected', label: t('apps.tab_rejected'), count: counts.rejected },
  ]
})

const filteredApplications = computed(() => {
  if (activeTab.value === 'all') {
    return applications.value
  } else if (activeTab.value === 'in_progress') {
    return applications.value.filter(a => ['reviewed', 'shortlisted', 'interview'].includes(a.stage))
  } else {
    return applications.value.filter(a => a.stage === activeTab.value)
  }
})

onMounted(async () => {
  await loadApplications()
})

async function loadApplications() {
  loading.value = true
  try {
    const response = await api.get('/applications/my')
    applications.value = response.data.data || response.data.applications || []
  } catch (error) {
    telegram.showAlert(t('apps.load_error'))
  } finally {
    loading.value = false
  }
}

function switchTab(value) {
  telegram.hapticFeedback('soft')
  activeTab.value = value
}

function viewApplication(app) {
  telegram.hapticFeedback('soft')
  router.push(`/vacancies/${app.vacancy_id}`)
}

function getStageLabel(stage) {
  const key = `apps.stage_${stage}`
  const label = t(key)
  return label !== key ? label : stage
}

function getStageStyle(stage) {
  const styles = {
    new: { backgroundColor: 'rgba(13, 148, 136, 0.12)', color: '#0D9488' },
    reviewed: { backgroundColor: 'rgba(139, 92, 246, 0.12)', color: '#8b5cf6' },
    shortlisted: { backgroundColor: 'rgba(245, 158, 11, 0.12)', color: '#f59e0b' },
    interview: { backgroundColor: 'rgba(249, 115, 22, 0.12)', color: '#f97316' },
    offered: { backgroundColor: 'rgba(16, 185, 129, 0.12)', color: '#10b981' },
    hired: { backgroundColor: 'rgba(16, 185, 129, 0.15)', color: '#059669' },
    rejected: { backgroundColor: 'rgba(239, 68, 68, 0.12)', color: '#ef4444' },
  }
  return styles[stage] || { backgroundColor: 'rgba(128,128,128,0.1)', color: 'var(--tg-theme-hint-color)' }
}

function formatSalary(vacancy) {
  return _formatSalary(vacancy, t, { short: true })
}

function formatDate(date) {
  return _formatDate(date, { short: true })
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
</style>
