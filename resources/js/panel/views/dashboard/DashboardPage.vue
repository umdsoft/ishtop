<template>
  <div class="space-y-6">
    <!-- Welcome Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl md:text-3xl font-bold text-surface-900 dark:text-surface-100">
          {{ $t('dashboard.welcome', { name: user?.first_name || 'User' }) }} 👋
        </h1>
        <p class="mt-1 text-surface-600 dark:text-surface-400">
          {{ $t('dashboard.overview') }}
        </p>
      </div>

      <AppButton variant="primary">
        <template #icon-left>
          <PlusIcon class="w-5 h-5" />
        </template>
        Yangi e'lon
      </AppButton>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
      <AppCard>
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-full bg-brand-100 dark:bg-brand-900/50 flex items-center justify-center">
            <BriefcaseIcon class="w-6 h-6 text-brand-600 dark:text-brand-400" />
          </div>
          <div class="flex-1">
            <p class="text-sm text-surface-600 dark:text-surface-400">{{ $t('dashboard.activeVacancies') }}</p>
            <p class="text-2xl font-bold text-surface-900 dark:text-surface-100">12</p>
            <p class="text-xs text-success-600 dark:text-success-400">↑ 12%</p>
          </div>
        </div>
      </AppCard>

      <AppCard>
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-full bg-info-100 dark:bg-info-900/50 flex items-center justify-center">
            <DocumentTextIcon class="w-6 h-6 text-info-600 dark:text-info-400" />
          </div>
          <div class="flex-1">
            <p class="text-sm text-surface-600 dark:text-surface-400">{{ $t('dashboard.todayApplications') }}</p>
            <p class="text-2xl font-bold text-surface-900 dark:text-surface-100">47</p>
            <p class="text-xs text-success-600 dark:text-success-400">+5 bugun</p>
          </div>
        </div>
      </AppCard>

      <AppCard>
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-full bg-warning-100 dark:bg-warning-900/50 flex items-center justify-center">
            <ClockIcon class="w-6 h-6 text-warning-600 dark:text-warning-400" />
          </div>
          <div class="flex-1">
            <p class="text-sm text-surface-600 dark:text-surface-400">O'rtacha javob</p>
            <p class="text-2xl font-bold text-surface-900 dark:text-surface-100">2.4s</p>
            <p class="text-xs text-danger-600 dark:text-danger-400">↓ 15%</p>
          </div>
        </div>
      </AppCard>

      <AppCard>
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-full bg-success-100 dark:bg-success-900/50 flex items-center justify-center">
            <CheckCircleIcon class="w-6 h-6 text-success-600 dark:text-success-400" />
          </div>
          <div class="flex-1">
            <p class="text-sm text-surface-600 dark:text-surface-400">{{ $t('dashboard.closedThisMonth') }}</p>
            <p class="text-2xl font-bold text-surface-900 dark:text-surface-100">5</p>
            <p class="text-xs text-success-600 dark:text-success-400">↑ 40%</p>
          </div>
        </div>
      </AppCard>
    </div>

    <!-- Charts and Lists -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <AppCard title="So'nggi arizalar">
        <div class="space-y-3">
          <div v-for="i in 5" :key="i" class="flex items-center gap-3 p-3 hover:bg-surface-50 dark:hover:bg-surface-800 rounded-lg transition-colors cursor-pointer">
            <div class="w-10 h-10 rounded-full bg-brand-100 dark:bg-brand-900 flex items-center justify-center text-brand-600 dark:text-brand-400 font-semibold">
              A{{ i }}
            </div>
            <div class="flex-1">
              <p class="font-medium text-surface-900 dark:text-surface-100">Candidate {{ i }}</p>
              <p class="text-xs text-surface-500 dark:text-surface-400">Vacancy Title</p>
            </div>
            <div class="text-right">
              <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-success-100 dark:bg-success-900 text-success-700 dark:text-success-300">
                85
              </span>
              <p class="text-xs text-surface-500 dark:text-surface-400 mt-1">2 min</p>
            </div>
          </div>
        </div>
      </AppCard>

      <AppCard title="Mening e'lonlarim">
        <div class="space-y-3">
          <div v-for="i in 4" :key="i" class="flex items-center justify-between p-3 hover:bg-surface-50 dark:hover:bg-surface-800 rounded-lg transition-colors cursor-pointer">
            <div>
              <p class="font-medium text-surface-900 dark:text-surface-100">Vacancy {{ i }}</p>
              <p class="text-xs text-surface-500 dark:text-surface-400">Category</p>
            </div>
            <span class="px-3 py-1 text-sm font-medium bg-brand-100 dark:bg-brand-900 text-brand-700 dark:text-brand-300 rounded-full">
              {{ 20 + i * 3 }}
            </span>
          </div>
        </div>
      </AppCard>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useAuthStore } from '../../stores/auth';
import AppCard from '../../components/ui/AppCard.vue';
import AppButton from '../../components/ui/AppButton.vue';
import {
  PlusIcon,
  BriefcaseIcon,
  DocumentTextIcon,
  ClockIcon,
  CheckCircleIcon,
} from '@heroicons/vue/24/outline';

const authStore = useAuthStore();
const user = computed(() => authStore.user);
</script>
