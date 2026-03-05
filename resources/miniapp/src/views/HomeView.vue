<template>
  <div class="home-view p-4 pb-20">
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold mb-2">IshTop</h1>
      <p class="text-tg-hint">Telegramdan chiqmay ish toping!</p>
    </div>

    <!-- Banner -->
    <BannerSlot placement="home_top" />

    <!-- Search Bar -->
    <div class="mb-6">
      <div class="input flex items-center gap-2 cursor-pointer" @click="router.push('/search')">
        <svg class="w-5 h-5 text-tg-hint" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <span class="text-tg-hint">Vakansiya qidirish...</span>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-4 gap-3 mb-6">
      <div
        v-for="action in quickActions"
        :key="action.label"
        class="flex flex-col items-center gap-2 p-3 card cursor-pointer active:opacity-70"
        @click="handleQuickAction(action.action)"
      >
        <span class="text-2xl">{{ action.icon }}</span>
        <span class="text-xs text-center">{{ action.label }}</span>
      </div>
    </div>

    <!-- Recommended Vacancies -->
    <div v-if="recommendedLoading || recommended.length > 0" class="mb-6">
      <div class="flex items-center justify-between mb-3">
        <h2 class="text-lg font-semibold">Siz uchun</h2>
        <RouterLink to="/search" class="text-sm text-tg-link">Barchasi</RouterLink>
      </div>

      <LoadingSpinner v-if="recommendedLoading" />
      <div v-else class="space-y-3">
        <VacancyCard v-for="vacancy in recommended" :key="vacancy.id" :vacancy="vacancy" />
      </div>
    </div>

    <!-- Latest Vacancies -->
    <div class="mb-6">
      <div class="flex items-center justify-between mb-3">
        <h2 class="text-lg font-semibold">So'nggi e'lonlar</h2>
      </div>

      <LoadingSpinner v-if="vacancyStore.loading" />
      <div v-else class="space-y-3">
        <VacancyCard v-for="vacancy in vacancyStore.vacancies" :key="vacancy.id" :vacancy="vacancy" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useVacancyStore } from '@/stores/vacancy'
import { useTelegram } from '@/composables/useTelegram'
import BannerSlot from '@/components/BannerSlot.vue'
import VacancyCard from '@/components/VacancyCard.vue'
import LoadingSpinner from '@/components/LoadingSpinner.vue'

const router = useRouter()
const vacancyStore = useVacancyStore()
const telegram = useTelegram()

const recommended = ref([])
const recommendedLoading = ref(false)

const quickActions = [
  { icon: '🔍', label: 'Qidirish', action: 'search' },
  { icon: '📍', label: 'Yaqinida', action: 'nearby' },
  { icon: '⭐', label: 'TOP', action: 'top' },
  { icon: '📝', label: 'Arizalar', action: 'applications' },
]

onMounted(async () => {
  // Fetch latest vacancies
  await vacancyStore.fetchVacancies({ per_page: 10 })

  // Fetch recommended vacancies
  recommendedLoading.value = true
  try {
    recommended.value = await vacancyStore.recommendedVacancies()
  } catch (error) {
    console.error('Failed to load recommendations:', error)
  } finally {
    recommendedLoading.value = false
  }
})

function handleQuickAction(action) {
  telegram.hapticFeedback('soft')

  switch (action) {
    case 'search':
      router.push('/search')
      break
    case 'nearby':
      router.push('/search?nearby=true')
      break
    case 'top':
      router.push('/search?is_top=true')
      break
    case 'applications':
      router.push('/applications')
      break
  }
}
</script>
