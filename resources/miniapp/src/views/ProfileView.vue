<template>
  <div class="pb-20">
    <!-- Profile Header -->
    <div class="px-5 pt-6 pb-5" style="background-color: var(--tg-theme-secondary-bg-color);">
      <div class="flex items-center gap-4 mb-5">
        <!-- Avatar -->
        <div class="relative">
          <img
            v-if="telegramPhoto"
            :src="telegramPhoto"
            class="w-16 h-16 rounded-full object-cover"
          />
          <div
            v-else
            class="w-16 h-16 rounded-full flex items-center justify-center text-2xl font-bold"
            style="background-color: var(--tg-theme-button-color); color: var(--tg-theme-button-text-color);"
          >
            {{ getUserInitial() }}
          </div>
        </div>

        <div class="flex-1">
          <h2 class="text-lg font-bold" style="color: var(--tg-theme-text-color);">
            {{ authStore.user?.first_name }} {{ authStore.user?.last_name || '' }}
          </h2>
          <p class="text-[13px]" style="color: var(--tg-theme-hint-color);">
            @{{ authStore.user?.username || 'user' }}
          </p>
        </div>
      </div>

      <!-- Balance Card -->
      <div
        class="flex items-center justify-between px-4 py-3 rounded-2xl cursor-pointer active:opacity-70 transition-opacity"
        style="background-color: var(--tg-theme-bg-color);"
        @click="router.push('/transactions')"
      >
        <div class="flex items-center gap-2">
          <svg class="w-5 h-5" style="color: var(--tg-theme-button-color);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
          </svg>
          <span class="text-[13px]" style="color: var(--tg-theme-hint-color);">{{ t('profile.balance') }}</span>
        </div>
        <div class="flex items-center gap-2">
          <span class="font-bold text-[15px]" style="color: var(--tg-theme-text-color);">
            {{ formatNumber(authStore.user?.balance || 0) }} {{ t('common.som') }}
          </span>
          <svg class="w-4 h-4" style="color: var(--tg-theme-hint-color);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
          </svg>
        </div>
      </div>
    </div>

    <!-- Profile Type Tabs -->
    <div class="px-5 pt-4 pb-3">
      <div class="flex gap-2 p-1 rounded-2xl" style="background-color: var(--tg-theme-secondary-bg-color);">
        <button
          class="flex-1 py-2.5 rounded-xl text-[14px] font-semibold transition-all"
          :style="profileType === 'worker'
            ? { backgroundColor: 'var(--tg-theme-button-color)', color: 'var(--tg-theme-button-text-color)' }
            : { color: 'var(--tg-theme-hint-color)' }"
          @click="profileType = 'worker'"
        >
          {{ t('profile.worker_tab') }}
        </button>
        <button
          class="flex-1 py-2.5 rounded-xl text-[14px] font-semibold transition-all"
          :style="profileType === 'employer'
            ? { backgroundColor: 'var(--tg-theme-button-color)', color: 'var(--tg-theme-button-text-color)' }
            : { color: 'var(--tg-theme-hint-color)' }"
          @click="profileType = 'employer'"
        >
          {{ t('profile.employer_tab') }}
        </button>
      </div>
    </div>

    <!-- Worker Profile -->
    <div v-if="profileType === 'worker'" class="px-5">
      <LoadingSpinner v-if="workerLoading" />

      <div v-else-if="workerProfile" class="space-y-3">
        <!-- Search Status -->
        <div class="rounded-2xl p-4" style="background-color: var(--tg-theme-secondary-bg-color);">
          <div class="flex items-center justify-between mb-2">
            <span class="text-[14px] font-semibold" style="color: var(--tg-theme-text-color);">{{ t('profile.search_status') }}</span>
            <select
              v-model="searchStatus"
              class="input text-[13px]"
              style="width: auto; padding: 6px 28px 6px 12px;"
              @change="updateSearchStatus"
            >
              <option value="open">{{ t('profile.status_open') }}</option>
              <option value="passive">{{ t('profile.status_passive') }}</option>
              <option value="closed">{{ t('profile.status_closed') }}</option>
            </select>
          </div>
          <p class="text-[12px]" style="color: var(--tg-theme-hint-color);">
            {{ t(`profile.status_${searchStatus}_hint`) }}
          </p>
        </div>

        <!-- Profile Info -->
        <div class="rounded-2xl p-4" style="background-color: var(--tg-theme-secondary-bg-color);">
          <h3 class="text-[14px] font-bold mb-3" style="color: var(--tg-theme-text-color);">{{ t('profile.basic_info') }}</h3>
          <div class="space-y-3">
            <div class="flex justify-between items-center">
              <span class="text-[13px]" style="color: var(--tg-theme-hint-color);">{{ t('profile.full_name') }}</span>
              <span class="text-[13px] font-medium" style="color: var(--tg-theme-text-color);">{{ workerProfile.full_name || '-' }}</span>
            </div>
            <div class="h-px" style="background-color: var(--separator-color);"></div>
            <div class="flex justify-between items-center">
              <span class="text-[13px]" style="color: var(--tg-theme-hint-color);">{{ t('profile.specialty') }}</span>
              <span class="text-[13px] font-medium" style="color: var(--tg-theme-text-color);">{{ workerProfile.specialty || '-' }}</span>
            </div>
            <div class="h-px" style="background-color: var(--separator-color);"></div>
            <div class="flex justify-between items-center">
              <span class="text-[13px]" style="color: var(--tg-theme-hint-color);">{{ t('profile.city') }}</span>
              <span class="text-[13px] font-medium" style="color: var(--tg-theme-text-color);">{{ formatLocation(workerProfile.city, workerProfile.district) || '-' }}</span>
            </div>
            <div class="h-px" style="background-color: var(--separator-color);"></div>
            <div class="flex justify-between items-center">
              <span class="text-[13px]" style="color: var(--tg-theme-hint-color);">{{ t('profile.experience') }}</span>
              <span class="text-[13px] font-medium" style="color: var(--tg-theme-text-color);">{{ workerProfile.experience_years || 0 }} {{ t('common.year') }}</span>
            </div>
            <div class="h-px" style="background-color: var(--separator-color);"></div>
            <div class="flex justify-between items-center">
              <span class="text-[13px]" style="color: var(--tg-theme-hint-color);">{{ t('profile.expected_salary') }}</span>
              <span class="text-[13px] font-medium" style="color: var(--tg-theme-text-color);">{{ formatSalaryRange(workerProfile) }}</span>
            </div>
          </div>
        </div>

        <!-- Work Experience -->
        <div v-if="workerProfile.work_experience?.length" class="rounded-2xl p-4" style="background-color: var(--tg-theme-secondary-bg-color);">
          <h3 class="text-[14px] font-bold mb-3" style="color: var(--tg-theme-text-color);">{{ t('profile.work_experience') }}</h3>
          <div class="space-y-3">
            <div v-for="(exp, idx) in workerProfile.work_experience" :key="idx">
              <div v-if="idx > 0" class="h-px mb-3" style="background-color: var(--separator-color);"></div>
              <div class="flex items-start gap-3">
                <div
                  class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5"
                  style="background-color: rgba(249,115,22,0.1);"
                >
                  <span class="text-[11px] font-bold" style="color: #f97316;">{{ idx + 1 }}</span>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="text-[14px] font-semibold" style="color: var(--tg-theme-text-color);">{{ exp.position }}</div>
                  <div class="text-[13px] mt-0.5" style="color: var(--tg-theme-hint-color);">{{ exp.company }}</div>
                  <div class="text-[12px] mt-1" style="color: var(--tg-theme-hint-color); opacity: 0.7;">
                    {{ formatExpDate(exp.start_date) }} — {{ exp.end_date ? formatExpDate(exp.end_date) : t('profile.present') }}
                  </div>
                  <div v-if="exp.description" class="text-[12px] mt-1.5" style="color: var(--tg-theme-hint-color);">
                    {{ exp.description }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Statistics -->
        <div class="rounded-2xl p-4" style="background-color: var(--tg-theme-secondary-bg-color);">
          <h3 class="text-[14px] font-bold mb-3" style="color: var(--tg-theme-text-color);">{{ t('profile.statistics') }}</h3>
          <div class="grid grid-cols-2 gap-2.5">
            <div class="text-center py-3 rounded-xl" style="background-color: var(--tg-theme-bg-color);">
              <div class="text-xl font-bold" style="color: var(--tg-theme-button-color);">{{ workerProfile.views_count || 0 }}</div>
              <div class="text-[11px] mt-0.5" style="color: var(--tg-theme-hint-color);">{{ t('profile.views_count') }}</div>
            </div>
            <div class="text-center py-3 rounded-xl" style="background-color: var(--tg-theme-bg-color);">
              <div class="text-xl font-bold" style="color: var(--tg-theme-button-color);">{{ applicationCount }}</div>
              <div class="text-[11px] mt-0.5" style="color: var(--tg-theme-hint-color);">{{ t('profile.apps_count') }}</div>
            </div>
          </div>
        </div>

        <!-- Edit Button -->
        <button class="btn-primary w-full" @click="router.push('/profile/worker/edit')">
          {{ t('profile.edit_profile') }}
        </button>
      </div>

      <!-- No Worker Profile -->
      <div v-else class="text-center py-12 rounded-2xl" style="background-color: var(--tg-theme-secondary-bg-color);">
        <svg class="w-12 h-12 mx-auto mb-3" style="color: var(--tg-theme-hint-color);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
        </svg>
        <p class="text-[14px] mb-4" style="color: var(--tg-theme-hint-color);">{{ t('profile.no_worker') }}</p>
        <button class="btn-primary" @click="router.push('/profile/worker/edit')">
          {{ t('profile.create_profile') }}
        </button>
      </div>
    </div>

    <!-- Employer Profile -->
    <div v-else class="px-5">
      <LoadingSpinner v-if="employerLoading" />

      <div v-else-if="employerProfile" class="space-y-3">
        <!-- Company Card -->
        <div class="rounded-2xl p-4" style="background-color: var(--tg-theme-secondary-bg-color);">
          <div class="flex items-center gap-3 mb-3">
            <img
              v-if="employerProfile.logo_url"
              :src="employerProfile.logo_url"
              class="w-12 h-12 rounded-xl object-cover"
            />
            <div
              v-else
              class="w-12 h-12 rounded-xl flex items-center justify-center"
              :style="{ backgroundColor: 'rgba(13, 148, 136, 0.12)' }"
            >
              <span class="text-lg font-bold" style="color: var(--tg-theme-button-color);">
                {{ employerProfile.company_name?.charAt(0)?.toUpperCase() || '?' }}
              </span>
            </div>
            <div class="flex-1">
              <h3 class="font-bold text-[15px]" style="color: var(--tg-theme-text-color);">{{ employerProfile.company_name }}</h3>
              <p class="text-[12px]" style="color: var(--tg-theme-hint-color);">{{ employerProfile.industry || t('profile.no_industry') }}</p>
            </div>
            <span
              v-if="employerProfile.verification_level === 'verified'"
              class="text-[12px] font-medium px-2 py-1 rounded-lg"
              style="background-color: rgba(13, 148, 136, 0.12); color: var(--tg-theme-button-color);"
            >
              {{ t('common.verified') }}
            </span>
          </div>
          <p v-if="employerProfile.description" class="text-[13px]" style="color: var(--tg-theme-hint-color);">
            {{ employerProfile.description }}
          </p>
        </div>

        <!-- Statistics -->
        <div class="rounded-2xl p-4" style="background-color: var(--tg-theme-secondary-bg-color);">
          <h3 class="text-[14px] font-bold mb-3" style="color: var(--tg-theme-text-color);">{{ t('profile.statistics') }}</h3>
          <div class="grid grid-cols-2 gap-2.5">
            <div class="text-center py-3 rounded-xl" style="background-color: var(--tg-theme-bg-color);">
              <div class="text-xl font-bold" style="color: var(--tg-theme-button-color);">{{ vacancyCount }}</div>
              <div class="text-[11px] mt-0.5" style="color: var(--tg-theme-hint-color);">{{ t('profile.vacancies_count') }}</div>
            </div>
            <div class="text-center py-3 rounded-xl" style="background-color: var(--tg-theme-bg-color);">
              <div class="text-xl font-bold" style="color: var(--tg-theme-button-color);">{{ employerProfile.rating || '0.0' }}</div>
              <div class="text-[11px] mt-0.5" style="color: var(--tg-theme-hint-color);">{{ t('common.rating_count') }}</div>
            </div>
          </div>
        </div>

        <!-- Edit Button -->
        <button class="btn-primary w-full" @click="router.push('/profile/employer/edit')">
          {{ t('profile.edit_profile') }}
        </button>
      </div>

      <!-- No Employer Profile -->
      <div v-else class="text-center py-12 rounded-2xl" style="background-color: var(--tg-theme-secondary-bg-color);">
        <svg class="w-12 h-12 mx-auto mb-3" style="color: var(--tg-theme-hint-color);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
        </svg>
        <p class="text-[14px] mb-4" style="color: var(--tg-theme-hint-color);">{{ t('profile.no_employer') }}</p>
        <button class="btn-primary" @click="router.push('/profile/employer/edit')">
          {{ t('profile.create_profile') }}
        </button>
      </div>
    </div>

    <!-- Settings Section -->
    <div class="px-5 mt-5">
      <h3 class="text-[14px] font-bold mb-2.5 px-1" style="color: var(--tg-theme-text-color);">{{ t('profile.settings') }}</h3>
      <div class="rounded-2xl overflow-hidden" style="background-color: var(--tg-theme-secondary-bg-color);">
        <!-- Language -->
        <div class="flex items-center justify-between px-4 py-3.5">
          <div class="flex items-center gap-3">
            <svg class="w-5 h-5" style="color: var(--tg-theme-hint-color);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 21l5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 016-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 01-3.827-5.802" />
            </svg>
            <span class="text-[14px]" style="color: var(--tg-theme-text-color);">{{ t('profile.language') }}</span>
          </div>
          <div class="flex gap-1 p-0.5 rounded-xl" style="background-color: var(--tg-theme-bg-color);">
            <button
              class="px-3 py-1.5 rounded-lg text-[12px] font-semibold transition-all"
              :style="currentLang === 'uz'
                ? { backgroundColor: 'var(--tg-theme-button-color)', color: 'var(--tg-theme-button-text-color)' }
                : { color: 'var(--tg-theme-hint-color)' }"
              @click="switchLang('uz')"
            >
              O'zbekcha
            </button>
            <button
              class="px-3 py-1.5 rounded-lg text-[12px] font-semibold transition-all"
              :style="currentLang === 'ru'
                ? { backgroundColor: 'var(--tg-theme-button-color)', color: 'var(--tg-theme-button-text-color)' }
                : { color: 'var(--tg-theme-hint-color)' }"
              @click="switchLang('ru')"
            >
              Русский
            </button>
          </div>
        </div>

        <div class="h-px mx-4" style="background-color: var(--separator-color);"></div>

        <!-- Saved -->
        <button
          class="w-full flex items-center justify-between px-4 py-3.5 active:opacity-70 transition-opacity"
          @click="router.push('/saved')"
        >
          <div class="flex items-center gap-3">
            <svg class="w-5 h-5" style="color: var(--tg-theme-hint-color);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
            </svg>
            <span class="text-[14px]" style="color: var(--tg-theme-text-color);">{{ t('profile.saved') }}</span>
          </div>
          <svg class="w-4 h-4" style="color: var(--tg-theme-hint-color);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
          </svg>
        </button>

        <div class="h-px mx-4" style="background-color: var(--separator-color);"></div>

        <!-- Notifications -->
        <button
          class="w-full flex items-center justify-between px-4 py-3.5 active:opacity-70 transition-opacity"
          @click="router.push('/notifications')"
        >
          <div class="flex items-center gap-3">
            <svg class="w-5 h-5" style="color: var(--tg-theme-hint-color);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
            </svg>
            <span class="text-[14px]" style="color: var(--tg-theme-text-color);">{{ t('profile.notifications') }}</span>
          </div>
          <svg class="w-4 h-4" style="color: var(--tg-theme-hint-color);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
          </svg>
        </button>
      </div>

      <!-- Logout -->
      <button
        class="w-full mt-3 py-3 rounded-2xl text-[14px] font-medium active:opacity-70 transition-opacity"
        style="background-color: rgba(239, 68, 68, 0.1); color: #ef4444;"
        @click="handleLogout"
      >
        {{ t('profile.logout') }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useProfileStore } from '@/stores/profile'
import { useTelegram } from '@/composables/useTelegram'
import { useLocale } from '@/composables/useLocale'
import { formatNumber, getInitial, formatLocation } from '@/utils/formatters'
import LoadingSpinner from '@/components/LoadingSpinner.vue'
import api from '@/utils/api'

const router = useRouter()
const authStore = useAuthStore()
const profileStore = useProfileStore()
const telegram = useTelegram()
const { t, lang, setLang } = useLocale()

const profileType = ref('worker')
const searchStatus = ref('open')
const applicationCount = ref(0)
const vacancyCount = ref(0)
const workerLoading = ref(false)
const employerLoading = ref(false)
const workerProfile = ref(null)
const employerProfile = ref(null)

const currentLang = computed(() => lang.value)

const telegramPhoto = computed(() => {
  return telegram.user.value?.photo_url || null
})

onMounted(async () => {
  // Load worker profile
  workerLoading.value = true
  try {
    await profileStore.fetchWorkerProfile()
    workerProfile.value = profileStore.workerProfile
    searchStatus.value = workerProfile.value?.search_status || 'open'
  } catch (e) {
    // No worker profile
  } finally {
    workerLoading.value = false
  }

  // Load employer profile
  employerLoading.value = true
  try {
    await profileStore.fetchEmployerProfile()
    employerProfile.value = profileStore.employerProfile
  } catch (e) {
    // No employer profile
  } finally {
    employerLoading.value = false
  }

  // Load counts
  try {
    const apps = await api.get('/applications/my')
    const appData = apps.data.applications || apps.data.data || apps.data || []
    applicationCount.value = Array.isArray(appData) ? appData.length : 0
  } catch (e) {}

  try {
    const vacancies = await api.get('/recruiter/vacancies')
    const vacData = vacancies.data.data || vacancies.data || []
    vacancyCount.value = Array.isArray(vacData) ? vacData.length : 0
  } catch (e) {}
})

function getUserInitial() {
  return getInitial(authStore.user?.first_name)
}

function formatExpDate(dateStr) {
  if (!dateStr) return ''
  const [y, m] = dateStr.split('-').map(Number)
  const months = t('edit_profile.months_short')
  return `${months[m - 1]} ${y}`
}

function formatSalaryRange(profile) {
  if (profile?.expected_salary_min && profile?.expected_salary_max) {
    return `${formatNumber(profile.expected_salary_min)} - ${formatNumber(profile.expected_salary_max)} ${t('common.som')}`
  } else if (profile?.expected_salary_min) {
    return `${formatNumber(profile.expected_salary_min)} ${t('common.som_from')}`
  }
  return t('common.not_specified')
}

async function updateSearchStatus() {
  try {
    await profileStore.updateSearchStatus(searchStatus.value)
    telegram.hapticFeedback('soft')
  } catch (error) {
    telegram.showAlert(t('common.error'))
  }
}

async function switchLang(newLang) {
  setLang(newLang)
  telegram.hapticFeedback('soft')

  // Save to server
  try {
    await api.put('/me', { language: newLang })
  } catch (e) {}
}

async function handleLogout() {
  const confirm = await telegram.showConfirm(t('profile.logout_confirm'))
  if (confirm) {
    authStore.logout()
    telegram.close()
  }
}
</script>
