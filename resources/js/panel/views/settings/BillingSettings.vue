<template>
  <div class="space-y-6">
    <!-- Header -->
    <div>
      <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">To'lov va obuna</h1>
      <p class="text-surface-600 dark:text-surface-400 mt-1">Obuna rejangizni boshqaring</p>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex items-center justify-center py-20">
      <AppLoadingSpinner size="lg" text="Yuklanmoqda..." />
    </div>

    <template v-else>
      <!-- Current Subscription -->
      <AppCard>
        <div class="flex items-start justify-between">
          <div>
            <div class="flex items-center gap-3">
              <h2 class="text-lg font-semibold text-surface-900 dark:text-surface-100">
                Joriy obuna
              </h2>
              <AppBadge :variant="currentSubscription ? 'success' : 'default'">
                {{ currentSubscription ? 'Faol' : 'Bepul' }}
              </AppBadge>
            </div>

            <div v-if="currentSubscription" class="mt-3 space-y-2">
              <div class="flex items-center gap-2">
                <SparklesIcon class="h-5 w-5 text-brand-500" />
                <span class="text-surface-900 dark:text-surface-100 font-medium">
                  {{ planLabel }}
                </span>
              </div>
              <p class="text-sm text-surface-600 dark:text-surface-400">
                Muddati: {{ formatDate(currentSubscription.expires_at) }}
                <span v-if="daysLeft > 0" class="ml-1 text-brand-600 dark:text-brand-400 font-medium">
                  ({{ daysLeft }} kun qoldi)
                </span>
                <span v-else class="ml-1 text-danger-600 dark:text-danger-400 font-medium">
                  (muddati tugagan)
                </span>
              </p>
              <p class="text-sm text-surface-500 dark:text-surface-400">
                Narxi: {{ formatPrice(currentSubscription.price) }} / oy
              </p>
            </div>

            <div v-else class="mt-3">
              <p class="text-surface-600 dark:text-surface-400">
                Hozirda bepul rejada foydalanmoqdasiz. Obuna bo'lib ko'proq imkoniyatlarga ega bo'ling.
              </p>
              <div class="flex items-center gap-1 mt-2 text-sm text-surface-500">
                <ExclamationCircleIcon class="h-4 w-4" />
                <span>Bepul rejada faqat 1 ta vakansiya e'lon qilish mumkin</span>
              </div>
            </div>
          </div>

          <div v-if="currentSubscription" class="shrink-0">
            <AppButton
              variant="outline"
              size="sm"
              @click="showCancelDialog = true"
            >
              Bekor qilish
            </AppButton>
          </div>
        </div>

        <!-- Balance -->
        <div class="mt-4 pt-4 border-t border-surface-200 dark:border-surface-700 flex items-center justify-between">
          <div>
            <p class="text-sm text-surface-600 dark:text-surface-400">Hisobingizdagi balans</p>
            <p class="text-xl font-bold text-surface-900 dark:text-surface-100 mt-0.5">
              {{ formatPrice(balance) }}
            </p>
          </div>
          <AppButton variant="outline" size="sm" @click="showTopupDialog = true">
            <template #icon-left>
              <PlusIcon class="h-4 w-4" />
            </template>
            Balansni to'ldirish
          </AppButton>
        </div>
      </AppCard>

      <!-- Usage & Limits -->
      <AppCard v-if="limits">
        <template #header>
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-surface-900 dark:text-surface-100">Foydalanish limiti</h2>
            <AppBadge variant="info" size="sm">{{ planLabel }}</AppBadge>
          </div>
        </template>

        <div class="space-y-5">
          <!-- Vacancies -->
          <div>
            <div class="flex items-center justify-between mb-1.5">
              <span class="text-sm font-medium text-surface-700 dark:text-surface-300">Vakansiyalar</span>
              <span class="text-sm text-surface-600 dark:text-surface-400">
                {{ usage.vacancies }} / {{ limits.max_vacancies ?? '∞' }}
              </span>
            </div>
            <div class="w-full h-2 bg-surface-100 dark:bg-surface-800 rounded-full overflow-hidden">
              <div
                :class="[
                  'h-full rounded-full transition-all',
                  getUsageBarColor('vacancies'),
                ]"
                :style="{ width: getUsagePercent('vacancies') + '%' }"
              />
            </div>
            <p v-if="limits.max_vacancies && usage.vacancies >= limits.max_vacancies" class="text-xs text-danger-600 dark:text-danger-400 mt-1">
              Limit tugadi. Obunangizni yangilang.
            </p>
          </div>

          <!-- Questionnaires -->
          <div>
            <div class="flex items-center justify-between mb-1.5">
              <span class="text-sm font-medium text-surface-700 dark:text-surface-300">Savolnomalar</span>
              <span class="text-sm text-surface-600 dark:text-surface-400">
                {{ usage.questionnaires }} / {{ limits.max_questionnaires ?? '∞' }}
              </span>
            </div>
            <div class="w-full h-2 bg-surface-100 dark:bg-surface-800 rounded-full overflow-hidden">
              <div
                :class="[
                  'h-full rounded-full transition-all',
                  getUsageBarColor('questionnaires'),
                ]"
                :style="{ width: getUsagePercent('questionnaires') + '%' }"
              />
            </div>
          </div>

          <!-- Template Messages -->
          <div>
            <div class="flex items-center justify-between mb-1.5">
              <span class="text-sm font-medium text-surface-700 dark:text-surface-300">Xabar shablonlari</span>
              <span class="text-sm text-surface-600 dark:text-surface-400">
                {{ usage.template_messages }} / {{ limits.max_template_messages ?? '∞' }}
              </span>
            </div>
            <div class="w-full h-2 bg-surface-100 dark:bg-surface-800 rounded-full overflow-hidden">
              <div
                :class="[
                  'h-full rounded-full transition-all',
                  getUsageBarColor('template_messages'),
                ]"
                :style="{ width: getUsagePercent('template_messages') + '%' }"
              />
            </div>
          </div>

          <!-- Feature Flags -->
          <div class="pt-3 border-t border-surface-200 dark:border-surface-700">
            <p class="text-sm font-medium text-surface-700 dark:text-surface-300 mb-3">Imkoniyatlar</p>
            <div class="grid grid-cols-2 gap-2">
              <div
                v-for="feat in featureFlags"
                :key="feat.key"
                class="flex items-center gap-2 text-sm"
              >
                <CheckCircleIcon v-if="limits[feat.key]" class="h-4 w-4 text-success-500 shrink-0" />
                <XCircleIcon v-else class="h-4 w-4 text-surface-300 dark:text-surface-600 shrink-0" />
                <span :class="limits[feat.key] ? 'text-surface-700 dark:text-surface-300' : 'text-surface-400 dark:text-surface-500'">
                  {{ feat.label }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </AppCard>

      <!-- Plans -->
      <div>
        <h2 class="text-lg font-semibold text-surface-900 dark:text-surface-100 mb-4">Obuna rejalari</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div
            v-for="plan in recruiterPlans"
            :key="plan.value"
            :class="[
              'relative rounded-xl border-2 p-5 transition-all',
              plan.popular
                ? 'border-brand-500 dark:border-brand-400 shadow-lg shadow-brand-500/10'
                : 'border-surface-200 dark:border-surface-700',
              currentPlan === plan.value
                ? 'bg-brand-50/50 dark:bg-brand-950/20'
                : 'bg-surface-0 dark:bg-surface-900',
            ]"
          >
            <!-- Popular badge -->
            <div
              v-if="plan.popular"
              class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-0.5 bg-brand-500 text-white text-xs font-bold rounded-full"
            >
              Mashhur
            </div>

            <div class="text-center mb-4">
              <h3 class="text-lg font-bold text-surface-900 dark:text-surface-100">
                {{ plan.label }}
              </h3>
              <div class="mt-2">
                <span class="text-3xl font-bold text-surface-900 dark:text-surface-100">
                  {{ formatPriceShort(plan.price) }}
                </span>
                <span class="text-surface-500 dark:text-surface-400 text-sm"> / oy</span>
              </div>
            </div>

            <ul class="space-y-2 mb-6">
              <li
                v-for="(feature, i) in plan.features"
                :key="i"
                class="flex items-start gap-2 text-sm"
              >
                <CheckIcon class="h-4 w-4 text-success-500 shrink-0 mt-0.5" />
                <span class="text-surface-700 dark:text-surface-300">{{ feature }}</span>
              </li>
            </ul>

            <AppButton
              v-if="currentPlan === plan.value"
              variant="outline"
              class="w-full"
              disabled
            >
              Joriy reja
            </AppButton>
            <AppButton
              v-else
              :variant="plan.popular ? 'primary' : 'outline'"
              class="w-full"
              @click="selectPlan(plan)"
            >
              Tanlash
            </AppButton>
          </div>
        </div>
      </div>

      <!-- Payment History -->
      <AppCard>
        <template #header>
          <h2 class="text-lg font-semibold text-surface-900 dark:text-surface-100">To'lov tarixi</h2>
        </template>

        <div v-if="paymentsLoading" class="flex justify-center py-8">
          <AppLoadingSpinner text="Yuklanmoqda..." />
        </div>

        <div v-else-if="payments.length === 0" class="text-center py-8">
          <BanknotesIcon class="h-12 w-12 mx-auto text-surface-400 dark:text-surface-500 mb-3" />
          <p class="text-surface-500 dark:text-surface-400">To'lovlar tarixi bo'sh</p>
        </div>

        <div v-else class="divide-y divide-surface-200 dark:divide-surface-700 -mx-6 -my-4">
          <div
            v-for="payment in payments"
            :key="payment.id"
            class="flex items-center justify-between px-6 py-3"
          >
            <div class="flex items-center gap-3">
              <div
                :class="[
                  'w-9 h-9 rounded-lg flex items-center justify-center shrink-0',
                  getPaymentTypeClasses(payment.type),
                ]"
              >
                <component :is="getPaymentTypeIcon(payment.type)" class="h-4 w-4" />
              </div>
              <div>
                <p class="text-sm font-medium text-surface-900 dark:text-surface-100">
                  {{ getPaymentTypeLabel(payment.type) }}
                </p>
                <p class="text-xs text-surface-500 dark:text-surface-400">
                  {{ formatDateTime(payment.created_at) }}
                  &bull; {{ getPaymentMethodLabel(payment.method) }}
                </p>
              </div>
            </div>

            <div class="flex items-center gap-3">
              <span class="text-sm font-semibold text-surface-900 dark:text-surface-100">
                {{ formatPrice(payment.amount) }}
              </span>
              <AppBadge :variant="getPaymentStatusVariant(payment.status)" size="sm">
                {{ getPaymentStatusLabel(payment.status) }}
              </AppBadge>
            </div>
          </div>
        </div>
      </AppCard>
    </template>

    <!-- Top-up Dialog -->
    <AppConfirmDialog
      :open="showTopupDialog"
      type="info"
      title="Balansni to'ldirish"
      message="To'ldirish summasi va usulini tanlang"
      confirm-text="To'ldirish"
      cancel-text="Bekor qilish"
      @confirm="handleTopup"
      @cancel="showTopupDialog = false"
    >
      <template #body>
        <div class="space-y-4 mt-4">
          <div>
            <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">
              Summa (so'm)
            </label>
            <input
              v-model.number="topupAmount"
              type="number"
              min="1000"
              step="1000"
              class="w-full px-3 py-2 rounded-lg border border-surface-300 dark:border-surface-600 bg-white dark:bg-surface-900 text-surface-900 dark:text-surface-100 focus:outline-none focus:ring-2 focus:ring-brand-500"
              placeholder="100 000"
            />
            <div class="flex gap-2 mt-2">
              <button
                v-for="preset in [50000, 100000, 500000, 1000000]"
                :key="preset"
                class="px-3 py-1 text-xs rounded-full border border-surface-200 dark:border-surface-700 hover:border-brand-300 dark:hover:border-brand-700 text-surface-600 dark:text-surface-400 hover:text-brand-600 transition-colors"
                @click="topupAmount = preset"
              >
                {{ formatPriceShort(preset) }}
              </button>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">
              To'lov usuli
            </label>
            <div class="grid grid-cols-3 gap-2">
              <button
                v-for="method in paymentMethods"
                :key="method.value"
                :class="[
                  'flex flex-col items-center gap-1 px-3 py-3 rounded-lg border-2 transition-all text-sm font-medium',
                  topupMethod === method.value
                    ? 'border-brand-500 bg-brand-50 dark:bg-brand-950/20 text-brand-700 dark:text-brand-300'
                    : 'border-surface-200 dark:border-surface-700 text-surface-600 dark:text-surface-400 hover:border-surface-300',
                ]"
                @click="topupMethod = method.value"
              >
                {{ method.label }}
              </button>
            </div>
          </div>
        </div>
      </template>
    </AppConfirmDialog>

    <!-- Subscribe Plan Dialog -->
    <AppConfirmDialog
      :open="showSubscribeDialog"
      type="info"
      :title="`${selectedPlan?.label} obunasi`"
      :message="`${selectedPlan?.label} rejasiga obuna bo'lmoqchimisiz? Narxi: ${selectedPlan ? formatPrice(selectedPlan.price) : ''} / oy. Hisobingizdagi balansdan yechib olinadi.`"
      confirm-text="Obuna bo'lish"
      cancel-text="Bekor qilish"
      :loading="subscribing"
      @confirm="handleSubscribe"
      @cancel="showSubscribeDialog = false"
    />

    <!-- Cancel Subscription Dialog -->
    <AppConfirmDialog
      :open="showCancelDialog"
      type="warning"
      title="Obunani bekor qilish"
      message="Obunangiz bekor qilinadi. Muddati tugaguncha imkoniyatlardan foydalanishingiz mumkin."
      confirm-text="Bekor qilish"
      cancel-text="Yo'q"
      :loading="cancelling"
      @confirm="handleCancel"
      @cancel="showCancelDialog = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { toast } from 'vue-sonner';
import axios from 'axios';
import {
  SparklesIcon,
  PlusIcon,
  CheckIcon,
  ExclamationCircleIcon,
  BanknotesIcon,
  CreditCardIcon,
  ArrowUpCircleIcon,
  BriefcaseIcon,
  StarIcon,
  CheckCircleIcon,
  XCircleIcon,
} from '@heroicons/vue/24/outline';
import { useAuthStore } from '../../stores/auth';
import AppCard from '../../components/ui/AppCard.vue';
import AppButton from '../../components/ui/AppButton.vue';
import AppBadge from '../../components/ui/AppBadge.vue';
import AppLoadingSpinner from '../../components/ui/AppLoadingSpinner.vue';
import AppConfirmDialog from '../../components/ui/AppConfirmDialog.vue';

const authStore = useAuthStore();

const loading = ref(true);
const currentSubscription = ref(null);
const daysLeft = ref(0);
const limits = ref(null);
const usage = ref({ vacancies: 0, questionnaires: 0, template_messages: 0 });
const planLabel = ref('Bepul');
const payments = ref([]);
const paymentsLoading = ref(false);

const featureFlags = [
  { key: 'ai_translation', label: 'AI tarjima' },
  { key: 'talent_pool', label: 'Nomzodlar bazasi' },
  { key: 'analytics', label: 'Analitika' },
  { key: 'vacancy_top', label: 'Vakansiyani ko\'tarish' },
  { key: 'vacancy_urgent', label: 'Shoshilinch e\'lon' },
];

const balance = computed(() => authStore.user?.balance || 0);

// Topup
const showTopupDialog = ref(false);
const topupAmount = ref(100000);
const topupMethod = ref('payme');

// Subscribe
const showSubscribeDialog = ref(false);
const selectedPlan = ref(null);
const subscribing = ref(false);

// Cancel
const showCancelDialog = ref(false);
const cancelling = ref(false);

const paymentMethods = [
  { value: 'payme', label: 'Payme' },
  { value: 'click', label: 'Click' },
  { value: 'uzum', label: 'Uzum' },
];

const recruiterPlans = [
  {
    value: 'business',
    label: 'Biznes',
    price: 99000,
    popular: false,
    features: [
      '10 ta vakansiya',
      'Asosiy analitika',
      'Savolnoma yaratish',
      'Kandidatlarni boshqarish',
      'Email va chat qo\'llab-quvvatlash',
    ],
  },
  {
    value: 'recruiter_pro',
    label: 'Recruiter Pro',
    price: 499000,
    popular: true,
    features: [
      'Cheksiz vakansiyalar',
      'Kengaytirilgan analitika',
      'Talent Pool',
      'AI baholash',
      'Savolnoma builder',
      'Ustuvor qo\'llab-quvvatlash',
    ],
  },
  {
    value: 'agency',
    label: 'Agency',
    price: 999000,
    popular: false,
    features: [
      'Barcha Pro imkoniyatlari',
      'Jamoa boshqaruvi',
      'API kirish',
      'White label',
      'Cheksiz hamma narsa',
      'Shaxsiy menejer',
    ],
  },
];

const currentPlan = computed(() => {
  if (!currentSubscription.value) return 'free';
  return currentSubscription.value.plan;
});

onMounted(async () => {
  await Promise.all([
    loadSubscription(),
    loadPayments(),
  ]);
  loading.value = false;
});

async function loadSubscription() {
  try {
    const { data } = await axios.get('/api/subscriptions/current');
    currentSubscription.value = data.subscription;
    daysLeft.value = data.days_left || 0;
    limits.value = data.limits || null;
    usage.value = data.usage || { vacancies: 0, questionnaires: 0, template_messages: 0 };
    planLabel.value = data.plan_label || 'Bepul';
  } catch (error) {
    console.error('Failed to load subscription:', error);
  }
}

function getUsagePercent(type) {
  const limitMap = {
    vacancies: 'max_vacancies',
    questionnaires: 'max_questionnaires',
    template_messages: 'max_template_messages',
  };
  const max = limits.value?.[limitMap[type]];
  if (max === null || max === undefined) return 0; // unlimited — don't show bar
  const current = usage.value[type] || 0;
  return Math.min(100, Math.round((current / max) * 100));
}

function getUsageBarColor(type) {
  const pct = getUsagePercent(type);
  if (pct >= 100) return 'bg-danger-500';
  if (pct >= 80) return 'bg-warning-500';
  return 'bg-brand-500';
}

async function loadPayments() {
  paymentsLoading.value = true;
  try {
    const { data } = await axios.get('/api/payments/history', { params: { per_page: 20 } });
    payments.value = data.data || [];
  } catch (error) {
    console.error('Failed to load payments:', error);
  } finally {
    paymentsLoading.value = false;
  }
}

function selectPlan(plan) {
  selectedPlan.value = plan;
  showSubscribeDialog.value = true;
}

async function handleSubscribe() {
  if (!selectedPlan.value) return;

  subscribing.value = true;
  try {
    const { data } = await axios.post('/api/subscriptions', {
      plan: selectedPlan.value.value,
    });
    currentSubscription.value = data.subscription;
    daysLeft.value = 30;
    showSubscribeDialog.value = false;
    toast.success(`${selectedPlan.value.label} obunasi faollashtirildi!`);
    await Promise.all([loadSubscription(), loadPayments(), authStore.fetchUser()]);
  } catch (error) {
    const msg = error.response?.data?.message || 'Xatolik yuz berdi';
    if (error.response?.status === 422 && msg.includes('Balans')) {
      toast.error(`Balans yetarli emas. Kerakli summa: ${formatPrice(selectedPlan.value.price)}`);
    } else {
      toast.error(msg);
    }
  } finally {
    subscribing.value = false;
  }
}

async function handleCancel() {
  if (!currentSubscription.value) return;

  cancelling.value = true;
  try {
    await axios.put(`/api/subscriptions/${currentSubscription.value.id}/cancel`);
    showCancelDialog.value = false;
    toast.success('Obuna bekor qilindi');
    await loadSubscription();
  } catch (error) {
    toast.error(error.response?.data?.message || 'Xatolik yuz berdi');
  } finally {
    cancelling.value = false;
  }
}

async function handleTopup() {
  if (!topupAmount.value || topupAmount.value < 1000) {
    toast.error('Minimal summa: 1,000 so\'m');
    return;
  }

  try {
    const { data } = await axios.post('/api/payments/topup', {
      amount: topupAmount.value,
      method: topupMethod.value,
    });

    if (data.checkout_url) {
      window.open(data.checkout_url, '_blank');
      toast.info('To\'lov sahifasi ochildi');
    } else {
      toast.success('To\'lov yaratildi');
    }

    showTopupDialog.value = false;
    await loadPayments();
  } catch (error) {
    toast.error(error.response?.data?.message || 'Xatolik yuz berdi');
  }
}

// ── Formatters ──

function getPlanLabel(plan) {
  const labels = {
    free: 'Bepul',
    business: 'Biznes',
    recruiter_pro: 'Recruiter Pro',
    agency: 'Agency',
    corporate: 'Korporativ',
  };
  return labels[plan] || plan;
}

function formatPrice(amount) {
  if (!amount) return '0 so\'m';
  return new Intl.NumberFormat('uz-UZ').format(amount) + ' so\'m';
}

function formatPriceShort(amount) {
  if (amount >= 1000000) return (amount / 1000000).toFixed(amount % 1000000 === 0 ? 0 : 1) + ' mln';
  if (amount >= 1000) return (amount / 1000).toFixed(0) + ' ming';
  return amount.toString();
}

function formatDate(date) {
  if (!date) return '—';
  return new Date(date).toLocaleDateString('uz-UZ', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });
}

function formatDateTime(date) {
  if (!date) return '—';
  return new Date(date).toLocaleDateString('uz-UZ', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}

function getPaymentTypeLabel(type) {
  const labels = {
    subscription: 'Obuna to\'lovi',
    vacancy_top: 'Vakansiyani ko\'tarish',
    vacancy_urgent: 'Shoshilinch e\'lon',
    vacancy_post: 'E\'lon joylash',
    balance_topup: 'Balans to\'ldirish',
  };
  return labels[type] || type;
}

function getPaymentTypeIcon(type) {
  const icons = {
    subscription: SparklesIcon,
    vacancy_top: ArrowUpCircleIcon,
    vacancy_urgent: StarIcon,
    vacancy_post: BriefcaseIcon,
    balance_topup: CreditCardIcon,
  };
  return icons[type] || CreditCardIcon;
}

function getPaymentTypeClasses(type) {
  const classes = {
    subscription: 'bg-brand-100 dark:bg-brand-900/20 text-brand-600 dark:text-brand-400',
    vacancy_top: 'bg-warning-100 dark:bg-warning-900/20 text-warning-600 dark:text-warning-400',
    vacancy_urgent: 'bg-danger-100 dark:bg-danger-900/20 text-danger-600 dark:text-danger-400',
    vacancy_post: 'bg-success-100 dark:bg-success-900/20 text-success-600 dark:text-success-400',
    balance_topup: 'bg-surface-100 dark:bg-surface-800 text-surface-600 dark:text-surface-400',
  };
  return classes[type] || 'bg-surface-100 dark:bg-surface-800 text-surface-600 dark:text-surface-400';
}

function getPaymentMethodLabel(method) {
  const labels = {
    payme: 'Payme',
    click: 'Click',
    uzum: 'Uzum',
    balance: 'Balans',
  };
  return labels[method] || method;
}

function getPaymentStatusVariant(status) {
  const variants = {
    pending: 'warning',
    processing: 'info',
    completed: 'success',
    failed: 'danger',
    refunded: 'default',
    cancelled: 'default',
  };
  return variants[status] || 'default';
}

function getPaymentStatusLabel(status) {
  const labels = {
    pending: 'Kutilmoqda',
    processing: 'Jarayonda',
    completed: 'Muvaffaqiyatli',
    failed: 'Xatolik',
    refunded: 'Qaytarilgan',
    cancelled: 'Bekor qilingan',
  };
  return labels[status] || status;
}
</script>
