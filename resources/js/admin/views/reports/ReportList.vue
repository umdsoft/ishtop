<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">{{ $t('reports.title') }}</h1>
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
          <option value="resolved">Hal qilingan</option>
          <option value="dismissed">Rad etilgan</option>
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
              <th class="text-left py-3 px-4 font-medium text-surface-500">Sabab</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500">Turi</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500">{{ $t('common.status') }}</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500 cursor-pointer" @click="setSort('created_at')">{{ $t('common.date') }}</th>
              <th class="text-right py-3 px-4 font-medium text-surface-500">{{ $t('common.actions') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="report in items"
              :key="report.id"
              class="border-b border-surface-100 dark:border-surface-800/50 hover:bg-surface-50 dark:hover:bg-surface-800/30 cursor-pointer"
              @click="$router.push(`/reports/${report.id}`)"
            >
              <td class="py-3 px-4">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-full bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center text-brand-600 dark:text-brand-400 text-xs font-bold">
                    {{ (report.user?.first_name?.[0] || 'U').toUpperCase() }}
                  </div>
                  <p class="font-medium text-surface-900 dark:text-surface-100">{{ report.user?.first_name }} {{ report.user?.last_name }}</p>
                </div>
              </td>
              <td class="py-3 px-4 text-surface-600 dark:text-surface-400">{{ report.reason || '—' }}</td>
              <td class="py-3 px-4 text-surface-600 dark:text-surface-400">
                <span class="text-xs px-2 py-0.5 rounded-full font-medium bg-surface-100 text-surface-700 dark:bg-surface-800 dark:text-surface-400">
                  {{ reportableType(report.reportable_type) }}
                </span>
              </td>
              <td class="py-3 px-4">
                <span :class="['text-xs px-2 py-0.5 rounded-full font-medium', statusClass(report.status)]">
                  {{ statusLabel(report.status) }}
                </span>
              </td>
              <td class="py-3 px-4 text-surface-500 text-xs">{{ formatDate(report.created_at) }}</td>
              <td class="py-3 px-4 text-right" @click.stop>
                <button
                  v-if="report.status === 'pending'"
                  @click="resolveReport(report)"
                  class="text-xs px-3 py-1.5 rounded-lg font-medium bg-success-50 text-success-700 hover:bg-success-100 dark:bg-success-950/30 dark:text-success-400 transition-colors"
                >
                  <CheckIcon class="w-4 h-4 inline" /> Hal qilish
                </button>
              </td>
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
import axios from 'axios';
import { toast } from 'vue-sonner';
import { useResourceList } from '../../composables/useAdminApi';
import AppCard from '@panel/components/ui/AppCard.vue';
import AppSearchInput from '@panel/components/ui/AppSearchInput.vue';
import AppPagination from '@panel/components/ui/AppPagination.vue';
import { CheckIcon } from '@heroicons/vue/24/outline';

const {
  items, total, currentPage, lastPage, loading, search, filters,
  fetchItems, goToPage, setSort, applySearch, applyFilter,
} = useResourceList('/reports');

function formatDate(d) {
  if (!d) return '';
  return new Date(d).toLocaleDateString('uz-UZ', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function reportableType(type) {
  if (!type) return '—';
  const parts = type.split('\\');
  return parts[parts.length - 1] || type;
}

function statusClass(status) {
  const map = {
    pending: 'bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-400',
    resolved: 'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400',
    dismissed: 'bg-surface-100 text-surface-700 dark:bg-surface-800 dark:text-surface-400',
  };
  return map[status] || 'bg-surface-100 text-surface-600';
}

function statusLabel(status) {
  const map = {
    pending: 'Kutilmoqda',
    resolved: 'Hal qilingan',
    dismissed: 'Rad etilgan',
  };
  return map[status] || status;
}

async function resolveReport(report) {
  try {
    const res = await axios.post(`/api/admin/reports/${report.id}/resolve`);
    report.status = 'resolved';
    toast.success(res.data.message || 'Shikoyat hal qilindi');
  } catch (err) {
    toast.error(err.response?.data?.message || 'Xatolik');
  }
}

onMounted(fetchItems);
</script>
