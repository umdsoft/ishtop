<template>
  <AppCard class="shadow-xl">
    <div class="text-center mb-6">
      <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-brand-500 flex items-center justify-center text-white text-2xl font-bold">
        I
      </div>
      <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">
        {{ $t('auth.register') }}
      </h1>
      <p class="mt-2 text-sm text-surface-600 dark:text-surface-400">
        KadrGo Recruiter Panel
      </p>
    </div>

    <form @submit.prevent="handleRegister" class="space-y-4">
      <AppInput
        v-model="form.companyName"
        :label="$t('auth.companyName')"
        placeholder="Grand Hotel"
        required
      />

      <div class="grid grid-cols-2 gap-4">
        <AppInput
          v-model="form.firstName"
          :label="$t('auth.firstName')"
          placeholder="Umidbek"
          required
        />
        <AppInput
          v-model="form.lastName"
          :label="$t('auth.lastName')"
          placeholder="Karimov"
          required
        />
      </div>

      <AppInput
        v-model="form.email"
        type="email"
        :label="$t('auth.email')"
        placeholder="your@email.com"
        required
      />

      <AppInput
        v-model="form.password"
        type="password"
        :label="$t('auth.password')"
        placeholder="••••••••"
        required
      />

      <AppButton
        type="submit"
        variant="primary"
        size="lg"
        full-width
        :loading="loading"
      >
        {{ $t('auth.register') }}
      </AppButton>
    </form>

    <p class="mt-6 text-center text-sm text-surface-600 dark:text-surface-400">
      {{ $t('auth.alreadyHaveAccount') }}
      <router-link to="/auth/login" class="text-brand-500 hover:text-brand-600 font-medium">
        {{ $t('auth.login') }}
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
  companyName: '',
  firstName: '',
  lastName: '',
  email: '',
  password: '',
});

const loading = ref(false);

async function handleRegister() {
  loading.value = true;

  const result = await authStore.register({
    company_name: form.value.companyName,
    first_name: form.value.firstName,
    last_name: form.value.lastName,
    email: form.value.email,
    password: form.value.password,
  });

  loading.value = false;

  if (result.success) {
    toast.success('Ro\'yxatdan muvaffaqiyatli o\'tdingiz!');
    router.push('/dashboard');
  } else {
    toast.error(result.message || 'Registration failed');
  }
}
</script>
