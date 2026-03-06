<template>
  <div class="saved-view p-4 pb-20">
    <!-- Header -->
    <div class="flex items-center gap-3 mb-6">
      <button class="text-tg-hint" @click="router.back()">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </button>
      <h1 class="text-2xl font-bold">{{ t('saved.title') }}</h1>
    </div>

    <!-- Loading -->
    <LoadingSpinner v-if="loading" />

    <!-- Empty State -->
    <div v-else-if="vacancies.length === 0" class="text-center py-12">
      <p class="text-4xl mb-3">🤍</p>
      <p class="text-lg font-medium mb-2">{{ t('saved.empty') }}</p>
      <p class="text-sm text-tg-hint mb-4">{{ t('saved.empty_hint') }}</p>
      <button class="btn-primary" @click="router.push('/search')">
        {{ t('apps.search_btn') }}
      </button>
    </div>

    <!-- Saved Vacancies -->
    <div v-else class="space-y-3">
      <div v-for="item in vacancies" :key="item.id" class="relative">
        <VacancyCard :vacancy="item.saveable || item" />
        <button
          class="absolute top-3 right-3 w-8 h-8 flex items-center justify-center rounded-full bg-red-50 text-red-500"
          @click.stop="removeSaved(item)"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useTelegram } from '@/composables/useTelegram'
import { useLocale } from '@/composables/useLocale'
import VacancyCard from '@/components/VacancyCard.vue'
import LoadingSpinner from '@/components/LoadingSpinner.vue'
import api from '@/utils/api'

const router = useRouter()
const telegram = useTelegram()
const { t } = useLocale()

const vacancies = ref([])
const loading = ref(false)

onMounted(async () => {
  await loadSaved()
})

async function loadSaved() {
  loading.value = true
  try {
    const response = await api.get('/saved', {
      params: { saveable_type: 'Vacancy' },
    })
    vacancies.value = response.data.items || response.data.data || []
  } catch (error) {
    telegram.showAlert(t('common.error'))
  } finally {
    loading.value = false
  }
}

async function removeSaved(item) {
  const confirm = await telegram.showConfirm(t('saved.remove_confirm'))
  if (!confirm) return

  try {
    await api.delete(`/saved/${item.id}`)
    vacancies.value = vacancies.value.filter(v => v.id !== item.id)
    telegram.hapticFeedback('soft')
  } catch (error) {
    telegram.showAlert(t('common.error'))
  }
}
</script>
