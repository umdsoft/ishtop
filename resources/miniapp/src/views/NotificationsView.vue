<template>
  <div class="notifications-view p-4 pb-20">
    <!-- Header -->
    <div class="flex items-center gap-3 mb-6">
      <button class="text-tg-hint" @click="router.back()">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </button>
      <h1 class="text-2xl font-bold">{{ t('notif.title') }}</h1>
    </div>

    <!-- Loading -->
    <LoadingSpinner v-if="loading" />

    <!-- Empty State -->
    <div v-else-if="notifications.length === 0" class="text-center py-12">
      <p class="text-4xl mb-3">🔔</p>
      <p class="text-lg font-medium mb-2">{{ t('notif.empty') }}</p>
      <p class="text-sm text-tg-hint">{{ t('notif.empty_hint') }}</p>
    </div>

    <!-- Notifications List -->
    <div v-else class="space-y-2">
      <div
        v-for="notif in notifications"
        :key="notif.id"
        class="card cursor-pointer transition-all"
        :class="{ 'opacity-60': notif.read_at }"
        @click="handleNotification(notif)"
      >
        <div class="flex items-start gap-3">
          <!-- Icon -->
          <div class="w-10 h-10 rounded-full flex items-center justify-center text-xl"
            :class="getNotifIconBg(notif.type)"
          >
            {{ getNotifIcon(notif.type) }}
          </div>

          <!-- Content -->
          <div class="flex-1 min-w-0">
            <h3 class="font-semibold text-sm mb-1">{{ notif.title }}</h3>
            <p class="text-sm text-tg-hint line-clamp-2">{{ notif.message }}</p>
            <p class="text-xs text-tg-hint mt-1">{{ timeAgo(notif.created_at) }}</p>
          </div>

          <!-- Unread indicator -->
          <div v-if="!notif.read_at" class="w-2 h-2 rounded-full bg-tg-button mt-2"></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useTelegram } from '@/composables/useTelegram'
import { useLocale } from '@/composables/useLocale'
import { timeAgo as _timeAgo } from '@/utils/formatters'
import LoadingSpinner from '@/components/LoadingSpinner.vue'
import api from '@/utils/api'

const router = useRouter()
const telegram = useTelegram()
const { t } = useLocale()

const notifications = ref([])
const loading = ref(false)

onMounted(async () => {
  await loadNotifications()
})

async function loadNotifications() {
  loading.value = true
  try {
    const response = await api.get('/notifications')
    notifications.value = response.data.notifications || response.data.data || []
  } catch (error) {
    telegram.showAlert(t('common.error'))
  } finally {
    loading.value = false
  }
}

async function handleNotification(notif) {
  telegram.hapticFeedback('soft')

  // Mark as read
  if (!notif.read_at) {
    try {
      await api.put(`/notifications/${notif.id}/read`)
      notif.read_at = new Date().toISOString()
    } catch (error) {
      // Silent fail
    }
  }

  // Navigate based on type
  if (notif.type === 'new_application' && notif.data?.vacancy_id) {
    router.push('/my-vacancies')
  } else if (notif.data?.vacancy_id) {
    router.push(`/vacancies/${notif.data.vacancy_id}`)
  } else if (notif.data?.application_id) {
    router.push('/applications')
  }
}

function getNotifIcon(type) {
  const icons = {
    application_stage: '📋',
    new_application: '📝',
    vacancy_moderated: '✅',
    matching_vacancy: '🎯',
    payment: '💳',
  }
  return icons[type] || '🔔'
}

function getNotifIconBg(type) {
  const bgs = {
    application_stage: 'bg-teal-100',
    new_application: 'bg-green-100',
    vacancy_moderated: 'bg-yellow-100',
    matching_vacancy: 'bg-purple-100',
    payment: 'bg-orange-100',
  }
  return bgs[type] || 'bg-gray-100'
}

function timeAgo(date) {
  return _timeAgo(date, t)
}
</script>
