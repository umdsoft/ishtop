<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">{{ $t('reports.title') }}</h1>
      <span class="text-sm text-surface-500 dark:text-surface-400">Jami: {{ total }}</span>
    </div>

    <!-- Filters -->
    <AppCard>
      <div class="flex flex-wrap items-center gap-3">
        <AppSearchInput v-model="search" :placeholder="$t('common.search')" @update:modelValue="applySearch" class="w-64" />
        <select
          v-model="filters.status"
          @change="applyFilter('status', filters.status)"
          class="px-3 py-2 bg-surface-50 dark:bg-surface-800 border border-surface-300 dark:border-surface-700 rounded-lg text-sm text-surface-900 dark:text-surface-100"
        >
          <option value="">Barcha status</option>
          <option value="pending">{{ $t('reports.pending') }}</option>
          <option value="resolved">{{ $t('reports.resolved') }}</option>
          <option value="dismissed">{{ $t('reports.dismissed') }}</option>
        </select>
      </div>
    </AppCard>

    <!-- Table -->
    <AppCard noPadding>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-surface-200 dark:border-surface-800">
              <th class="text-left py-3 px-4 font-medium text-surface-500">{{ $t('reports.reporter') }}</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500">{{ $t('reports.reason') }}</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500">{{ $t('reports.target') }}</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500">{{ $t('common.status') }}</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500 cursor-pointer hover:text-surface-700 dark:hover:text-surface-300 transition-colors" @click="setSort('created_at')">
                {{ $t('common.date') }}
              </th>
              <th class="text-right py-3 px-4 font-medium text-surface-500">{{ $t('common.actions') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="report in items"
              :key="report.id"
              class="border-b border-surface-100 dark:border-surface-800/50 hover:bg-surface-50 dark:hover:bg-surface-800/30 cursor-pointer transition-colors"
              @click="$router.push(`/reports/${report.id}`)"
            >
              <td class="py-3 px-4">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-full bg-danger-100 dark:bg-danger-900/30 flex items-center justify-center text-danger-600 dark:text-danger-400 text-xs font-bold shrink-0">
                    {{ (report.reporter?.first_name?.[0] || 'U').toUpperCase() }}
                  </div>
                  <div class="min-w-0">
                    <p class="font-medium text-surface-900 dark:text-surface-100 truncate">{{ report.reporter?.first_name }} {{ report.reporter?.last_name }}</p>
                    <p v-if="report.reporter?.phone" class="text-xs text-surface-500 dark:text-surface-400">{{ report.reporter.phone }}</p>
                  </div>
                </div>
              </td>
              <td class="py-3 px-4">
                <p class="text-surface-600 dark:text-surface-400 line-clamp-2 max-w-xs">{{ report.reason || '—' }}</p>
              </td>
              <td class="py-3 px-4">
                <div class="flex items-center gap-2">
                  <span :class="['text-xs px-2 py-0.5 rounded-full font-medium whitespace-nowrap', typeClass(report.reportable_type)]">
                    {{ reportableTypeLabel(report.reportable_type) }}
                  </span>
                  <span v-if="reportableTitle(report)" class="text-xs text-surface-500 dark:text-surface-400 truncate max-w-[120px]" :title="reportableTitle(report)">
                    {{ reportableTitle(report) }}
                  </span>
                </div>
              </td>
              <td class="py-3 px-4">
                <span :class="['text-xs px-2 py-0.5 rounded-full font-medium whitespace-nowrap', statusClass(report.status)]">
                  {{ statusLabel(report.status) }}
                </span>
              </td>
              <td class="py-3 px-4 text-surface-500 text-xs whitespace-nowrap">{{ formatDate(report.created_at) }}</td>
              <td class="py-3 px-4 text-right" @click.stop>
                <div v-if="report.status === 'pending'" class="flex items-center justify-end gap-1.5">
                  <button
                    @click="resolveReport(report)"
                    class="p-1.5 rounded-lg bg-success-50 text-success-600 hover:bg-success-100 dark:bg-success-950/30 dark:text-success-400 dark:hover:bg-success-900/40 transition-colors"
                    :title="$t('reports.resolve')"
                  >
                    <CheckIcon class="w-4 h-4" />
                  </button>
                  <button
                    @click="dismissReport(report)"
                    class="p-1.5 rounded-lg bg-surface-100 text-surface-600 hover:bg-surface-200 dark:bg-surface-800 dark:text-surface-400 dark:hover:bg-surface-700 transition-colors"
                    :title="$t('reports.dismiss')"
                  >
                    <XMarkIcon class="w-4 h-4" />
                  </button>
                </div>
                <span v-else class="text-xs text-surface-400">—</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-if="loading" class="p-8 text-center text-surface-500">{{ $t('common.loading') }}</div>
      <div v-if="!loading && items.length === 0" class="p-12 text-center">
        <ShieldCheckIcon class="w-10 h-10 mx-auto text-surface-300 dark:text-surface-600 mb-3" />
        <p class="text-surface-500 dark:text-surface-400">Shikoyatlar yo'q</p>
      </div>
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
import { CheckIcon, XMarkIcon, ShieldCheckIcon } from '@heroicons/vue/24/outline';

const {
  items, total, currentPage, lastPage, loading, search, filters,
  fetchItems, goToPage, setSort, applySearch, applyFilter,
} = useResourceList('/reports');

function formatDate(d) {
  if (!d) return '';
  return new Date(d).toLocaleDateString('uz-UZ', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function reportableTypeLabel(type) {
  if (!type) return '—';
  const map = {
    'App\\Models\\Vacancy': 'Vakansiya',
    'App\\Models\\EmployerProfile': 'Ish beruvchi',
    'App\\Models\\User': 'Foydalanuvchi',
    'App\\Models\\WorkerProfile': 'Ishchi',
  };
  return map[type] || type.split('\\').pop();
}

function typeClass(type) {
  if (!type) return 'bg-surface-100 text-surface-600';
  const map = {
    'App\\Models\\Vacancy': 'bg-brand-100 text-brand-700 dark:bg-brand-900/30 dark:text-brand-400',
    'App\\Models\\EmployerProfile': 'bg-info-100 text-info-700 dark:bg-info-900/30 dark:text-info-400',
    'App\\Models\\User': 'bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-400',
    'App\\Models\\WorkerProfile': 'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400',
  };
  return map[type] || 'bg-surface-100 text-surface-600 dark:bg-surface-800 dark:text-surface-400';
}

function reportableTitle(report) {
  if (!report.reportable) return null;
  const r = report.reportable;
  return r.title_uz || r.company_name || r.full_name || (r.first_name ? `${r.first_name} ${r.last_name || ''}`.trim() : null);
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

async function dismissReport(report) {
  try {
    const res = await axios.post(`/api/admin/reports/${report.id}/dismiss`);
    report.status = 'dismissed';
    toast.success(res.data.message || 'Shikoyat rad etildi');
  } catch (err) {
    toast.error(err.response?.data?.message || 'Xatolik');
  }
}

onMounted(fetchItems);
</script>
