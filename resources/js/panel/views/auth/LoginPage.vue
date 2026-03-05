<template>
  <AppCard class="shadow-xl">
    <div class="text-center mb-6">
      <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-brand-500 flex items-center justify-center text-white text-2xl font-bold">
        I
      </div>
      <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">
        {{ $t('auth.login') }}
      </h1>
      <p class="mt-2 text-sm text-surface-600 dark:text-surface-400">
        IshTop Recruiter Panel ga xush kelibsiz
      </p>
    </div>

    <form @submit.prevent="handleLogin" class="space-y-4">
      <AppInput
        v-model="form.email"
        type="email"
        :label="$t('auth.email')"
        :placeholder="'your@email.com'"
        :error="errors.email"
        required
        autocomplete="email"
      />

      <AppInput
        v-model="form.password"
        type="password"
        :label="$t('auth.password')"
        :placeholder="'••••••••'"
        :error="errors.password"
        required
        autocomplete="current-password"
      />

      <div class="flex items-center justify-between">
        <label class="flex items-center gap-2 cursor-pointer">
          <input
            v-model="form.remember"
            type="checkbox"
            class="rounded border-surface-300 text-brand-500 focus:ring-brand-500"
          />
          <span class="text-sm text-surface-700 dark:text-surface-300">
            {{ $t('auth.rememberMe') }}
          </span>
        </label>

        <a href="#" class="text-sm text-brand-500 hover:text-brand-600">
          {{ $t('auth.forgotPassword') }}
        </a>
      </div>

      <AppButton
        type="submit"
        variant="primary"
        size="lg"
        full-width
        :loading="loading"
      >
        {{ $t('auth.login') }}
      </AppButton>

      <div class="relative my-6">
        <div class="absolute inset-0 flex items-center">
          <div class="w-full border-t border-surface-200 dark:border-surface-800"></div>
        </div>
        <div class="relative flex justify-center text-sm">
          <span class="px-2 bg-surface-0 dark:bg-surface-900 text-surface-500">yoki</span>
        </div>
      </div>

      <AppButton
        variant="outline"
        size="lg"
        full-width
        @click="loginWithTelegram"
      >
        <template #icon-left>
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
            <path d="m12 24c6.629 0 12-5.371 12-12s-5.371-12-12-12-12 5.371-12 12 5.371 12 12 12zm-6.509-12.26 11.57-4.461c.537-.194 1.006.131.832.943l.001-.001-1.97 9.281c-.146.658-.537.818-1.084.508l-3-2.211-1.447 1.394c-.16.16-.295.295-.605.295l.213-3.053 5.56-5.023c.242-.213-.054-.333-.373-.121l-6.871 4.326-2.962-.924c-.643-.204-.657-.643.136-.953z"/>
          </svg>
        </template>
        {{ $t('auth.loginWithTelegram') }}
      </AppButton>
    </form>

    <p class="mt-6 text-center text-sm text-surface-600 dark:text-surface-400">
      {{ $t('auth.dontHaveAccount') }}
      <router-link to="/auth/register" class="text-brand-500 hover:text-brand-600 font-medium">
        {{ $t('auth.register') }}
      </router-link>
    </p>
  </AppCard>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import { toast } from 'vue-sonner';
import AppCard from '../../components/ui/AppCard.vue';
import AppInput from '../../components/ui/AppInput.vue';
import AppButton from '../../components/ui/AppButton.vue';

const router = useRouter();
const authStore = useAuthStore();

const form = ref({
  email: '',
  password: '',
  remember: false,
});

const errors = ref({});
const loading = ref(false);

async function handleLogin() {
  loading.value = true;
  errors.value = {};

  const result = await authStore.login({
    email: form.value.email,
    password: form.value.password,
  });

  loading.value = false;

  if (result.success) {
    toast.success('Muvaffaqiyatli kirdingiz!');
    router.push('/dashboard');
  } else {
    toast.error(result.message || 'Login failed');
    errors.value = result.errors || {};
  }
}

function loginWithTelegram() {
  router.push('/auth/telegram');
}
</script>
