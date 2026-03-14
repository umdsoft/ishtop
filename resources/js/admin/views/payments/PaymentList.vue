<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">{{ $t('payments.title') }}</h1>
    </div>

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
          <option value="pending">Kutilmoqda</option>
          <option value="completed">Muvaffaqiyatli</option>
          <option value="failed">Muvaffaqiyatsiz</option>
          <option value="refunded">Qaytarilgan</option>
        </select>
      </div>
    </AppCard>

    <!-- Table -->
    <AppCard noPadding>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-surface-200 dark:border-surface-800">
              <th class="text-left py-3 px-4 font-medium text-surface-500">Foydalanuvchi</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500 cursor-pointer" @click="setSort('amount')">Summa</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500">{{ $t('common.status') }}</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500">To'lov usuli</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500 cursor-pointer" @click="setSort('created_at')">{{ $t('common.date') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="payment in items"
              :key="payment.id"
              class="border-b border-surface-100 dark:border-surface-800/50 hover:bg-surface-50 dark:hover:bg-surface-800/30 cursor-pointer"
              @click="$router.push(`/payments/${payment.id}`)"
            >
              <td class="py-3 px-4">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-full bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center text-brand-600 dark:text-brand-400 text-xs font-bold">
                    {{ (payment.user?.first_name?.[0] || 'U').toUpperCase() }}
                  </div>
                  <p class="font-medium text-surface-900 dark:text-surface-100">{{ payment.user?.first_name }} {{ payment.user?.last_name }}</p>
                </div>
              </td>
              <td class="py-3 px-4 font-medium text-surface-900 dark:text-surface-100">{{ Number(payment.amount).toLocaleString() }} so'm</td>
              <td class="py-3 px-4">
                <span :class="['text-xs px-2 py-0.5 rounded-full font-medium', statusClass(payment.status)]">
                  {{ statusLabel(payment.status) }}
                </span>
              </td>
              <td class="py-3 px-4 text-surface-600 dark:text-surface-400">{{ payment.payment_method || '—' }}</td>
              <td class="py-3 px-4 text-surface-500 text-xs">{{ formatDate(payment.created_at) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-if="loading" class="p-8 text-center text-surface-500">{{ $t('common.loading') }}</div>
      <div v-if="!loading && items.length === 0" class="p-8 text-center text-surface-500">{{ $t('common.noData') }}</div>
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

const {
  items, total, currentPage, lastPage, loading, search, filters,
  fetchItems, goToPage, setSort, applySearch, applyFilter,
} = useResourceList('/payments');

function formatDate(d) {
  if (!d) return '';
  return new Date(d).toLocaleDateString('uz-UZ', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function statusClass(status) {
  const map = {
    pending: 'bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-400',
    completed: 'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400',
    failed: 'bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-400',
    refunded: 'bg-info-100 text-info-700 dark:bg-info-900/30 dark:text-info-400',
  };
  return map[status] || 'bg-surface-100 text-surface-600';
}

function statusLabel(status) {
  const map = {
    pending: 'Kutilmoqda',
    completed: 'Muvaffaqiyatli',
    failed: 'Muvaffaqiyatsiz',
    refunded: 'Qaytarilgan',
  };
  return map[status] || status;
}

onMounted(fetchItems);
</script>
