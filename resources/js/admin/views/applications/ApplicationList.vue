<template>
  <div class="space-y-6">
    <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">{{ $t('applications.title') }}</h1>

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
          <option value="new">{{ $t('applications.new') }}</option>
          <option value="reviewed">{{ $t('applications.reviewed') }}</option>
          <option value="shortlisted">{{ $t('applications.shortlisted') }}</option>
          <option value="interview">{{ $t('applications.interview') }}</option>
          <option value="offered">{{ $t('applications.offered') }}</option>
          <option value="hired">{{ $t('applications.hired') }}</option>
          <option value="rejected">{{ $t('applications.rejected') }}</option>
        </select>
      </div>
    </AppCard>

    <!-- Table -->
    <AppCard noPadding>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-surface-200 dark:border-surface-800">
              <th class="text-left py-3 px-4 font-medium text-surface-500">{{ $t('applications.vacancy') }}</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500">{{ $t('applications.worker') }}</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500">{{ $t('applications.stage') }}</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500">{{ $t('applications.score') }}</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500">Manba</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500 cursor-pointer" @click="setSort('created_at')">{{ $t('common.date') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="app in items"
              :key="app.id"
              class="border-b border-surface-100 dark:border-surface-800/50 hover:bg-surface-50 dark:hover:bg-surface-800/30 cursor-pointer"
              @click="$router.push(`/applications/${app.id}`)"
            >
              <td class="py-3 px-4">
                <p class="font-medium text-surface-900 dark:text-surface-100 truncate max-w-xs">{{ app.vacancy?.title_uz || '—' }}</p>
                <p class="text-xs text-surface-500 dark:text-surface-400 mt-0.5">{{ app.vacancy?.employer?.company_name || '' }}</p>
              </td>
              <td class="py-3 px-4">
                <div class="flex items-center gap-3">
                  <div v-if="app.user?.avatar_url" class="w-8 h-8 rounded-full overflow-hidden shrink-0">
                    <img :src="app.user.avatar_url" class="w-full h-full object-cover" />
                  </div>
                  <div v-else class="w-8 h-8 rounded-full bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center text-brand-600 dark:text-brand-400 text-xs font-bold shrink-0">
                    {{ (app.user?.first_name?.[0] || '?').toUpperCase() }}
                  </div>
                  <div class="min-w-0">
                    <p class="font-medium text-surface-900 dark:text-surface-100 truncate">{{ app.user?.first_name }} {{ app.user?.last_name }}</p>
                    <p class="text-xs text-surface-500 dark:text-surface-400">{{ app.user?.phone || '' }}</p>
                  </div>
                </div>
              </td>
              <td class="py-3 px-4">
                <span :class="['text-xs px-2 py-0.5 rounded-full font-medium', stageClass(app.stage)]">
                  {{ stageLabel(app.stage) }}
                </span>
              </td>
              <td class="py-3 px-4">
                <template v-if="app.questionnaire_score != null">
                  <div class="flex items-center gap-2">
                    <div class="w-16 h-1.5 rounded-full bg-surface-100 dark:bg-surface-800 overflow-hidden">
                      <div
                        class="h-full rounded-full"
                        :class="scoreBarColor(app.questionnaire_score, app.questionnaire_max_score)"
                        :style="{ width: scorePct(app.questionnaire_score, app.questionnaire_max_score) + '%' }"
                      />
                    </div>
                    <span class="text-xs font-medium text-surface-600 dark:text-surface-400">
                      {{ Math.round(app.questionnaire_score) }}/{{ Math.round(app.questionnaire_max_score || 100) }}
                    </span>
                  </div>
                </template>
                <span v-else class="text-surface-400 text-xs">—</span>
              </td>
              <td class="py-3 px-4 text-surface-600 dark:text-surface-400 text-xs capitalize">{{ app.source || '—' }}</td>
              <td class="py-3 px-4 text-surface-500 text-xs whitespace-nowrap">{{ formatDate(app.created_at) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-if="loading" class="p-8 text-center text-surface-500">{{ $t('common.loading') }}</div>
      <div v-if="!loading && items.length === 0" class="p-8 text-center text-surface-500">
        <DocumentTextIcon class="w-10 h-10 mx-auto text-surface-300 dark:text-surface-600 mb-2" />
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
import { DocumentTextIcon } from '@heroicons/vue/24/outline';

const {
  items, total, currentPage, lastPage, loading, search, filters,
  fetchItems, goToPage, setSort, applySearch, applyFilter,
} = useResourceList('/applications');

function formatDate(d) {
  if (!d) return '';
  return new Date(d).toLocaleDateString('uz-UZ', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function scorePct(score, max) {
  if (!max || max === 0) return 0;
  return Math.min(100, Math.round((score / max) * 100));
}

function scoreBarColor(score, max) {
  const pct = scorePct(score, max);
  if (pct >= 80) return 'bg-success-500';
  if (pct >= 50) return 'bg-warning-500';
  return 'bg-danger-500';
}

function stageClass(stage) {
  const map = {
    new: 'bg-brand-100 text-brand-700 dark:bg-brand-900/30 dark:text-brand-400',
    reviewed: 'bg-info-100 text-info-700 dark:bg-info-900/30 dark:text-info-400',
    shortlisted: 'bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-400',
    interview: 'bg-brand-100 text-brand-700 dark:bg-brand-900/30 dark:text-brand-400',
    offered: 'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400',
    hired: 'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400',
    rejected: 'bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-400',
    withdrawn: 'bg-surface-100 text-surface-700 dark:bg-surface-800 dark:text-surface-400',
  };
  return map[stage] || 'bg-surface-100 text-surface-600';
}

function stageLabel(stage) {
  const map = {
    new: 'Yangi',
    reviewed: "Ko'rilgan",
    shortlisted: 'Tanlangan',
    interview: 'Intervyu',
    offered: 'Taklif',
    hired: 'Ishga olingan',
    rejected: 'Rad etilgan',
    withdrawn: 'Qaytarilgan',
  };
  return map[stage] || stage;
}

onMounted(fetchItems);
</script>
