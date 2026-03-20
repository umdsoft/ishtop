<template>
  <div
    class="vacancy-card rounded-xl px-3.5 py-3 cursor-pointer active:scale-[0.98] transition-transform"
    style="background-color: var(--tg-theme-secondary-bg-color);"
    @click="handleClick"
  >
    <div class="flex items-center gap-3">
      <!-- Company Logo -->
      <img
        v-if="vacancy.employer?.logo_url"
        :src="vacancy.employer.logo_url"
        :alt="vacancy.employer.company_name"
        class="w-10 h-10 rounded-lg object-cover flex-shrink-0"
      />
      <div
        v-else
        class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0"
        :style="{ backgroundColor: avatarBg, color: avatarColor }"
      >
        <span class="text-sm font-bold">
          {{ getInitial(vacancy.employer?.company_name) }}
        </span>
      </div>

      <!-- Content -->
      <div class="flex-1 min-w-0">
        <!-- Row 1: Title + badges -->
        <div class="flex items-center gap-1.5">
          <h3 class="font-semibold text-[14px] leading-tight truncate flex-1" style="color: var(--tg-theme-text-color);">
            {{ vacancyTitle(vacancy) }}
          </h3>
          <span v-if="displayScore != null" class="badge-match" :style="{ backgroundColor: matchBadgeBg, color: matchColor }">
            <svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            {{ displayScore }}/10
          </span>
          <span v-else-if="vacancy.is_top" class="badge-top">TOP</span>
          <span v-if="vacancy.is_urgent" class="badge-urgent"><svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12.75 2.25c0-.41-.34-.75-.75-.75s-.75.34-.75.75c0 3.36-1.66 5.65-3.47 7.53-.6.62-1.22 1.17-1.78 1.71C4.86 12.58 4 13.5 4 15c0 4.42 3.58 8 8 8s8-3.58 8-8c0-3.26-1.56-5.12-3.13-6.57-.53-.49-1.06-.92-1.52-1.37C13.94 5.65 12.75 4.1 12.75 2.25zM12 20c-2.76 0-5-2.24-5-5 0-.76.42-1.36 1.2-2.1.5-.48 1.08-.98 1.66-1.58.82-.85 1.64-1.87 2.14-3.18.5 1.31 1.32 2.33 2.14 3.18.58.6 1.16 1.1 1.66 1.58.78.74 1.2 1.34 1.2 2.1 0 2.76-2.24 5-5 5z"/></svg> {{ t('vacancy.urgent_badge_card') }}</span>
        </div>

        <!-- Row 2: Company + salary -->
        <div class="flex items-center gap-1 mt-0.5 text-[12px]">
          <span class="truncate" style="color: var(--tg-theme-hint-color);">{{ vacancy.employer?.company_name }}</span>
          <span v-if="vacancy.salary_min || vacancy.salary_max" style="color: var(--tg-theme-hint-color);">·</span>
          <span v-if="vacancy.salary_min || vacancy.salary_max" class="flex-shrink-0 font-semibold" style="color: var(--tg-theme-button-color);">
            {{ formatSalary(vacancy) }}
          </span>
        </div>

        <!-- Row 3: Meta info -->
        <div class="flex items-center gap-2 mt-1 text-[11px]" style="color: var(--tg-theme-hint-color);">
          <span v-if="vacancy.city || props.distance != null" class="flex items-center gap-0.5 truncate">
            <svg class="w-2.5 h-2.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
            </svg>
            {{ formatLocationShort(vacancy.city, vacancy.district) }}<template v-if="props.distance != null"><span style="opacity:0.5"> · </span>{{ Math.round(props.distance * 10) / 10 }} km</template>
          </span>
          <span v-if="vacancy.work_type" class="flex items-center gap-0.5 flex-shrink-0">
            {{ t(`work_type.${vacancy.work_type}`) }}
          </span>
          <span class="flex items-center gap-0.5 flex-shrink-0 ml-auto">
            <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
            </svg>
            {{ vacancy.applications_count || 0 }}
          </span>
          <span class="flex-shrink-0">{{ timeAgo(vacancy.created_at) }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useTelegram } from '@/composables/useTelegram'
import { useLocale } from '@/composables/useLocale'
import { formatSalary as _formatSalary, timeAgo as _timeAgo, getInitial, formatLocationShort } from '@/utils/formatters'

const props = defineProps({
  vacancy: {
    type: Object,
    required: true,
  },
  distance: {
    type: Number,
    default: null,
  },
  matchScore: {
    type: Number,
    default: null,
  },
})

const router = useRouter()
const telegram = useTelegram()
const { t, lang } = useLocale()

// Avatar color palette — hash-based for consistent variety
const AVATAR_COLORS = [
  { bg: 'rgba(13, 148, 136, 0.12)', color: '#0D9488' },   // teal-brand
  { bg: 'rgba(16, 185, 129, 0.12)', color: '#10b981' },   // emerald
  { bg: 'rgba(245, 158, 11, 0.12)', color: '#f59e0b' },   // amber
  { bg: 'rgba(139, 92, 246, 0.12)', color: '#8b5cf6' },   // violet
  { bg: 'rgba(236, 72, 153, 0.12)', color: '#ec4899' },   // pink
  { bg: 'rgba(239, 68, 68, 0.12)', color: '#ef4444' },    // red
  { bg: 'rgba(20, 184, 166, 0.12)', color: '#14b8a6' },   // teal
  { bg: 'rgba(249, 115, 22, 0.12)', color: '#f97316' },   // orange
]

const avatarIndex = computed(() => {
  const name = props.vacancy.employer?.company_name || ''
  let hash = 0
  for (let i = 0; i < name.length; i++) {
    hash = name.charCodeAt(i) + ((hash << 5) - hash)
  }
  return Math.abs(hash) % AVATAR_COLORS.length
})

const avatarBg = computed(() => AVATAR_COLORS[avatarIndex.value].bg)
const avatarColor = computed(() => AVATAR_COLORS[avatarIndex.value].color)

// Convert matchScore (0-100) to /10 display
const displayScore = computed(() => {
  if (props.matchScore == null) return null
  return Math.round(props.matchScore / 10)
})

const matchColor = computed(() => {
  if (displayScore.value == null) return ''
  if (displayScore.value >= 7) return '#22c55e'
  if (displayScore.value >= 5) return '#eab308'
  return '#f97316'
})

const matchBadgeBg = computed(() => {
  if (displayScore.value == null) return ''
  if (displayScore.value >= 7) return 'rgba(34, 197, 94, 0.15)'
  if (displayScore.value >= 5) return 'rgba(234, 179, 8, 0.15)'
  return 'rgba(249, 115, 22, 0.15)'
})

function vacancyTitle(v) {
  const ru = v?.title_ru
  const uz = v?.title_uz
  return lang.value === 'ru' ? (ru || uz || v?.title) : (uz || ru || v?.title)
}

function handleClick() {
  telegram.hapticFeedback('soft')
  router.push(`/vacancies/${props.vacancy.id}`)
}

function formatSalary(vacancy) {
  return _formatSalary(vacancy, t, { short: true })
}

function timeAgo(date) {
  return _timeAgo(date, t)
}
</script>

<style scoped>
.badge-top {
  display: inline-flex;
  align-items: center;
  padding: 1px 5px;
  font-size: 9px;
  font-weight: 800;
  border-radius: 4px;
  background-color: rgba(245, 158, 11, 0.15);
  color: #f59e0b;
  letter-spacing: 0.5px;
  flex-shrink: 0;
}

.badge-match {
  display: inline-flex;
  align-items: center;
  gap: 2px;
  padding: 1px 5px;
  font-size: 9px;
  font-weight: 800;
  border-radius: 4px;
  letter-spacing: 0.3px;
  flex-shrink: 0;
}

.badge-urgent {
  display: inline-flex;
  align-items: center;
  gap: 2px;
  padding: 1px 5px;
  font-size: 9px;
  font-weight: 800;
  border-radius: 4px;
  background-color: rgba(239, 68, 68, 0.15);
  color: #ef4444;
  letter-spacing: 0.3px;
  flex-shrink: 0;
}
</style>
