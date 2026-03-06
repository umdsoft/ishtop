<template>
  <div class="home-view pb-20">
    <!-- Hero Header -->
    <div class="hero-header px-5 pt-6 pb-5">
      <div class="flex items-center justify-between mb-5">
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

      <!-- Search Bar -->
      <div
        class="flex items-center gap-3 px-4 py-3 rounded-2xl cursor-pointer active:scale-[0.98] transition-transform"
        style="background-color: var(--tg-theme-secondary-bg-color); border: 1px solid var(--separator-color);"
        @click="router.push('/search')"
      >
        <svg class="w-5 h-5 flex-shrink-0" style="color: var(--tg-theme-hint-color);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="7" />
          <path stroke-linecap="round" d="M20 20l-4-4" />
        </svg>
        <span class="text-[15px]" style="color: var(--tg-theme-hint-color);">{{ t('home.search_placeholder') }}</span>
      </div>
    </div>

    <!-- Banner -->
    <div class="px-5">
      <BannerSlot placement="home_top" />
    </div>

    <!-- Recommended Vacancies -->
    <div v-if="recommendedLoading || recommended.length > 0" class="mb-2">
      <div class="flex items-center justify-between px-5 mb-3">
        <h2 class="text-[17px] font-bold" style="color: var(--tg-theme-text-color);">{{ t('home.recommended') }}</h2>
        <RouterLink
          to="/search"
          class="text-[13px] font-medium"
          style="color: var(--tg-theme-link-color);"
        >
          {{ t('common.all') }}
        </RouterLink>
      </div>

      <div class="px-5">
        <LoadingSpinner v-if="recommendedLoading" />
        <div v-else class="space-y-2.5">
          <VacancyCard v-for="vacancy in recommended" :key="vacancy.id" :vacancy="vacancy" />
        </div>
      </div>
    </div>

    <!-- Latest Vacancies -->
    <div class="mt-4">
      <div class="flex items-center justify-between px-5 mb-3">
        <h2 class="text-[17px] font-bold" style="color: var(--tg-theme-text-color);">{{ t('home.latest') }}</h2>
      </div>

      <div class="px-5">
        <LoadingSpinner v-if="vacancyStore.loading" />
        <div v-else class="space-y-2.5">
          <VacancyCard v-for="vacancy in vacancyStore.vacancies" :key="vacancy.id" :vacancy="vacancy" />
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
import { useTelegram } from '@/composables/useTelegram'
import { useLocale } from '@/composables/useLocale'
import BannerSlot from '@/components/BannerSlot.vue'
import VacancyCard from '@/components/VacancyCard.vue'
import LoadingSpinner from '@/components/LoadingSpinner.vue'

const router = useRouter()
const vacancyStore = useVacancyStore()
const authStore = useAuthStore()
const telegram = useTelegram()
const { t } = useLocale()

const recommended = ref([])
const recommendedLoading = ref(false)

const userInitial = computed(() => {
  const name = telegram.user.value?.first_name || authStore.user?.first_name || ''
  return name ? name.charAt(0).toUpperCase() : 'U'
})

const greeting = computed(() => {
  const name = telegram.user.value?.first_name || authStore.user?.first_name || ''
  const hour = new Date().getHours()
  let greet = ''
  if (hour >= 5 && hour < 12) greet = 'Xayrli tong'
  else if (hour >= 12 && hour < 18) greet = 'Xayrli kun'
  else greet = 'Xayrli kech'

  return name ? `${greet}, ${name}!` : `${greet}!`
})

onMounted(async () => {
  await vacancyStore.fetchVacancies({ per_page: 10 })

  recommendedLoading.value = true
  try {
    recommended.value = await vacancyStore.recommendedVacancies()
  } catch (error) {
    console.error('Failed to load recommendations:', error)
  } finally {
    recommendedLoading.value = false
  }
})

</script>
