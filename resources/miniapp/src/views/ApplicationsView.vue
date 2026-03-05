<template>
  <div class="applications-view p-4 pb-20">
    <!-- Header -->
    <h1 class="text-2xl font-bold mb-4">Mening arizalarim</h1>

    <!-- Filter Tabs -->
    <div class="flex gap-2 mb-4 overflow-x-auto">
      <button
        v-for="tab in tabs"
        :key="tab.value"
        class="px-4 py-2 rounded-lg font-medium whitespace-nowrap transition-colors"
        :class="activeTab === tab.value ? 'bg-tg-button text-tg-button-text' : 'bg-tg-secondary-bg'"
        @click="activeTab = tab.value"
      >
        {{ tab.label }}
        <span v-if="tab.count > 0" class="ml-1 text-xs opacity-80">({{ tab.count }})</span>
      </button>
    </div>

    <!-- Loading -->
    <LoadingSpinner v-if="loading" />

    <!-- Empty State -->
    <div v-else-if="filteredApplications.length === 0" class="text-center py-12">
      <p class="text-4xl mb-3">📝</p>
      <p class="text-lg font-medium mb-2">Arizalar yo'q</p>
      <p class="text-sm text-tg-hint mb-4">Siz hali hech qanday vakansiyaga ariza yubormagansiz</p>
      <button class="btn-primary" @click="router.push('/search')">
        Vakansiya qidirish
      </button>
    </div>

    <!-- Applications List -->
    <div v-else class="space-y-3">
      <div
        v-for="app in filteredApplications"
        :key="app.id"
        class="card cursor-pointer hover:shadow-md transition-shadow"
        @click="viewApplication(app)"
      >
        <!-- Header -->
        <div class="flex items-start gap-3 mb-3">
          <img
            v-if="app.vacancy?.employer?.logo_url"
            :src="app.vacancy.employer.logo_url"
            class="w-12 h-12 rounded-lg object-cover"
          />
          <div class="flex-1 min-w-0">
            <h3 class="font-semibold line-clamp-2">{{ app.vacancy?.title }}</h3>
            <p class="text-sm text-tg-hint">{{ app.vacancy?.employer?.company_name }}</p>
          </div>
        </div>

        <!-- Stage Badge -->
        <div class="mb-3">
          <span
            class="inline-block px-3 py-1 rounded-full text-xs font-medium"
            :class="getStageClass(app.stage)"
          >
            {{ getStageLabel(app.stage) }}
          </span>
        </div>

        <!-- Score and Rating -->
        <div class="flex items-center gap-4 text-sm mb-3">
          <div v-if="app.questionnaire_score" class="flex items-center gap-1">
            <span>📊</span>
            <span class="font-medium">{{ app.questionnaire_score }}%</span>
            <span class="text-tg-hint">ball</span>
          </div>
          <div v-if="app.matching_score" class="flex items-center gap-1">
            <span>🎯</span>
            <span class="font-medium">{{ app.matching_score }}%</span>
            <span class="text-tg-hint">mos</span>
          </div>
          <div v-if="app.recruiter_rating" class="flex items-center gap-1">
            <span>⭐</span>
            <span class="font-medium">{{ app.recruiter_rating }}/5</span>
          </div>
        </div>

        <!-- Timeline -->
        <div class="flex items-center justify-between text-xs text-tg-hint">
          <span>Yuborilgan: {{ formatDate(app.created_at) }}</span>
          <span v-if="app.viewed_at">Ko'rilgan ✓</span>
        </div>

        <!-- Rejection Reason -->
        <div v-if="app.stage === 'rejected' && app.rejected_reason" class="mt-3 p-2 bg-red-50 rounded text-sm text-red-700">
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
import LoadingSpinner from '@/components/LoadingSpinner.vue'
import api from '@/utils/api'

const router = useRouter()
const telegram = useTelegram()

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
    { value: 'all', label: 'Barchasi', count: counts.all },
    { value: 'new', label: 'Yangi', count: counts.new },
    { value: 'in_progress', label: 'Jarayonda', count: counts.in_progress },
    { value: 'offered', label: 'Taklif', count: counts.offered },
    { value: 'hired', label: 'Ishga qabul', count: counts.hired },
    { value: 'rejected', label: 'Rad etilgan', count: counts.rejected },
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
    applications.value = response.data.applications || []
  } catch (error) {
    telegram.showAlert('Arizalarni yuklashda xatolik')
  } finally {
    loading.value = false
  }
}

function viewApplication(app) {
  telegram.hapticFeedback('soft')
  router.push(`/vacancies/${app.vacancy_id}`)
}

function getStageLabel(stage) {
  const labels = {
    new: 'Yangi',
    reviewed: 'Ko\'rib chiqildi',
    shortlisted: 'Tanlangan',
    interview: 'Intervyu',
    offered: 'Taklif',
    hired: 'Ishga qabul',
    rejected: 'Rad etildi',
  }
  return labels[stage] || stage
}

function getStageClass(stage) {
  const classes = {
    new: 'bg-blue-100 text-blue-800',
    reviewed: 'bg-purple-100 text-purple-800',
    shortlisted: 'bg-yellow-100 text-yellow-800',
    interview: 'bg-orange-100 text-orange-800',
    offered: 'bg-green-100 text-green-800',
    hired: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
  }
  return classes[stage] || 'bg-gray-100 text-gray-800'
}

function formatDate(date) {
  return new Date(date).toLocaleDateString('uz-UZ', {
    day: 'numeric',
    month: 'short',
  })
}
</script>
