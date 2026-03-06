<template>
  <div class="edit-profile-view p-4 pb-20">
    <!-- Header -->
    <div class="flex items-center gap-3 mb-6">
      <button class="text-tg-hint" @click="router.back()">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </button>
      <h1 class="text-xl font-bold">{{ t('edit_profile.worker_title') }}</h1>
    </div>

    <!-- Loading -->
    <LoadingSpinner v-if="loading" />

    <!-- Form -->
    <form v-else @submit.prevent="handleSave" class="space-y-4">
      <!-- Full Name -->
      <div>
        <label class="block text-sm font-medium mb-1">{{ t('edit_profile.full_name') }}</label>
        <input v-model="form.full_name" type="text" class="input w-full" required />
      </div>

      <!-- Specialty -->
      <div>
        <label class="block text-sm font-medium mb-1">{{ t('edit_profile.specialty') }}</label>
        <input v-model="form.specialty" type="text" class="input w-full" />
      </div>

      <!-- City -->
      <div>
        <label class="block text-sm font-medium mb-1">{{ t('edit_profile.city') }}</label>
        <select v-model="form.city" class="input w-full">
          <option value="">{{ t('common.not_specified') }}</option>
          <option v-for="city in cities" :key="city.name_uz" :value="city.name_uz">
            {{ city.name_uz }}
          </option>
        </select>
      </div>

      <!-- District -->
      <div>
        <label class="block text-sm font-medium mb-1">{{ t('edit_profile.district') }}</label>
        <input v-model="form.district" type="text" class="input w-full" />
      </div>

      <!-- Experience -->
      <div>
        <label class="block text-sm font-medium mb-1">{{ t('edit_profile.experience_years') }}</label>
        <input v-model.number="form.experience_years" type="number" min="0" max="50" class="input w-full" />
      </div>

      <!-- Salary Range -->
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-sm font-medium mb-1">{{ t('edit_profile.expected_salary_min') }}</label>
          <input v-model.number="form.expected_salary_min" type="number" class="input w-full" />
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">{{ t('edit_profile.expected_salary_max') }}</label>
          <input v-model.number="form.expected_salary_max" type="number" class="input w-full" />
        </div>
      </div>

      <!-- Work Type -->
      <div>
        <label class="block text-sm font-medium mb-1">{{ t('edit_profile.work_type') }}</label>
        <div class="space-y-2">
          <label v-for="wt in workTypes" :key="wt.value" class="flex items-center gap-2 cursor-pointer">
            <input
              type="checkbox"
              :value="wt.value"
              v-model="form.preferred_work_types"
              class="rounded border-gray-300"
            />
            <span class="text-sm">{{ wt.label }}</span>
          </label>
        </div>
      </div>

      <!-- Bio -->
      <div>
        <label class="block text-sm font-medium mb-1">{{ t('edit_profile.bio') }}</label>
        <textarea v-model="form.bio" rows="4" class="input w-full"></textarea>
      </div>

      <!-- Submit -->
      <button
        type="submit"
        class="btn-primary w-full"
        :disabled="saving"
      >
        {{ saving ? t('common.loading') : t('common.save') }}
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useProfileStore } from '@/stores/profile'
import { useTelegram } from '@/composables/useTelegram'
import { useLocale } from '@/composables/useLocale'
import LoadingSpinner from '@/components/LoadingSpinner.vue'
import api from '@/utils/api'

const router = useRouter()
const profileStore = useProfileStore()
const telegram = useTelegram()
const { t } = useLocale()

const loading = ref(false)
const saving = ref(false)
const cities = ref([])

const form = ref({
  full_name: '',
  specialty: '',
  city: '',
  district: '',
  experience_years: 0,
  expected_salary_min: null,
  expected_salary_max: null,
  preferred_work_types: [],
  bio: '',
})

const workTypes = computed(() => [
  { value: 'full_time', label: t('work_type.full_time') },
  { value: 'part_time', label: t('work_type.part_time') },
  { value: 'remote', label: t('work_type.remote') },
  { value: 'temporary', label: t('work_type.temporary') },
])

onMounted(async () => {
  loading.value = true
  try {
    // Load cities
    const cityRes = await api.get('/cities')
    cities.value = cityRes.data.cities || []

    // Load existing profile
    await profileStore.fetchWorkerProfile()
    if (profileStore.workerProfile) {
      const p = profileStore.workerProfile
      form.value = {
        full_name: p.full_name || '',
        specialty: p.specialty || '',
        city: p.city || '',
        district: p.district || '',
        experience_years: p.experience_years || 0,
        expected_salary_min: p.expected_salary_min || null,
        expected_salary_max: p.expected_salary_max || null,
        preferred_work_types: p.preferred_work_types || [],
        bio: p.bio || '',
      }
    }
  } catch (error) {
    console.error('Failed to load profile:', error)
  } finally {
    loading.value = false
  }
})

async function handleSave() {
  saving.value = true
  try {
    await api.put('/profile/worker', form.value)
    await profileStore.fetchWorkerProfile()
    telegram.showAlert(t('edit_profile.save_success'))
    telegram.hapticFeedback('medium')
    router.back()
  } catch (error) {
    telegram.showAlert(error.response?.data?.message || t('edit_profile.save_error'))
  } finally {
    saving.value = false
  }
}
</script>
