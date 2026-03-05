<template>
  <AppCard class="shadow-xl">
    <div class="text-center mb-6">
      <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-brand-500 flex items-center justify-center text-white text-2xl font-bold">
        I
      </div>
      <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">
        Kirish
      </h1>
      <p class="mt-2 text-sm text-surface-600 dark:text-surface-400">
        IshTop Recruiter Panel ga xush kelibsiz
      </p>
    </div>

    <!-- Phone + OTP flow -->
    <template v-if="authMethod === 'phone'">
      <!-- Step 1: Phone number -->
      <form v-if="step === 'phone'" @submit.prevent="handleSendOtp" class="space-y-4">
        <AppInput
          v-model="form.phone"
          type="tel"
          label="Telefon raqam"
          placeholder="+998901234567"
          :error="errors.phone"
          required
        />

        <AppButton
          type="submit"
          variant="primary"
          size="lg"
          full-width
          :loading="loading"
        >
          Kod yuborish
        </AppButton>

        <p class="text-xs text-center text-surface-500 dark:text-surface-400">
          Tasdiqlash kodi Telegram botga yuboriladi
        </p>
      </form>

      <!-- Step 2: OTP code -->
      <form v-else-if="step === 'otp'" @submit.prevent="handleVerifyOtp" class="space-y-4">
        <div class="text-center mb-2">
          <p class="text-sm text-surface-600 dark:text-surface-400">
            <span class="font-medium text-surface-900 dark:text-surface-100">{{ form.phone }}</span>
            raqamiga bog'langan Telegram botga kod yuborildi
          </p>
        </div>

        <AppInput
          v-model="form.code"
          type="text"
          label="Tasdiqlash kodi"
          placeholder="123456"
          :error="errors.code"
          required
          autocomplete="one-time-code"
        />

        <AppButton
          type="submit"
          variant="primary"
          size="lg"
          full-width
          :loading="loading"
        >
          Tasdiqlash
        </AppButton>

        <div class="flex items-center justify-between">
          <button
            type="button"
            class="text-sm text-surface-500 hover:text-surface-700 dark:hover:text-surface-300"
            @click="step = 'phone'"
          >
            Raqamni o'zgartirish
          </button>
          <button
            type="button"
            class="text-sm text-brand-500 hover:text-brand-600 disabled:opacity-50"
            :disabled="resendTimer > 0 || loading"
            @click="handleSendOtp"
          >
            {{ resendTimer > 0 ? `Qayta yuborish (${resendTimer}s)` : 'Qayta yuborish' }}
          </button>
        </div>
      </form>
    </template>

    <!-- Login/Password flow -->
    <form v-else @submit.prevent="handlePasswordLogin" class="space-y-4">
      <AppInput
        v-model="form.login"
        type="text"
        label="Login"
        placeholder="Username yoki email"
        :error="errors.login"
        required
        autocomplete="username"
      />
      <AppInput
        v-model="form.password"
        type="password"
        label="Parol"
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
        Kirish
      </AppButton>
    </form>

    <!-- Divider -->
    <div class="relative my-6">
      <div class="absolute inset-0 flex items-center">
        <div class="w-full border-t border-surface-200 dark:border-surface-800"></div>
      </div>
      <div class="relative flex justify-center text-sm">
        <span class="px-2 bg-surface-0 dark:bg-surface-900 text-surface-500">yoki</span>
      </div>
    </div>

    <!-- Switch method button -->
    <AppButton
      variant="outline"
      size="lg"
      full-width
      @click="switchAuthMethod"
    >
      {{ authMethod === 'phone' ? 'Login/Parol bilan kirish' : 'Telefon raqam bilan kirish' }}
    </AppButton>

    <p class="mt-6 text-center text-sm text-surface-600 dark:text-surface-400">
      Akkauntingiz yo'qmi?
      <router-link to="/auth/register" class="text-brand-500 hover:text-brand-600 font-medium">
        Ro'yxatdan o'tish
      </router-link>
    </p>
  </AppCard>
</template>

<script setup>
import { ref, onUnmounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import { toast } from 'vue-sonner';
import AppCard from '../../components/ui/AppCard.vue';
import AppInput from '../../components/ui/AppInput.vue';
import AppButton from '../../components/ui/AppButton.vue';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

function redirectAfterLogin() {
  const redirect = route.query.redirect;
  return router.replace(redirect || '/dashboard');
}

const authMethod = ref('phone'); // 'phone' | 'password'
const step = ref('phone'); // 'phone' | 'otp' (for phone auth method)
const loading = ref(false);
const errors = ref({});
const resendTimer = ref(0);
let timerInterval = null;

const form = ref({
  phone: '+998',
  code: '',
  login: '',
  password: '',
});

function switchAuthMethod() {
  authMethod.value = authMethod.value === 'phone' ? 'password' : 'phone';
  errors.value = {};
}

function startResendTimer() {
  resendTimer.value = 60;
  timerInterval = setInterval(() => {
    resendTimer.value--;
    if (resendTimer.value <= 0) {
      clearInterval(timerInterval);
    }
  }, 1000);
}

async function handleSendOtp() {
  if (!form.value.phone || form.value.phone.length < 13) {
    errors.value = { phone: 'To\'liq telefon raqam kiriting (+998XXXXXXXXX)' };
    return;
  }

  loading.value = true;
  errors.value = {};

  const result = await authStore.sendOtp(form.value.phone);

  loading.value = false;

  if (result.success) {
    step.value = 'otp';
    form.value.code = '';
    startResendTimer();
    toast.success('Kod Telegram botga yuborildi!');

    // Dev mode: auto-fill code
    if (result.code) {
      form.value.code = result.code;
    }
  } else {
    toast.error(result.message);
    errors.value = { phone: result.message };
  }
}

async function handleVerifyOtp() {
  if (!form.value.code || form.value.code.length !== 6) {
    errors.value = { code: '6 xonali kodni kiriting' };
    return;
  }

  loading.value = true;
  errors.value = {};

  const result = await authStore.verifyOtp(form.value.phone, form.value.code);

  loading.value = false;

  if (result.success) {
    toast.success('Muvaffaqiyatli kirdingiz!');
    await redirectAfterLogin();
  } else {
    toast.error(result.message);
    errors.value = { code: result.message };
  }
}

async function handlePasswordLogin() {
  loading.value = true;
  errors.value = {};

  const result = await authStore.login({
    login: form.value.login,
    password: form.value.password,
  });

  loading.value = false;

  if (result.success) {
    toast.success('Muvaffaqiyatli kirdingiz!');
    await redirectAfterLogin();
  } else {
    toast.error(result.message);
    errors.value = result.errors || {};
  }
}

onUnmounted(() => {
  if (timerInterval) clearInterval(timerInterval);
});
</script>
