<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">{{ $t('vacancies.title') }}</h1>
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
          <option value="active">Faol</option>
          <option value="closed">Yopilgan</option>
          <option value="expired">Muddati o'tgan</option>
        </select>
        <select
          v-model="filters.work_type"
          @change="applyFilter('work_type', filters.work_type)"
          class="px-3 py-2 bg-surface-50 dark:bg-surface-800 border border-surface-300 dark:border-surface-700 rounded-lg text-sm"
        >
          <option value="">Barcha ish turi</option>
          <option value="full_time">To'liq stavka</option>
          <option value="part_time">Yarim stavka</option>
          <option value="remote">Masofaviy</option>
          <option value="freelance">Frilanser</option>
        </select>
      </div>
    </AppCard>

    <!-- Table -->
    <AppCard noPadding>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-surface-200 dark:border-surface-800">
              <th class="text-left py-3 px-4 font-medium text-surface-500 cursor-pointer" @click="setSort('title_uz')">Sarlavha</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500">Kompaniya</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500">Kategoriya</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500">Shahar</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500">{{ $t('common.status') }}</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500 cursor-pointer" @click="setSort('views_count')">Ko'rishlar</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500">Arizalar</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500 cursor-pointer" @click="setSort('created_at')">{{ $t('common.date') }}</th>
              <th class="text-right py-3 px-4 font-medium text-surface-500">{{ $t('common.actions') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="vacancy in items"
              :key="vacancy.id"
              class="border-b border-surface-100 dark:border-surface-800/50 hover:bg-surface-50 dark:hover:bg-surface-800/30 cursor-pointer"
              @click="$router.push(`/vacancies/${vacancy.id}`)"
            >
              <td class="py-3 px-4">
                <p class="font-medium text-surface-900 dark:text-surface-100">{{ vacancy.title_uz }}</p>
              </td>
              <td class="py-3 px-4 text-surface-600 dark:text-surface-400">{{ vacancy.employer?.company_name || '—' }}</td>
              <td class="py-3 px-4 text-surface-600 dark:text-surface-400">{{ vacancy.category?.name || '—' }}</td>
              <td class="py-3 px-4 text-surface-600 dark:text-surface-400">{{ vacancy.city || '—' }}</td>
              <td class="py-3 px-4">
                <span :class="['text-xs px-2 py-0.5 rounded-full font-medium', statusClass(vacancy.status)]">
                  {{ statusLabel(vacancy.status) }}
                </span>
              </td>
              <td class="py-3 px-4 text-surface-600 dark:text-surface-400">{{ vacancy.views_count || 0 }}</td>
              <td class="py-3 px-4 text-surface-600 dark:text-surface-400">{{ vacancy.applications_count || 0 }}</td>
              <td class="py-3 px-4 text-surface-500 text-xs">{{ formatDate(vacancy.created_at) }}</td>
              <td class="py-3 px-4 text-right" @click.stop>
                <div class="flex items-center justify-end gap-2">
                  <button
                    v-if="vacancy.status === 'pending'"
                    @click="approveVacancy(vacancy)"
                    class="text-xs px-3 py-1.5 rounded-lg font-medium bg-success-50 text-success-700 hover:bg-success-100 dark:bg-success-950/30 dark:text-success-400 transition-colors"
                  >
                    <CheckIcon class="w-4 h-4 inline" />
                  </button>
                  <button
                    v-if="vacancy.status === 'pending'"
                    @click="rejectVacancy(vacancy)"
                    class="text-xs px-3 py-1.5 rounded-lg font-medium bg-danger-50 text-danger-700 hover:bg-danger-100 dark:bg-danger-950/30 dark:text-danger-400 transition-colors"
                  >
                    <XMarkIcon class="w-4 h-4 inline" />
                  </button>
                </div>
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
import { CheckIcon, XMarkIcon } from '@heroicons/vue/24/outline';

const {
  items, total, currentPage, lastPage, loading, search, filters,
  fetchItems, goToPage, setSort, applySearch, applyFilter,
} = useResourceList('/vacancies');

function formatDate(d) {
  if (!d) return '';
  return new Date(d).toLocaleDateString('uz-UZ', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function statusClass(status) {
  const map = {
    active: 'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400',
    pending: 'bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-400',
    closed: 'bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-400',
    expired: 'bg-surface-100 text-surface-700 dark:bg-surface-800 dark:text-surface-400',
    rejected: 'bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-400',
  };
  return map[status] || 'bg-surface-100 text-surface-600';
}

function statusLabel(status) {
  const map = {
    active: 'Faol',
    pending: 'Kutilmoqda',
    closed: 'Yopilgan',
    expired: 'Muddati o\'tgan',
    rejected: 'Rad etilgan',
  };
  return map[status] || status;
}

async function approveVacancy(vacancy) {
  try {
    const res = await axios.post(`/api/admin/vacancies/${vacancy.id}/approve`);
    vacancy.status = 'active';
    toast.success(res.data.message || 'Vakansiya tasdiqlandi');
  } catch (err) {
    toast.error(err.response?.data?.message || 'Xatolik');
  }
}

async function rejectVacancy(vacancy) {
  try {
    const res = await axios.post(`/api/admin/vacancies/${vacancy.id}/reject`);
    vacancy.status = 'rejected';
    toast.success(res.data.message || 'Vakansiya rad etildi');
  } catch (err) {
    toast.error(err.response?.data?.message || 'Xatolik');
  }
}

onMounted(fetchItems);
</script>
