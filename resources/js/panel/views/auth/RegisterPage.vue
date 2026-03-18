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
        {{ $t('auth.register') }}
      </h1>
      <p class="mt-2 text-sm text-surface-600 dark:text-surface-400">
        KadrGo Recruiter Panel
      </p>
    </div>

    <form @submit.prevent="handleRegister" class="space-y-4">
      <div class="grid grid-cols-2 gap-4">
        <AppInput
          v-model="form.firstName"
          :label="$t('auth.firstName')"
          placeholder="Umidbek"
          :error="errors.first_name"
          required
        />
        <AppInput
          v-model="form.lastName"
          :label="$t('auth.lastName')"
          placeholder="Karimov"
          :error="errors.last_name"
        />
      </div>

      <AppInput
        :model-value="form.phone"
        @update:model-value="onPhoneInput"
        type="tel"
        :label="$t('auth.phone')"
        placeholder="+998 90 123 45 67"
        :error="errors.phone"
        required
      />

      <AppInput
        v-model="form.login"
        :label="$t('auth.loginField')"
        placeholder="umidbek_k"
        :error="errors.login"
        required
      />

      <AppInput
        v-model="form.password"
        type="password"
        :label="$t('auth.password')"
        placeholder="••••••••"
        :error="errors.password"
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
import { ref, nextTick } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import { toast } from 'vue-sonner';
import AppCard from '../../components/ui/AppCard.vue';
import AppInput from '../../components/ui/AppInput.vue';
import AppButton from '../../components/ui/AppButton.vue';

const router = useRouter();
const authStore = useAuthStore();

const form = ref({
  firstName: '',
  lastName: '',
  phone: '+998 ',
  login: '',
  password: '',
});

const loading = ref(false);
const errors = ref({});

function onPhoneInput(val) {
  let digits = val.replace(/\D/g, '');
  if (!digits.startsWith('998')) {
    digits = '998';
  }
  digits = digits.slice(0, 12);

  let formatted = '+998';
  const rest = digits.slice(3);
  if (rest.length > 0) formatted += ' ' + rest.slice(0, 2);
  if (rest.length > 2) formatted += ' ' + rest.slice(2, 5);
  if (rest.length > 5) formatted += ' ' + rest.slice(5, 7);
  if (rest.length > 7) formatted += ' ' + rest.slice(7, 9);

  const oldVal = form.value.phone;
  if (formatted === oldVal) {
    form.value.phone = '';
    nextTick(() => { form.value.phone = formatted; });
  } else {
    form.value.phone = formatted;
  }
}

async function handleRegister() {
  loading.value = true;
  errors.value = {};

  const result = await authStore.register({
    first_name: form.value.firstName,
    last_name: form.value.lastName,
    phone: form.value.phone.replace(/\s/g, ''),
    login: form.value.login,
    password: form.value.password,
  });

  loading.value = false;

  if (result.success) {
    toast.success('Ro\'yxatdan muvaffaqiyatli o\'tdingiz!');
    router.push('/dashboard');
  } else {
    // Show field-level errors
    if (result.errors && Object.keys(result.errors).length > 0) {
      for (const [field, messages] of Object.entries(result.errors)) {
        errors.value[field] = Array.isArray(messages) ? messages[0] : messages;
      }
      toast.error('Ma\'lumotlarni tekshiring');
    } else {
      toast.error(result.message || 'Ro\'yxatdan o\'tishda xatolik');
    }
  }
}
</script>
