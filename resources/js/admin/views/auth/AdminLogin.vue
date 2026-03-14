<template>
  <div>
    <!-- Logo -->
    <div class="text-center mb-8">
      <div class="w-16 h-16 mx-auto rounded-2xl bg-brand-500 flex items-center justify-center mb-4">
        <svg width="32" height="32" viewBox="0 0 48 48" fill="none">
          <path d="M15 14L15 34" stroke="white" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M15 24L27 14" stroke="white" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M15 24L27 34" stroke="white" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M30 17L35 17L35 31L30 31" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" opacity="0.7"/>
        </svg>
      </div>
      <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">{{ $t('auth.loginTitle') }}</h1>
      <p class="text-surface-500 dark:text-surface-400 mt-1">{{ $t('auth.loginSubtitle') }}</p>
    </div>

    <!-- Login Form -->
    <div class="bg-surface-0 dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 shadow-xl p-6">
      <form @submit.prevent="handleLogin" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">
            {{ $t('auth.username') }}
          </label>
          <input
            v-model="form.login"
            type="text"
            required
            autocomplete="username"
            class="w-full px-4 py-2.5 bg-surface-50 dark:bg-surface-800 border border-surface-300 dark:border-surface-700 rounded-xl text-surface-900 dark:text-surface-100 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-colors"
            :placeholder="$t('auth.username')"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">
            {{ $t('auth.password') }}
          </label>
          <input
            v-model="form.password"
            type="password"
            required
            autocomplete="current-password"
            class="w-full px-4 py-2.5 bg-surface-50 dark:bg-surface-800 border border-surface-300 dark:border-surface-700 rounded-xl text-surface-900 dark:text-surface-100 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-colors"
            :placeholder="$t('auth.password')"
          />
        </div>

        <div v-if="errorMessage" class="p-3 bg-danger-50 dark:bg-danger-950/30 border border-danger-200 dark:border-danger-800 rounded-xl text-sm text-danger-700 dark:text-danger-400">
          {{ errorMessage }}
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="w-full py-2.5 px-4 bg-brand-500 hover:bg-brand-600 disabled:opacity-50 text-white font-medium rounded-xl transition-colors flex items-center justify-center gap-2"
        >
          <svg v-if="loading" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
          </svg>
          {{ $t('auth.loginButton') }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../../stores/auth';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

const form = reactive({
  login: '',
  password: '',
});

const loading = ref(false);
const errorMessage = ref('');

async function handleLogin() {
  loading.value = true;
  errorMessage.value = '';

  const result = await authStore.login(form);

  if (result.success) {
    const redirect = route.query.redirect || '/';
    router.push(redirect);
  } else {
    errorMessage.value = result.message;
  }

  loading.value = false;
}
</script>
