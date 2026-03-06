<template>
  <div class="edit-profile-view p-4 pb-20">
    <!-- Header -->
    <div class="flex items-center gap-3 mb-6">
      <button class="text-tg-hint" @click="router.back()">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </button>
      <h1 class="text-xl font-bold">{{ t('edit_profile.employer_title') }}</h1>
    </div>

    <!-- Loading -->
    <LoadingSpinner v-if="loading" />

    <!-- Form -->
    <form v-else @submit.prevent="handleSave" class="space-y-4">
      <!-- Company Name -->
      <div>
        <label class="block text-sm font-medium mb-1">{{ t('edit_profile.company_name') }}</label>
        <input v-model="form.company_name" type="text" class="input w-full" required />
      </div>

      <!-- Industry -->
      <div>
        <label class="block text-sm font-medium mb-1">{{ t('edit_profile.industry') }}</label>
        <input v-model="form.industry" type="text" class="input w-full" />
      </div>

      <!-- Description -->
      <div>
        <label class="block text-sm font-medium mb-1">{{ t('edit_profile.description') }}</label>
        <textarea v-model="form.description" rows="4" class="input w-full"></textarea>
      </div>

      <!-- Address -->
      <div>
        <label class="block text-sm font-medium mb-1">{{ t('edit_profile.address') }}</label>
        <input v-model="form.address" type="text" class="input w-full" />
      </div>

      <!-- Phone -->
      <div>
        <label class="block text-sm font-medium mb-1">{{ t('edit_profile.phone') }}</label>
        <input v-model="form.phone" type="tel" class="input w-full" />
      </div>

      <!-- Website -->
      <div>
        <label class="block text-sm font-medium mb-1">{{ t('edit_profile.website') }}</label>
        <input v-model="form.website" type="url" class="input w-full" placeholder="https://" />
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
import { ref, onMounted } from 'vue'
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

const form = ref({
  company_name: '',
  industry: '',
  description: '',
  address: '',
  phone: '',
  website: '',
})

onMounted(async () => {
  loading.value = true
  try {
    await profileStore.fetchEmployerProfile()
    if (profileStore.employerProfile) {
      const p = profileStore.employerProfile
      form.value = {
        company_name: p.company_name || '',
        industry: p.industry || '',
        description: p.description || '',
        address: p.address || '',
        phone: p.phone || '',
        website: p.website || '',
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
    await api.put('/profile/employer', form.value)
    await profileStore.fetchEmployerProfile()
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
