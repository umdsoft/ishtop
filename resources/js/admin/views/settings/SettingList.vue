<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">{{ $t('settings.title') }}</h1>
      <button
        @click="clearCache"
        :disabled="clearingCache"
        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition-colors"
        :class="clearingCache
          ? 'bg-surface-200 dark:bg-surface-700 text-surface-500 cursor-not-allowed'
          : 'bg-warning-100 text-warning-700 hover:bg-warning-200 dark:bg-warning-900/30 dark:text-warning-400 dark:hover:bg-warning-900/50'"
      >
        <ArrowPathIcon class="w-4 h-4" :class="{ 'animate-spin': clearingCache }" />
        {{ $t('settings.clearCache') }}
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="space-y-6">
      <AppCard v-for="i in 4" :key="i">
        <div class="animate-pulse space-y-4">
          <div class="h-5 bg-surface-200 dark:bg-surface-700 rounded w-1/4"></div>
          <div class="space-y-3">
            <div v-for="j in 3" :key="j" class="flex justify-between">
              <div class="h-4 bg-surface-100 dark:bg-surface-800 rounded w-1/3"></div>
              <div class="h-4 bg-surface-100 dark:bg-surface-800 rounded w-1/5"></div>
            </div>
          </div>
        </div>
      </AppCard>
    </div>

    <template v-else>
      <!-- General -->
      <AppCard v-if="settings.general">
        <div class="flex items-center gap-3 mb-5">
          <div class="w-9 h-9 rounded-xl bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center">
            <Cog6ToothIcon class="w-5 h-5 text-brand-600 dark:text-brand-400" />
          </div>
          <h2 class="text-lg font-semibold text-surface-900 dark:text-surface-100">{{ $t('settings.general') }}</h2>
        </div>
        <div class="space-y-0 divide-y divide-surface-100 dark:divide-surface-800">
          <div v-for="item in settings.general" :key="item.key" class="flex items-center justify-between py-3.5">
            <div>
              <p class="text-sm font-medium text-surface-800 dark:text-surface-200">{{ item.label }}</p>
              <p class="text-xs text-surface-500 mt-0.5">{{ item.key }}</p>
            </div>
            <div class="text-sm font-mono px-3 py-1.5 rounded-lg bg-surface-50 dark:bg-surface-800 text-surface-700 dark:text-surface-300 max-w-sm truncate">
              {{ item.value || '—' }}
            </div>
          </div>
        </div>
      </AppCard>

      <!-- Pricing -->
      <AppCard v-if="settings.pricing">
        <div class="flex items-center gap-3 mb-5">
          <div class="w-9 h-9 rounded-xl bg-success-100 dark:bg-success-900/30 flex items-center justify-center">
            <BanknotesIcon class="w-5 h-5 text-success-600 dark:text-success-400" />
          </div>
          <h2 class="text-lg font-semibold text-surface-900 dark:text-surface-100">{{ $t('settings.pricing') }}</h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="item in settings.pricing"
            :key="item.key"
            class="flex items-center justify-between p-4 rounded-xl bg-surface-50 dark:bg-surface-800/50 border border-surface-100 dark:border-surface-800"
          >
            <div>
              <p class="text-sm font-medium text-surface-800 dark:text-surface-200">{{ item.label }}</p>
            </div>
            <p class="text-lg font-bold text-success-600 dark:text-success-400">
              {{ formatCurrency(item.value) }}
            </p>
          </div>
        </div>
      </AppCard>

      <!-- Durations -->
      <AppCard v-if="settings.durations">
        <div class="flex items-center gap-3 mb-5">
          <div class="w-9 h-9 rounded-xl bg-info-100 dark:bg-info-900/30 flex items-center justify-center">
            <ClockIcon class="w-5 h-5 text-info-600 dark:text-info-400" />
          </div>
          <h2 class="text-lg font-semibold text-surface-900 dark:text-surface-100">{{ $t('settings.durations') }}</h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div
            v-for="item in settings.durations"
            :key="item.key"
            class="text-center p-5 rounded-xl bg-surface-50 dark:bg-surface-800/50 border border-surface-100 dark:border-surface-800"
          >
            <p class="text-3xl font-bold text-info-600 dark:text-info-400">{{ item.value }}</p>
            <p class="text-xs text-surface-500 mt-1">kun</p>
            <p class="text-sm font-medium text-surface-800 dark:text-surface-200 mt-2">{{ item.label }}</p>
          </div>
        </div>
      </AppCard>

      <!-- Rate Limits -->
      <AppCard v-if="settings.rate_limits">
        <div class="flex items-center gap-3 mb-5">
          <div class="w-9 h-9 rounded-xl bg-warning-100 dark:bg-warning-900/30 flex items-center justify-center">
            <ShieldExclamationIcon class="w-5 h-5 text-warning-600 dark:text-warning-400" />
          </div>
          <h2 class="text-lg font-semibold text-surface-900 dark:text-surface-100">{{ $t('settings.rateLimits') }}</h2>
        </div>
        <div class="space-y-0 divide-y divide-surface-100 dark:divide-surface-800">
          <div v-for="item in settings.rate_limits" :key="item.key" class="flex items-center justify-between py-3.5">
            <div>
              <p class="text-sm font-medium text-surface-800 dark:text-surface-200">{{ item.label }}</p>
            </div>
            <span class="inline-flex items-center gap-1 text-sm font-semibold px-3 py-1 rounded-lg bg-warning-50 dark:bg-warning-900/20 text-warning-700 dark:text-warning-400">
              {{ item.value }}
            </span>
          </div>
        </div>
      </AppCard>

      <!-- Scoring & Anti-fraud side by side -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Scoring -->
        <AppCard v-if="settings.scoring">
          <div class="flex items-center gap-3 mb-5">
            <div class="w-9 h-9 rounded-xl bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center">
              <ChartBarIcon class="w-5 h-5 text-brand-600 dark:text-brand-400" />
            </div>
            <h2 class="text-lg font-semibold text-surface-900 dark:text-surface-100">{{ $t('settings.scoring') }}</h2>
          </div>
          <div class="space-y-4">
            <div v-for="item in settings.scoring" :key="item.key" class="flex items-center justify-between">
              <p class="text-sm font-medium text-surface-800 dark:text-surface-200">{{ item.label }}</p>
              <span class="text-sm font-bold" :class="scoringColor(item)">
                {{ item.value }}{{ item.type === 'percent' ? '%' : item.type === 'seconds' ? 's' : '' }}
              </span>
            </div>
          </div>
        </AppCard>

        <!-- Anti-fraud -->
        <AppCard v-if="settings.anti_fraud">
          <div class="flex items-center gap-3 mb-5">
            <div class="w-9 h-9 rounded-xl bg-danger-100 dark:bg-danger-900/30 flex items-center justify-center">
              <ShieldCheckIcon class="w-5 h-5 text-danger-600 dark:text-danger-400" />
            </div>
            <h2 class="text-lg font-semibold text-surface-900 dark:text-surface-100">{{ $t('settings.antiFraud') }}</h2>
          </div>
          <div class="space-y-4">
            <div v-for="item in settings.anti_fraud" :key="item.key" class="flex items-center justify-between">
              <p class="text-sm font-medium text-surface-800 dark:text-surface-200">{{ item.label }}</p>
              <span class="text-sm font-bold text-danger-600 dark:text-danger-400">
                {{ item.value }}{{ item.type === 'percent' ? '%' : '' }}
              </span>
            </div>
          </div>
        </AppCard>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { toast } from 'vue-sonner';
import AppCard from '@panel/components/ui/AppCard.vue';
import {
  Cog6ToothIcon, BanknotesIcon, ClockIcon, ShieldExclamationIcon,
  ChartBarIcon, ShieldCheckIcon, ArrowPathIcon,
} from '@heroicons/vue/24/outline';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const settings = ref({});
const loading = ref(true);
const clearingCache = ref(false);

function formatCurrency(value) {
  return Number(value).toLocaleString() + ' so\'m';
}

function scoringColor(item) {
  if (item.key === 'green_threshold') return 'text-success-600 dark:text-success-400';
  if (item.key === 'yellow_threshold') return 'text-warning-600 dark:text-warning-400';
  return 'text-surface-700 dark:text-surface-300';
}

async function fetchSettings() {
  try {
    const res = await axios.get('/api/admin/settings');
    settings.value = res.data.settings || {};
  } catch (err) {
    toast.error('Sozlamalarni yuklashda xatolik');
  } finally {
    loading.value = false;
  }
}

async function clearCache() {
  clearingCache.value = true;
  try {
    await axios.post('/api/admin/settings/clear-cache');
    toast.success(t('settings.cacheClearedSuccess'));
  } catch (err) {
    toast.error(t('settings.cacheClearedError'));
  } finally {
    clearingCache.value = false;
  }
}

onMounted(fetchSettings);
</script>
