<template>
  <div class="bg-white rounded-xl p-5 border border-surface-100">
    <h3 class="font-semibold text-surface-900 mb-4">{{ $t('apply_title') }}</h3>

    <!-- Telegram CTA -->
    <a
      :href="`https://t.me/kadrgobot?start=vacancy_${vacancy.id}`"
      target="_blank"
      class="flex items-center justify-center gap-2 w-full bg-[#2AABEE] hover:bg-[#229ED9] text-white px-4 py-3 rounded-xl font-medium transition-colors text-sm mb-4"
    >
      <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
      {{ $t('apply_via_telegram') }}
    </a>

    <div class="relative text-center my-4">
      <div class="border-t border-surface-200"></div>
      <span class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-white px-3 text-xs text-surface-400">
        {{ $t('apply_via_web') }}
      </span>
    </div>

    <!-- Success message -->
    <div v-if="sent" class="text-center py-4">
      <svg class="w-12 h-12 mx-auto text-green-500 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <p class="text-green-600 font-medium">{{ $t('application_sent') }}</p>
    </div>

    <!-- Form -->
    <form v-else @submit.prevent="submitForm" class="space-y-3">
      <div>
        <input
          v-model="form.name"
          type="text"
          :placeholder="$t('your_name')"
          class="w-full border border-surface-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-400"
          required
        >
      </div>
      <div>
        <input
          v-model="form.phone"
          type="tel"
          :placeholder="$t('phone_format')"
          class="w-full border border-surface-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-400"
          required
        >
      </div>
      <div>
        <textarea
          v-model="form.message"
          :placeholder="$t('message')"
          rows="3"
          class="w-full border border-surface-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-400 resize-none"
        ></textarea>
      </div>
      <button
        type="submit"
        :disabled="submitting"
        class="w-full bg-brand-500 hover:bg-brand-600 disabled:opacity-50 text-white px-4 py-2.5 rounded-xl font-medium transition-colors text-sm"
      >
        {{ submitting ? '...' : $t('send_application') }}
      </button>
      <p v-if="error" class="text-red-500 text-xs text-center">{{ error }}</p>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useApi } from '@website/composables/useApi';

const props = defineProps({
  vacancy: { type: Object, required: true },
});

const { applyToVacancy } = useApi();
const form = reactive({ name: '', phone: '', message: '' });
const submitting = ref(false);
const sent = ref(false);
const error = ref('');

async function submitForm() {
  submitting.value = true;
  error.value = '';
  try {
    await applyToVacancy(props.vacancy.id, form);
    sent.value = true;
  } catch (e) {
    if (e.response?.status === 422) {
      const errors = e.response.data.errors;
      error.value = Object.values(errors).flat().join(', ');
    } else {
      error.value = 'Xatolik yuz berdi. Qayta urinib ko\'ring.';
    }
  } finally {
    submitting.value = false;
  }
}
</script>
