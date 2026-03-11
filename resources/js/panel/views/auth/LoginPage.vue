<template>
  <AppCard class="shadow-xl">
    <div class="text-center mb-6">
      <div class="flex items-center justify-center gap-2.5 mb-4">
        <div class="w-10 h-10 bg-brand-500 rounded-xl flex items-center justify-center flex-shrink-0">
          <svg width="22" height="22" viewBox="0 0 48 48" fill="none">
            <path d="M15 14L15 34" stroke="white" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M15 24L27 14" stroke="white" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M15 24L27 34" stroke="white" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M30 17L35 17L35 31L30 31" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" opacity="0.7"/>
          </svg>
        </div>
        <div class="flex flex-col leading-none">
          <span class="text-[13px] font-extrabold tracking-[0.5px] text-brand-500">KADR</span>
          <span class="text-[13px] font-black tracking-[1px] text-accent-500 flex items-center">GO<span class="w-[5px] h-[5px] rounded-full bg-accent-500 ml-1"></span></span>
        </div>
      </div>
      <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">
        {{ $t('auth.login') }}
      </h1>
      <p class="mt-2 text-sm text-surface-600 dark:text-surface-400">
        KadrGo Recruiter Panel ga xush kelibsiz
      </p>
    </div>

    <form @submit.prevent="handleLogin" class="space-y-4">
      <AppInput
        v-model="form.login"
        type="text"
        :label="$t('auth.loginField')"
        placeholder="Username"
        :error="errors.login"
        required
        autocomplete="username"
      />
      <AppInput
        v-model="form.password"
        type="password"
        :label="$t('auth.password')"
        placeholder="••••••••"
        :error="errors.password"
        required
        autocomplete="current-password"
      />
      <AppButton
        type="submit"
        variant="primary"
        size="lg"
        full-width
        :loading="loading"
      >
        {{ $t('auth.login') }}
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
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import { toast } from 'vue-sonner';
import AppCard from '../../components/ui/AppCard.vue';
import AppInput from '../../components/ui/AppInput.vue';
import AppButton from '../../components/ui/AppButton.vue';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

const loading = ref(false);
const errors = ref({});

const form = ref({
  login: '',
  password: '',
});

async function handleLogin() {
  loading.value = true;
  errors.value = {};

  const result = await authStore.login({
    login: form.value.login,
    password: form.value.password,
  });

  loading.value = false;

  if (result.success) {
    toast.success('Muvaffaqiyatli kirdingiz!');
    const redirect = route.query.redirect;
    router.replace(redirect || '/dashboard');
  } else {
    toast.error(result.message);
    errors.value = result.errors || {};
  }
}
</script>
