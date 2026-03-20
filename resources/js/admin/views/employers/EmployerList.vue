<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">{{ $t('employers.title') }}</h1>
    </div>

    <!-- Filters -->
    <AppCard>
      <div class="flex flex-wrap items-center gap-3">
        <AppSearchInput v-model="search" :placeholder="$t('common.search')" @update:modelValue="applySearch" class="w-64" />
      </div>
    </AppCard>

    <!-- Table -->
    <AppCard noPadding>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-surface-200 dark:border-surface-800">
              <th class="text-left py-3 px-4 font-medium text-surface-500 cursor-pointer" @click="setSort('company_name')">Kompaniya</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500">Foydalanuvchi</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500">Telefon</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500">Vakansiyalar</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500 cursor-pointer" @click="setSort('created_at')">{{ $t('common.date') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="employer in items"
              :key="employer.id"
              class="border-b border-surface-100 dark:border-surface-800/50 hover:bg-surface-50 dark:hover:bg-surface-800/30 cursor-pointer"
              @click="$router.push(`/employers/${employer.id}`)"
            >
              <td class="py-3 px-4">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-full bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center text-brand-600 dark:text-brand-400 text-xs font-bold">
                    {{ (employer.company_name?.[0] || 'E').toUpperCase() }}
                  </div>
                  <p class="font-medium text-surface-900 dark:text-surface-100">{{ employer.company_name || '—' }}</p>
                </div>
              </td>
              <td class="py-3 px-4 text-surface-600 dark:text-surface-400">{{ employer.user?.first_name }} {{ employer.user?.last_name }}</td>
              <td class="py-3 px-4 text-surface-600 dark:text-surface-400">{{ employer.phone || employer.user?.phone || '—' }}</td>
              <td class="py-3 px-4 text-surface-600 dark:text-surface-400">{{ employer.vacancies_count || 0 }}</td>
              <td class="py-3 px-4 text-surface-500 text-xs">{{ formatDate(employer.created_at) }}</td>
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
import { formatDate } from '@/shared/formatters';

const {
  items, total, currentPage, lastPage, loading, search, filters,
  fetchItems, goToPage, setSort, applySearch, applyFilter,
} = useResourceList('/employers');

onMounted(fetchItems);
</script>
