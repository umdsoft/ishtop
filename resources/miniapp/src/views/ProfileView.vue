<template>
  <div class="profile-view p-4 pb-20">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold">Profil</h1>
      <button class="btn-secondary text-sm" @click="handleLogout">
        Chiqish
      </button>
    </div>

    <!-- User Info Card -->
    <div v-if="authStore.user" class="card mb-4">
      <div class="flex items-center gap-3 mb-4">
        <div class="w-16 h-16 rounded-full bg-tg-button text-tg-button-text flex items-center justify-center text-2xl font-bold">
          {{ getUserInitial() }}
        </div>
        <div class="flex-1">
          <h2 class="font-semibold text-lg">{{ authStore.user.first_name }} {{ authStore.user.last_name }}</h2>
          <p class="text-sm text-tg-hint">@{{ authStore.user.username || 'user' }}</p>
        </div>
      </div>

      <!-- Balance -->
      <div class="flex items-center justify-between p-3 bg-tg-bg rounded-lg">
        <span class="text-sm text-tg-hint">Balans</span>
        <span class="font-semibold">{{ formatNumber(authStore.user.balance || 0) }} so'm</span>
      </div>
    </div>

    <!-- Profile Type Tabs -->
    <div class="flex gap-2 mb-4">
      <button
        class="flex-1 py-2 px-4 rounded-lg font-medium transition-colors"
        :class="profileType === 'worker' ? 'bg-tg-button text-tg-button-text' : 'bg-tg-secondary-bg'"
        @click="profileType = 'worker'"
      >
        Ishchi profili
      </button>
      <button
        class="flex-1 py-2 px-4 rounded-lg font-medium transition-colors"
        :class="profileType === 'employer' ? 'bg-tg-button text-tg-button-text' : 'bg-tg-secondary-bg'"
        @click="profileType = 'employer'"
      >
        Ish beruvchi
      </button>
    </div>

    <!-- Worker Profile -->
    <div v-if="profileType === 'worker'">
      <LoadingSpinner v-if="profileStore.loading" />

      <div v-else-if="profileStore.workerProfile" class="space-y-4">
        <!-- Search Status -->
        <div class="card">
          <div class="flex items-center justify-between mb-2">
            <span class="font-medium">Ish qidirish holati</span>
            <select
              v-model="searchStatus"
              class="px-3 py-1 rounded-lg bg-tg-secondary-bg"
              @change="updateSearchStatus"
            >
              <option value="open">Ochiq</option>
              <option value="passive">Passiv</option>
              <option value="closed">Yopiq</option>
            </select>
          </div>
          <p class="text-sm text-tg-hint">
            {{ searchStatusHint }}
          </p>
        </div>

        <!-- Profile Info -->
        <div class="card">
          <h3 class="font-semibold mb-3">Asosiy ma'lumotlar</h3>
          <div class="space-y-2 text-sm">
            <div class="flex justify-between">
              <span class="text-tg-hint">To'liq ism</span>
              <span class="font-medium">{{ profileStore.workerProfile.full_name }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-tg-hint">Mutaxassislik</span>
              <span class="font-medium">{{ profileStore.workerProfile.specialty || '-' }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-tg-hint">Shahar</span>
              <span class="font-medium">{{ profileStore.workerProfile.city || '-' }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-tg-hint">Tajriba</span>
              <span class="font-medium">{{ profileStore.workerProfile.experience_years }} yil</span>
            </div>
            <div class="flex justify-between">
              <span class="text-tg-hint">Kutilgan maosh</span>
              <span class="font-medium">
                {{ formatSalaryRange(profileStore.workerProfile) }}
              </span>
            </div>
          </div>
        </div>

        <!-- Statistics -->
        <div class="card">
          <h3 class="font-semibold mb-3">Statistika</h3>
          <div class="grid grid-cols-2 gap-3">
            <div class="text-center p-3 bg-tg-bg rounded-lg">
              <div class="text-2xl font-bold text-tg-button">{{ profileStore.workerProfile.views_count || 0 }}</div>
              <div class="text-xs text-tg-hint">Ko'rishlar</div>
            </div>
            <div class="text-center p-3 bg-tg-bg rounded-lg">
              <div class="text-2xl font-bold text-tg-button">{{ applicationCount }}</div>
              <div class="text-xs text-tg-hint">Arizalar</div>
            </div>
          </div>
        </div>

        <!-- Edit Button -->
        <button class="btn-primary w-full" @click="editWorkerProfile">
          Profilni tahrirlash
        </button>
      </div>

      <div v-else class="card text-center py-8">
        <p class="text-tg-hint mb-4">Ishchi profili yaratilmagan</p>
        <button class="btn-primary" @click="createWorkerProfile">
          Profil yaratish
        </button>
      </div>
    </div>

    <!-- Employer Profile -->
    <div v-else>
      <LoadingSpinner v-if="profileStore.loading" />

      <div v-else-if="profileStore.employerProfile" class="space-y-4">
        <!-- Company Info -->
        <div class="card">
          <div class="flex items-start gap-3 mb-4">
            <img
              v-if="profileStore.employerProfile.logo_url"
              :src="profileStore.employerProfile.logo_url"
              class="w-16 h-16 rounded-lg object-cover"
            />
            <div class="flex-1">
              <h3 class="font-semibold text-lg">{{ profileStore.employerProfile.company_name }}</h3>
              <p class="text-sm text-tg-hint">{{ profileStore.employerProfile.industry || 'Sanoat ko\'rsatilmagan' }}</p>
            </div>
          </div>

          <!-- Rating -->
          <div class="flex items-center gap-2 mb-3">
            <span class="text-xl">⭐</span>
            <span class="font-semibold">{{ profileStore.employerProfile.rating || '0.0' }}</span>
            <span class="text-sm text-tg-hint">({{ profileStore.employerProfile.rating_count || 0 }} baho)</span>
            <span v-if="profileStore.employerProfile.verification_level === 'verified'" class="ml-auto text-blue-500 text-sm">
              ✓ Tasdiqlangan
            </span>
          </div>

          <p v-if="profileStore.employerProfile.description" class="text-sm">
            {{ profileStore.employerProfile.description }}
          </p>
        </div>

        <!-- Contact Info -->
        <div class="card">
          <h3 class="font-semibold mb-3">Aloqa ma'lumotlari</h3>
          <div class="space-y-2 text-sm">
            <div v-if="profileStore.employerProfile.phone" class="flex items-center gap-2">
              <span>📞</span>
              <span>{{ profileStore.employerProfile.phone }}</span>
            </div>
            <div v-if="profileStore.employerProfile.website" class="flex items-center gap-2">
              <span>🌐</span>
              <a :href="profileStore.employerProfile.website" class="text-tg-link">
                {{ profileStore.employerProfile.website }}
              </a>
            </div>
            <div v-if="profileStore.employerProfile.address" class="flex items-center gap-2">
              <span>📍</span>
              <span>{{ profileStore.employerProfile.address }}</span>
            </div>
          </div>
        </div>

        <!-- Statistics -->
        <div class="card">
          <h3 class="font-semibold mb-3">Statistika</h3>
          <div class="grid grid-cols-2 gap-3">
            <div class="text-center p-3 bg-tg-bg rounded-lg">
              <div class="text-2xl font-bold text-tg-button">{{ vacancyCount }}</div>
              <div class="text-xs text-tg-hint">Vakansiyalar</div>
            </div>
            <div class="text-center p-3 bg-tg-bg rounded-lg">
              <div class="text-2xl font-bold text-tg-button">{{ profileStore.employerProfile.response_time_avg || 0 }}</div>
              <div class="text-xs text-tg-hint">O'rt. javob vaqti (daqiqa)</div>
            </div>
          </div>
        </div>

        <!-- Edit Button -->
        <button class="btn-primary w-full" @click="editEmployerProfile">
          Profilni tahrirlash
        </button>
      </div>

      <div v-else class="card text-center py-8">
        <p class="text-tg-hint mb-4">Ish beruvchi profili yaratilmagan</p>
        <button class="btn-primary" @click="createEmployerProfile">
          Profil yaratish
        </button>
      </div>
    </div>

    <!-- Settings -->
    <div class="mt-6">
      <h3 class="font-semibold mb-3">Sozlamalar</h3>
      <div class="space-y-2">
        <div class="card flex items-center justify-between cursor-pointer" @click="router.push('/saved')">
          <span>🤍 Saqlangan e'lonlar</span>
          <span class="text-tg-hint">›</span>
        </div>
        <div class="card flex items-center justify-between cursor-pointer" @click="router.push('/notifications')">
          <span>🔔 Bildirishnomalar</span>
          <span class="text-tg-hint">›</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useProfileStore } from '@/stores/profile'
import { useTelegram } from '@/composables/useTelegram'
import LoadingSpinner from '@/components/LoadingSpinner.vue'
import api from '@/utils/api'

const router = useRouter()
const authStore = useAuthStore()
const profileStore = useProfileStore()
const telegram = useTelegram()

const profileType = ref('worker')
const searchStatus = ref('open')
const applicationCount = ref(0)
const vacancyCount = ref(0)

const searchStatusHint = computed(() => {
  const hints = {
    open: 'Siz aktiv ish qidiryapsiz. Profilingiz ish beruvchilarga ko\'rinadi.',
    passive: 'Siz passiv qidiryapsiz. Faqat mos takliflar keladi.',
    closed: 'Siz ish qidirmayapsiz. Profilingiz yashirin.',
  }
  return hints[searchStatus.value]
})

onMounted(async () => {
  if (authStore.isWorker) {
    await profileStore.fetchWorkerProfile()
    searchStatus.value = profileStore.workerProfile?.search_status || 'open'

    // Fetch application count
    try {
      const apps = await api.get('/applications/my')
      applicationCount.value = apps.data.applications?.length || 0
    } catch (error) {
      console.error('Failed to load applications:', error)
    }
  }

  if (authStore.isEmployer) {
    await profileStore.fetchEmployerProfile()

    // Fetch vacancy count
    try {
      const vacancies = await api.get('/recruiter/vacancies')
      vacancyCount.value = vacancies.data.data?.length || 0
    } catch (error) {
      console.error('Failed to load vacancies:', error)
    }
  }
})

function getUserInitial() {
  return authStore.user?.first_name?.charAt(0).toUpperCase() || '?'
}

function formatNumber(num) {
  return new Intl.NumberFormat('uz-UZ').format(num)
}

function formatSalaryRange(profile) {
  if (profile.expected_salary_min && profile.expected_salary_max) {
    return `${formatNumber(profile.expected_salary_min)} - ${formatNumber(profile.expected_salary_max)} so'm`
  } else if (profile.expected_salary_min) {
    return `${formatNumber(profile.expected_salary_min)} so'm dan`
  }
  return 'Ko\'rsatilmagan'
}

async function updateSearchStatus() {
  try {
    await profileStore.updateSearchStatus(searchStatus.value)
    telegram.hapticFeedback('soft')
  } catch (error) {
    telegram.showAlert('Xatolik yuz berdi')
  }
}

function editWorkerProfile() {
  telegram.showAlert('Profilni tahrirlash funksiyasi tez orada qo\'shiladi')
}

function createWorkerProfile() {
  telegram.showAlert('Bot orqali profil yaratish kerak')
}

function editEmployerProfile() {
  telegram.showAlert('Profilni tahrirlash funksiyasi tez orada qo\'shiladi')
}

function createEmployerProfile() {
  telegram.showAlert('Bot orqali profil yaratish kerak')
}

async function handleLogout() {
  const confirm = await telegram.showConfirm('Haqiqatan ham chiqmoqchimisiz?')
  if (confirm) {
    authStore.logout()
    telegram.close()
  }
}
</script>
