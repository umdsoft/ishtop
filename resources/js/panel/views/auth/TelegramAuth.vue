<template>
  <AppCard class="shadow-xl">
    <div class="text-center mb-6">
      <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-[#2AABEE] flex items-center justify-center text-white">
        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
          <path d="m12 24c6.629 0 12-5.371 12-12s-5.371-12-12-12-12 5.371-12 12 5.371 12 12 12zm-6.509-12.26 11.57-4.461c.537-.194 1.006.131.832.943l.001-.001-1.97 9.281c-.146.658-.537.818-1.084.508l-3-2.211-1.447 1.394c-.16.16-.295.295-.605.295l.213-3.053 5.56-5.023c.242-.213-.054-.333-.373-.121l-6.871 4.326-2.962-.924c-.643-.204-.657-.643.136-.953z"/>
        </svg>
      </div>
      <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">
        Telegram orqali kirish
      </h1>
      <p class="mt-2 text-sm text-surface-600 dark:text-surface-400">
        Telegram akkauntingiz orqali tizimga kiring
      </p>
    </div>

    <div v-if="error" class="mb-4 p-3 rounded-lg bg-danger-50 dark:bg-danger-900/20 text-danger-600 dark:text-danger-400 text-sm">
      {{ error }}
    </div>

    <div v-if="loading" class="flex flex-col items-center py-8">
      <svg class="animate-spin h-8 w-8 text-brand-500 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
      <p class="text-surface-500">Telegram bilan bog'lanmoqda...</p>
    </div>

    <div v-else class="space-y-4">
      <div ref="telegramWidget" class="flex justify-center"></div>

      <div v-if="!botUsername" class="text-center py-4">
        <p class="text-surface-500 dark:text-surface-400 text-sm">
          Telegram bot sozlanmagan. Administrator bilan bog'laning.
        </p>
      </div>
    </div>

    <div class="mt-6 text-center">
      <router-link to="/auth/login" class="text-sm text-brand-500 hover:text-brand-600 font-medium">
        Email/Username bilan kirish
      </router-link>
    </div>
  </AppCard>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import { toast } from 'vue-sonner';
import AppCard from '../../components/ui/AppCard.vue';
import axios from 'axios';

const router = useRouter();
const authStore = useAuthStore();
const telegramWidget = ref(null);
const loading = ref(false);
const error = ref('');
const botUsername = ref('');

async function fetchBotUsername() {
  try {
    const response = await axios.get('/api/recruiter/telegram-bot-info');
    botUsername.value = response.data.bot_username;
  } catch (e) {
    botUsername.value = '';
  }
}

function initTelegramWidget() {
  if (!botUsername.value || !telegramWidget.value) return;

  const script = document.createElement('script');
  script.src = 'https://telegram.org/js/telegram-widget.js?22';
  script.setAttribute('data-telegram-login', botUsername.value);
  script.setAttribute('data-size', 'large');
  script.setAttribute('data-radius', '8');
  script.setAttribute('data-onauth', 'onTelegramAuth(user)');
  script.setAttribute('data-request-access', 'write');
  script.async = true;

  telegramWidget.value.appendChild(script);
}

async function handleTelegramAuth(telegramUser) {
  loading.value = true;
  error.value = '';

  try {
    const response = await axios.post('/api/recruiter/telegram-login', telegramUser);

    authStore.token = response.data.token;
    authStore.user = response.data.user;
    localStorage.setItem('ishtop-token', response.data.token);
    axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.token}`;

    toast.success('Muvaffaqiyatli kirdingiz!');
    router.push('/dashboard');
  } catch (e) {
    error.value = e.response?.data?.message || 'Telegram orqali kirish amalga oshmadi';
  } finally {
    loading.value = false;
  }
}

onMounted(async () => {
  window.onTelegramAuth = handleTelegramAuth;

  await fetchBotUsername();
  initTelegramWidget();
});
</script>
