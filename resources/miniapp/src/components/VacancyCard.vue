<template>
  <div class="card cursor-pointer hover:shadow-md transition-shadow" @click="handleClick">
    <div class="flex items-start gap-3">
      <!-- Company Logo -->
      <img
        v-if="vacancy.employer?.logo_url"
        :src="vacancy.employer.logo_url"
        :alt="vacancy.employer.company_name"
        class="w-12 h-12 rounded-lg object-cover"
      />
      <div v-else class="w-12 h-12 rounded-lg bg-gray-200 flex items-center justify-center">
        <span class="text-xl">{{ getInitial(vacancy.employer?.company_name) }}</span>
      </div>

      <!-- Vacancy Info -->
      <div class="flex-1 min-w-0">
        <!-- Title and Badges -->
        <div class="flex items-start justify-between gap-2 mb-1">
          <h3 class="font-semibold text-base line-clamp-2 flex-1">
            {{ vacancy.title_uz || vacancy.title_ru }}
          </h3>
          <div class="flex gap-1">
            <span v-if="vacancy.is_top" class="badge bg-yellow-100 text-yellow-800">TOP</span>
            <span v-if="vacancy.is_urgent" class="badge bg-red-100 text-red-800">Tez</span>
          </div>
        </div>

        <!-- Company Name -->
        <p class="text-sm text-tg-hint mb-2">{{ vacancy.employer?.company_name }}</p>

        <!-- Salary -->
        <p v-if="vacancy.salary_min || vacancy.salary_max" class="text-sm font-medium text-green-600 mb-2">
          {{ formatSalary(vacancy) }}
        </p>

        <!-- Location and Work Type -->
        <div class="flex flex-wrap gap-2 text-xs text-tg-hint">
          <span v-if="vacancy.city">📍 {{ vacancy.city }}</span>
          <span v-if="vacancy.work_type">💼 {{ workTypeLabel(vacancy.work_type) }}</span>
          <span v-if="vacancy.experience_required">⏱ {{ experienceLabel(vacancy.experience_required) }}</span>
        </div>

        <!-- Views and Date -->
        <div class="flex items-center justify-between mt-2 text-xs text-tg-hint">
          <span>👁 {{ vacancy.views_count }} ko'rildi</span>
          <span>{{ timeAgo(vacancy.created_at) }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { useTelegram } from '@/composables/useTelegram'

const props = defineProps({
  vacancy: {
    type: Object,
    required: true,
  },
})

const router = useRouter()
const telegram = useTelegram()

function handleClick() {
  telegram.hapticFeedback('soft')
  router.push(`/vacancies/${props.vacancy.id}`)
}

function getInitial(name) {
  return name ? name.charAt(0).toUpperCase() : '?'
}

function formatSalary(vacancy) {
  if (vacancy.salary_min && vacancy.salary_max) {
    return `${formatNumber(vacancy.salary_min)} - ${formatNumber(vacancy.salary_max)} so'm`
  } else if (vacancy.salary_min) {
    return `${formatNumber(vacancy.salary_min)} so'm dan`
  } else if (vacancy.salary_max) {
    return `${formatNumber(vacancy.salary_max)} so'm gacha`
  }
  return 'Kelishuv asosida'
}

function formatNumber(num) {
  return new Intl.NumberFormat('uz-UZ').format(num)
}

function workTypeLabel(type) {
  const labels = {
    full_time: 'To\'liq kun',
    part_time: 'Yarim kun',
    remote: 'Masofaviy',
    temporary: 'Vaqtinchalik',
  }
  return labels[type] || type
}

function experienceLabel(exp) {
  const labels = {
    no_experience: 'Tajriba kerak emas',
    '0-1': '0-1 yil',
    '1-3': '1-3 yil',
    '3-5': '3-5 yil',
    '5+': '5+ yil',
  }
  return labels[exp] || exp
}

function timeAgo(date) {
  const seconds = Math.floor((Date.now() - new Date(date).getTime()) / 1000)

  if (seconds < 60) return 'Hozirgina'
  if (seconds < 3600) return `${Math.floor(seconds / 60)} daqiqa oldin`
  if (seconds < 86400) return `${Math.floor(seconds / 3600)} soat oldin`
  if (seconds < 604800) return `${Math.floor(seconds / 86400)} kun oldin`

  return new Date(date).toLocaleDateString('uz-UZ')
}
</script>

<style scoped>
.badge {
  @apply px-2 py-0.5 text-xs font-medium rounded;
}
</style>
