<template>
  <div class="space-y-6">
    <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">{{ $t('subscriptions.title') }}</h1>

    <!-- Filters -->
    <AppCard>
      <div class="flex flex-wrap items-center gap-3">
        <AppSearchInput v-model="search" :placeholder="$t('common.search')" @update:modelValue="applySearch" class="w-64" />
        <select
          v-model="filters.status"
          @change="applyFilter('status', filters.status)"
          class="px-3 py-2 bg-surface-50 dark:bg-surface-800 border border-surface-300 dark:border-surface-700 rounded-lg text-sm"
        >
          <option value="">{{ $t('common.all') }}</option>
          <option value="active">{{ $t('subscriptions.active') }}</option>
          <option value="expired">{{ $t('subscriptions.expired') }}</option>
          <option value="cancelled">{{ $t('subscriptions.cancelled') }}</option>
        </select>
        <select
          v-model="filters.plan"
          @change="applyFilter('plan', filters.plan)"
          class="px-3 py-2 bg-surface-50 dark:bg-surface-800 border border-surface-300 dark:border-surface-700 rounded-lg text-sm"
        >
          <option value="">Barcha tariflar</option>
          <option value="free">Bepul</option>
          <option value="worker_premium">Worker Premium</option>
          <option value="business">Business</option>
          <option value="recruiter_pro">Recruiter Pro</option>
          <option value="agency">Agency</option>
          <option value="corporate">Corporate</option>
        </select>
      </div>
    </AppCard>

    <!-- Table -->
    <AppCard noPadding>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-surface-200 dark:border-surface-800">
              <th class="text-left py-3 px-4 font-medium text-surface-500">{{ $t('payments.user') }}</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500">{{ $t('subscriptions.plan') }}</th>
              <th class="text-right py-3 px-4 font-medium text-surface-500">{{ $t('subscriptions.price') }}</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500">{{ $t('common.status') }}</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500">{{ $t('subscriptions.daysLeft') }}</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500 cursor-pointer" @click="setSort('expires_at')">{{ $t('subscriptions.expiresAt') }}</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500 cursor-pointer" @click="setSort('created_at')">{{ $t('common.date') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="sub in items"
              :key="sub.id"
              class="border-b border-surface-100 dark:border-surface-800/50 hover:bg-surface-50 dark:hover:bg-surface-800/30 cursor-pointer"
              @click="$router.push(`/subscriptions/${sub.id}`)"
            >
              <td class="py-3 px-4">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-full bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center text-brand-600 dark:text-brand-400 text-xs font-bold shrink-0">
                    {{ (sub.user?.first_name?.[0] || '?').toUpperCase() }}
                  </div>
                  <div class="min-w-0">
                    <p class="font-medium text-surface-900 dark:text-surface-100 truncate">{{ sub.user?.first_name }} {{ sub.user?.last_name }}</p>
                    <p class="text-xs text-surface-500 dark:text-surface-400">{{ sub.user?.phone || '' }}</p>
                  </div>
                </div>
              </td>
              <td class="py-3 px-4">
                <span :class="['text-xs px-2 py-0.5 rounded-full font-medium', planClass(sub.plan)]">
                  {{ planLabel(sub.plan) }}
                </span>
              </td>
              <td class="py-3 px-4 text-right font-semibold text-surface-900 dark:text-surface-100">
                <template v-if="sub.price > 0">{{ Number(sub.price).toLocaleString() }} <span class="text-xs text-surface-500 font-normal">so'm</span></template>
                <span v-else class="text-surface-400 font-normal">Bepul</span>
              </td>
              <td class="py-3 px-4">
                <span :class="['text-xs px-2 py-0.5 rounded-full font-medium', statusClass(sub.status, sub.expires_at)]">
                  {{ statusLabel(sub.status, sub.expires_at) }}
                </span>
              </td>
              <td class="py-3 px-4">
                <template v-if="sub.status === 'active' && sub.expires_at">
                  <span :class="['text-sm font-medium', daysLeft(sub.expires_at) <= 3 ? 'text-danger-600' : daysLeft(sub.expires_at) <= 7 ? 'text-warning-600' : 'text-surface-700 dark:text-surface-300']">
                    {{ daysLeft(sub.expires_at) }} kun
                  </span>
                </template>
                <span v-else class="text-surface-400">—</span>
              </td>
              <td class="py-3 px-4 text-surface-500 text-xs whitespace-nowrap">{{ formatDate(sub.expires_at) }}</td>
              <td class="py-3 px-4 text-surface-500 text-xs whitespace-nowrap">{{ formatDate(sub.created_at) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-if="loading" class="p-8 text-center text-surface-500">{{ $t('common.loading') }}</div>
      <div v-if="!loading && items.length === 0" class="p-8 text-center text-surface-500">
        <CreditCardIcon class="w-10 h-10 mx-auto text-surface-300 dark:text-surface-600 mb-2" />
        <p>{{ $t('common.noData') }}</p>
      </div>
    </AppCard>

    <!-- Pagination -->
    <AppPagination v-if="lastPage > 1" :current-page="currentPage" :last-page="lastPage" :total="total" @page-change="goToPage" />
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useResourceList } from '../../composables/useAdminApi';
import AppCard from '@panel/components/ui/AppCard.vue';
import AppSearchInput from '@panel/components/ui/AppSearchInput.vue';
import AppPagination from '@panel/components/ui/AppPagination.vue';
import { CreditCardIcon } from '@heroicons/vue/24/outline';
import { formatDate } from '@/shared/formatters';

const {
  items, total, currentPage, lastPage, loading, search, filters,
  fetchItems, goToPage, setSort, applySearch, applyFilter,
} = useResourceList('/subscriptions');

function daysLeft(expiresAt) {
  if (!expiresAt) return 0;
  const diff = new Date(expiresAt) - new Date();
  return Math.max(0, Math.ceil(diff / (1000 * 60 * 60 * 24)));
}

function planLabel(plan) {
  const map = {
    free: 'Bepul',
    worker_premium: 'Worker Premium',
    business: 'Business',
    recruiter_pro: 'Recruiter Pro',
    agency: 'Agency',
    corporate: 'Corporate',
  };
  return map[plan] || plan;
}

function planClass(plan) {
  const map = {
    free: 'bg-surface-100 text-surface-700 dark:bg-surface-800 dark:text-surface-400',
    worker_premium: 'bg-brand-100 text-brand-700 dark:bg-brand-900/30 dark:text-brand-400',
    business: 'bg-info-100 text-info-700 dark:bg-info-900/30 dark:text-info-400',
    recruiter_pro: 'bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-400',
    agency: 'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400',
    corporate: 'bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-400',
  };
  return map[plan] || 'bg-surface-100 text-surface-600';
}

function statusClass(status, expiresAt) {
  if (status === 'active' && expiresAt && daysLeft(expiresAt) <= 3) {
    return 'bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-400';
  }
  const map = {
    active: 'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400',
    expired: 'bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-400',
    cancelled: 'bg-surface-100 text-surface-700 dark:bg-surface-800 dark:text-surface-400',
  };
  return map[status] || 'bg-surface-100 text-surface-600';
}

function statusLabel(status, expiresAt) {
  if (status === 'active' && expiresAt && daysLeft(expiresAt) <= 3) {
    return 'Tugayapti';
  }
  const map = {
    active: 'Faol',
    expired: "Muddati o'tgan",
    cancelled: 'Bekor qilingan',
  };
  return map[status] || status;
}

onMounted(fetchItems);
</script>
