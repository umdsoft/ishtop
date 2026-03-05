<template>
  <div v-if="vacancy" class="vacancy-detail pb-24">
    <!-- Header -->
    <div class="p-4 bg-tg-secondary-bg">
      <div class="flex items-start gap-3 mb-4">
        <img
          v-if="vacancy.employer?.logo_url"
          :src="vacancy.employer.logo_url"
          :alt="vacancy.employer.company_name"
          class="w-16 h-16 rounded-lg object-cover"
        />
        <div class="flex-1">
          <h1 class="text-xl font-bold mb-1">{{ vacancy.title_uz || vacancy.title_ru }}</h1>
          <p class="text-tg-hint">{{ vacancy.employer?.company_name }}</p>
        </div>
      </div>

      <!-- Badges -->
      <div class="flex flex-wrap gap-2 mb-3">
        <span v-if="vacancy.is_top" class="badge bg-yellow-100 text-yellow-800">TOP e'lon</span>
        <span v-if="vacancy.is_urgent" class="badge bg-red-100 text-red-800">Tezkor</span>
        <span class="badge bg-gray-100">{{ vacancy.category }}</span>
      </div>

      <!-- Salary -->
      <div v-if="vacancy.salary_min || vacancy.salary_max" class="text-xl font-bold text-green-600 mb-2">
        {{ formatSalary(vacancy) }}
      </div>

      <!-- Meta Info -->
      <div class="flex flex-wrap gap-3 text-sm text-tg-hint">
        <span>📍 {{ vacancy.city || 'Shahar ko\'rsatilmagan' }}</span>
        <span>💼 {{ workTypeLabel(vacancy.work_type) }}</span>
        <span>⏱ {{ experienceLabel(vacancy.experience_required) }}</span>
        <span>👁 {{ vacancy.views_count }} ko'rildi</span>
      </div>
    </div>

    <!-- Description -->
    <div class="p-4">
      <h2 class="text-lg font-semibold mb-2">Tavsif</h2>
      <div class="text-tg-text whitespace-pre-line">{{ vacancy.description_uz || vacancy.description_ru }}</div>
    </div>

    <!-- Requirements -->
    <div v-if="vacancy.requirements_uz || vacancy.requirements_ru" class="p-4 bg-tg-secondary-bg">
      <h2 class="text-lg font-semibold mb-2">Talablar</h2>
      <div class="text-tg-text whitespace-pre-line">{{ vacancy.requirements_uz || vacancy.requirements_ru }}</div>
    </div>

    <!-- Responsibilities -->
    <div v-if="vacancy.responsibilities_uz || vacancy.responsibilities_ru" class="p-4">
      <h2 class="text-lg font-semibold mb-2">Mas'uliyatlar</h2>
      <div class="text-tg-text whitespace-pre-line">{{ vacancy.responsibilities_uz || vacancy.responsibilities_ru }}</div>
    </div>

    <!-- Company Info -->
    <div v-if="vacancy.employer" class="p-4 bg-tg-secondary-bg">
      <h2 class="text-lg font-semibold mb-3">Kompaniya haqida</h2>
      <div class="flex items-center gap-2 mb-2">
        <span class="text-2xl">⭐</span>
        <span class="font-medium">{{ vacancy.employer.rating || '0.0' }}</span>
        <span class="text-sm text-tg-hint">({{ vacancy.employer.rating_count || 0 }} baho)</span>
        <span v-if="vacancy.employer.verification_level === 'verified'" class="ml-2 text-blue-500">✓ Tasdiqlangan</span>
      </div>
      <p v-if="vacancy.employer.description" class="text-sm text-tg-text">{{ vacancy.employer.description }}</p>
    </div>

    <!-- Contact Info -->
    <div v-if="vacancy.contact_phone" class="p-4">
      <h2 class="text-lg font-semibold mb-2">Aloqa</h2>
      <a :href="`tel:${vacancy.contact_phone}`" class="text-tg-link">
        📞 {{ vacancy.contact_phone }}
      </a>
    </div>

    <!-- Published Date -->
    <div class="p-4 text-sm text-tg-hint">
      E'lon qilingan: {{ formatDate(vacancy.published_at || vacancy.created_at) }}
    </div>

    <!-- Fixed Bottom Actions -->
    <div class="fixed bottom-0 left-0 right-0 p-4 bg-tg-bg border-t border-gray-200 flex gap-2">
      <button
        v-if="!hasApplied"
        class="btn-primary flex-1"
        @click="handleApply"
        :disabled="applying"
      >
        {{ applying ? 'Yuborilmoqda...' : 'Ariza yuborish' }}
      </button>
      <div v-else class="flex-1 text-center py-2 text-green-600 font-medium">
        ✓ Ariza yuborilgan
      </div>
      <button class="btn-secondary" @click="handleSave">
        {{ isSaved ? '❤️' : '🤍' }}
      </button>
      <button class="btn-secondary" @click="handleShare">
        🔗
      </button>
    </div>
  </div>

  <LoadingSpinner v-else-if="loading" />

  <div v-else class="p-4 text-center">
    <p class="text-tg-hint">Vakansiya topilmadi</p>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useVacancyStore } from '@/stores/vacancy'
import { useAuthStore } from '@/stores/auth'
import { useTelegram } from '@/composables/useTelegram'
import LoadingSpinner from '@/components/LoadingSpinner.vue'
import api from '@/utils/api'

const route = useRoute()
const router = useRouter()
const vacancyStore = useVacancyStore()
const authStore = useAuthStore()
const telegram = useTelegram()

const vacancy = ref(null)
const loading = ref(false)
const applying = ref(false)
const hasApplied = ref(false)
const isSaved = ref(false)

onMounted(async () => {
  loading.value = true
  try {
    vacancy.value = await vacancyStore.fetchVacancy(route.params.id)

    // Check if already applied
    const myApps = await api.get('/applications/my')
    hasApplied.value = myApps.data.applications?.some(app => app.vacancy_id === vacancy.value.id)

    // Check if saved
    const saved = await api.get('/saved')
    isSaved.value = saved.data.items?.some(
      item => item.saveable_type === 'Vacancy' && item.saveable_id === vacancy.value.id
    )
  } catch (error) {
    telegram.showAlert('Vakansiya yuklanmadi')
    router.back()
  } finally {
    loading.value = false
  }
})

async function handleApply() {
  if (!authStore.isAuthenticated) {
    telegram.showAlert('Avval tizimga kiring')
    return
  }

  if (!authStore.isWorker) {
    telegram.showAlert('Ariza yuborish uchun ishchi profili kerak')
    return
  }

  // Check if vacancy has questionnaire
  if (vacancy.value.has_questionnaire) {
    const confirm = await telegram.showConfirm(
      'Bu vakansiya uchun savolnoma to\'ldirish kerak. Davom etasizmi?'
    )
    if (!confirm) return

    try {
      applying.value = true
      const application = await vacancyStore.applyToVacancy(vacancy.value.id)

      // Navigate to questionnaire
      router.push({
        name: 'questionnaire',
        params: { vacancyId: vacancy.value.id },
        query: { applicationId: application.application.id },
      })
    } catch (error) {
      telegram.showAlert(error.response?.data?.message || 'Xatolik yuz berdi')
    } finally {
      applying.value = false
    }
  } else {
    try {
      applying.value = true
      await vacancyStore.applyToVacancy(vacancy.value.id)
      hasApplied.value = true
      telegram.showAlert('Ariza muvaffaqiyatli yuborildi!')
    } catch (error) {
      telegram.showAlert(error.response?.data?.message || 'Xatolik yuz berdi')
    } finally {
      applying.value = false
    }
  }
}

async function handleSave() {
  try {
    if (isSaved.value) {
      // Remove from saved
      const saved = await api.get('/saved', {
        params: { saveable_type: 'Vacancy' },
      })
      const savedItem = saved.data.items?.find(
        item => item.saveable_id === vacancy.value.id
      )
      if (savedItem) {
        await api.delete(`/saved/${savedItem.id}`)
        isSaved.value = false
        telegram.hapticFeedback('soft')
      }
    } else {
      // Add to saved
      await api.post('/saved', {
        saveable_type: 'App\\Models\\Vacancy',
        saveable_id: vacancy.value.id,
      })
      isSaved.value = true
      telegram.hapticFeedback('medium')
    }
  } catch (error) {
    telegram.showAlert('Xatolik yuz berdi')
  }
}

function handleShare() {
  const url = `https://t.me/ishtop_bot/app?startapp=vacancy_${vacancy.value.id}`
  const text = `${vacancy.value.title_uz || vacancy.value.title_ru} - ${vacancy.value.employer?.company_name}`
  telegram.shareUrl(url, text)
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

function formatDate(date) {
  return new Date(date).toLocaleDateString('uz-UZ', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
}
</script>

<style scoped>
.badge {
  @apply px-2 py-1 text-xs font-medium rounded;
}
</style>
