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
        style="background-color: rgba(var(--tg-button-rgb, 59,130,246), 0.1);"
      >
        <span class="text-sm font-bold" style="color: var(--tg-theme-button-color);">
          {{ getInitial(vacancy.employer?.company_name) }}
        </span>
      </div>

      <!-- Content -->
      <div class="flex-1 min-w-0">
        <!-- Row 1: Title + badges -->
        <div class="flex items-center gap-1.5">
          <h3 class="font-semibold text-[14px] leading-tight truncate flex-1" style="color: var(--tg-theme-text-color);">
            {{ vacancy.title_uz || vacancy.title_ru || vacancy.title }}
          </h3>
          <span v-if="vacancy.is_top" class="badge-top">TOP</span>
          <span v-if="vacancy.is_urgent" class="badge-urgent">!</span>
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
        <div class="flex items-center gap-2.5 mt-1 text-[11px]" style="color: var(--tg-theme-hint-color);">
          <span v-if="vacancy.city" class="flex items-center gap-0.5 truncate">
            <svg class="w-2.5 h-2.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
            </svg>
            {{ vacancy.city }}
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
import { useRouter } from 'vue-router'
import { useTelegram } from '@/composables/useTelegram'
import { useLocale } from '@/composables/useLocale'

const props = defineProps({
  vacancy: {
    type: Object,
    required: true,
  },
})

const router = useRouter()
const telegram = useTelegram()
const { t } = useLocale()

function handleClick() {
  telegram.hapticFeedback('soft')
  router.push(`/vacancies/${props.vacancy.id}`)
}

function getInitial(name) {
  return name ? name.charAt(0).toUpperCase() : '?'
}

function formatSalary(vacancy) {
  const fmt = (n) => new Intl.NumberFormat('uz-UZ').format(n)
  if (vacancy.salary_min && vacancy.salary_max) {
    return `${fmt(vacancy.salary_min)} - ${fmt(vacancy.salary_max)}`
  } else if (vacancy.salary_min) {
    return `${fmt(vacancy.salary_min)}+`
  } else if (vacancy.salary_max) {
    return `${fmt(vacancy.salary_max)} gacha`
  }
  return ''
}

function timeAgo(date) {
  const seconds = Math.floor((Date.now() - new Date(date).getTime()) / 1000)

  if (seconds < 60) return t('time.just_now')
  if (seconds < 3600) return `${Math.floor(seconds / 60)} ${t('time.minutes_ago')}`
  if (seconds < 86400) return `${Math.floor(seconds / 3600)} ${t('time.hours_ago')}`
  if (seconds < 604800) return `${Math.floor(seconds / 86400)} ${t('time.days_ago')}`

  return new Date(date).toLocaleDateString('uz-UZ')
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

.badge-urgent {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 16px;
  height: 16px;
  font-size: 10px;
  font-weight: 800;
  border-radius: 4px;
  background-color: rgba(239, 68, 68, 0.15);
  color: #ef4444;
  flex-shrink: 0;
}
</style>
