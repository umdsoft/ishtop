<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">{{ $t('users.title') }}</h1>
    </div>

    <!-- Stats cards -->
    <div v-if="statsLoading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
      <div v-for="i in 6" :key="i" class="rounded-2xl bg-white dark:bg-surface-900 border border-surface-100 dark:border-surface-800 p-4">
        <div class="animate-pulse space-y-2">
          <div class="h-3 bg-surface-200 dark:bg-surface-700 rounded w-2/3"></div>
          <div class="h-7 bg-surface-200 dark:bg-surface-700 rounded w-1/2"></div>
        </div>
      </div>
    </div>
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
      <div
        v-for="card in statsCards"
        :key="card.label"
        class="rounded-2xl bg-white dark:bg-surface-900 border border-surface-100 dark:border-surface-800 p-4 hover:shadow-md transition-shadow"
      >
        <div class="flex items-center justify-between mb-2">
          <span class="text-xs font-medium text-surface-500 uppercase tracking-wide">{{ card.label }}</span>
          <div class="w-8 h-8 rounded-lg flex items-center justify-center" :class="card.bgClass">
            <component :is="card.icon" class="w-4 h-4" :class="card.iconClass" />
          </div>
        </div>
        <p class="text-2xl font-bold" :class="card.valueClass">{{ card.value.toLocaleString() }}</p>
      </div>
    </div>

    <!-- Filters -->
    <AppCard>
      <div class="flex flex-wrap items-center gap-3">
        <AppSearchInput v-model="search" :placeholder="$t('common.search')" @update:modelValue="applySearch" class="w-64" />
        <select
          v-model="filters.is_blocked"
          @change="applyFilter('is_blocked', filters.is_blocked)"
          class="px-3 py-2 bg-surface-50 dark:bg-surface-800 border border-surface-300 dark:border-surface-700 rounded-lg text-sm"
        >
          <option value="">{{ $t('common.all') }}</option>
          <option value="0">{{ $t('common.active') }}</option>
          <option value="1">{{ $t('common.blocked') }}</option>
        </select>
      </div>
    </AppCard>

    <!-- Table -->
    <AppCard noPadding>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-surface-200 dark:border-surface-800">
              <th class="text-left py-3 px-4 font-medium text-surface-500 cursor-pointer" @click="setSort('first_name')">{{ $t('users.firstName') }}</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500">{{ $t('users.phone') }}</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500">{{ $t('users.email') }}</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500">Rol</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500">{{ $t('common.status') }}</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500 cursor-pointer" @click="setSort('created_at')">{{ $t('common.date') }}</th>
              <th class="text-right py-3 px-4 font-medium text-surface-500">{{ $t('common.actions') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="user in items"
              :key="user.id"
              class="border-b border-surface-100 dark:border-surface-800/50 hover:bg-surface-50 dark:hover:bg-surface-800/30 cursor-pointer"
              @click="$router.push(`/users/${user.id}`)"
            >
              <td class="py-3 px-4">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-full bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center text-brand-600 dark:text-brand-400 text-xs font-bold">
                    {{ (user.first_name?.[0] || 'U').toUpperCase() }}
                  </div>
                  <div>
                    <p class="font-medium text-surface-900 dark:text-surface-100">{{ user.first_name }} {{ user.last_name }}</p>
                    <p v-if="user.username" class="text-xs text-surface-500">@{{ user.username }}</p>
                  </div>
                </div>
              </td>
              <td class="py-3 px-4 text-surface-600 dark:text-surface-400">{{ user.phone || '—' }}</td>
              <td class="py-3 px-4 text-surface-600 dark:text-surface-400">{{ user.email || '—' }}</td>
              <td class="py-3 px-4">
                <div class="flex flex-wrap gap-1">
                  <span v-if="user.worker_profile_count" class="text-xs px-1.5 py-0.5 rounded bg-brand-100 dark:bg-brand-900/30 text-brand-700 dark:text-brand-400 font-medium">Ishchi</span>
                  <span v-if="user.employer_profiles_count" class="text-xs px-1.5 py-0.5 rounded bg-info-100 dark:bg-info-900/30 text-info-700 dark:text-info-400 font-medium">Ish beruvchi</span>
                  <span v-if="!user.worker_profile_count && !user.employer_profiles_count" class="text-xs text-surface-400">—</span>
                </div>
              </td>
              <td class="py-3 px-4">
                <span :class="['text-xs px-2 py-0.5 rounded-full font-medium', user.is_blocked ? 'bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-400' : 'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400']">
                  {{ user.is_blocked ? $t('common.blocked') : $t('common.active') }}
                </span>
              </td>
              <td class="py-3 px-4 text-surface-500 text-xs">{{ formatDate(user.created_at) }}</td>
              <td class="py-3 px-4 text-right" @click.stop>
                <button
                  @click="toggleBlock(user)"
                  :class="['text-xs px-3 py-1.5 rounded-lg font-medium transition-colors', user.is_blocked ? 'bg-success-50 text-success-700 hover:bg-success-100 dark:bg-success-950/30 dark:text-success-400' : 'bg-danger-50 text-danger-700 hover:bg-danger-100 dark:bg-danger-950/30 dark:text-danger-400']"
                >
                  {{ user.is_blocked ? $t('users.unblock') : $t('users.block') }}
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
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import { toast } from 'vue-sonner';
import { useResourceList } from '../../composables/useAdminApi';
import AppCard from '@panel/components/ui/AppCard.vue';
import AppSearchInput from '@panel/components/ui/AppSearchInput.vue';
import AppPagination from '@panel/components/ui/AppPagination.vue';
import {
  UsersIcon, UserPlusIcon, UserGroupIcon, ShieldExclamationIcon,
  BriefcaseIcon, BuildingOffice2Icon,
} from '@heroicons/vue/24/outline';
import { formatDate } from '@/shared/formatters';

const {
  items, total, currentPage, lastPage, loading, search, filters,
  fetchItems, goToPage, setSort, applySearch, applyFilter,
} = useResourceList('/users');

// Stats
const statsLoading = ref(true);
const stats = ref({});

const statsCards = computed(() => [
  {
    label: 'Jami',
    value: stats.value.total_users || 0,
    icon: UsersIcon,
    bgClass: 'bg-brand-100 dark:bg-brand-900/30',
    iconClass: 'text-brand-600 dark:text-brand-400',
    valueClass: 'text-brand-700 dark:text-brand-400',
  },
  {
    label: 'Bugun',
    value: stats.value.today_users || 0,
    icon: UserPlusIcon,
    bgClass: 'bg-info-100 dark:bg-info-900/30',
    iconClass: 'text-info-600 dark:text-info-400',
    valueClass: 'text-info-700 dark:text-info-400',
  },
  {
    label: 'Faol',
    value: stats.value.active_users || 0,
    icon: UserGroupIcon,
    bgClass: 'bg-success-100 dark:bg-success-900/30',
    iconClass: 'text-success-600 dark:text-success-400',
    valueClass: 'text-success-700 dark:text-success-400',
  },
  {
    label: 'Bloklangan',
    value: stats.value.blocked_users || 0,
    icon: ShieldExclamationIcon,
    bgClass: 'bg-danger-100 dark:bg-danger-900/30',
    iconClass: 'text-danger-600 dark:text-danger-400',
    valueClass: 'text-danger-700 dark:text-danger-400',
  },
  {
    label: 'Ishchilar',
    value: stats.value.workers || 0,
    icon: BriefcaseIcon,
    bgClass: 'bg-warning-100 dark:bg-warning-900/30',
    iconClass: 'text-warning-600 dark:text-warning-400',
    valueClass: 'text-warning-700 dark:text-warning-400',
  },
  {
    label: 'Ish beruvchilar',
    value: stats.value.employers || 0,
    icon: BuildingOffice2Icon,
    bgClass: 'bg-brand-100 dark:bg-brand-900/30',
    iconClass: 'text-brand-600 dark:text-brand-400',
    valueClass: 'text-brand-700 dark:text-brand-400',
  },
]);

async function fetchStats() {
  statsLoading.value = true;
  try {
    const res = await axios.get('/api/admin/users/stats');
    stats.value = res.data;
  } catch {
    // Stats are non-critical, fail silently
  } finally {
    statsLoading.value = false;
  }
}

async function toggleBlock(user) {
  try {
    const res = await axios.post(`/api/admin/users/${user.id}/toggle-block`);
    user.is_blocked = res.data.user.is_blocked;
    toast.success(res.data.message);
    fetchStats(); // Refresh stats after block/unblock
  } catch (err) {
    toast.error(err.response?.data?.message || 'Xatolik');
  }
}

onMounted(() => {
  fetchItems();
  fetchStats();
});
</script>
