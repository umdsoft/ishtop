<template>
  <div class="space-y-6">
    <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">{{ $t('dashboard.title') }}</h1>

    <!-- Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
      <AdminStatCard
        :label="$t('dashboard.totalUsers')"
        :value="formatNumber(stats.total_users)"
        :description="`${stats.today_users} ${$t('dashboard.today')}`"
        :trend="userTrend"
        :icon="UsersIcon"
        color="brand"
      />
      <AdminStatCard
        :label="$t('dashboard.workersEmployers')"
        :value="`${stats.workers} / ${stats.employers}`"
        :description="$t('dashboard.registeredProfiles')"
        :icon="UserGroupIcon"
        color="brand"
      />
      <AdminStatCard
        :label="$t('dashboard.activeVacancies')"
        :value="formatNumber(stats.active_vacancies)"
        :description="`${stats.pending_vacancies} ${$t('dashboard.inModeration')}`"
        :icon="BriefcaseIcon"
        :color="stats.pending_vacancies > 0 ? 'warning' : 'success'"
      />
      <AdminStatCard
        :label="$t('dashboard.applications')"
        :value="formatNumber(stats.total_apps)"
        :description="`${stats.today_apps} ${$t('dashboard.today')}, ${stats.week_apps} ${$t('dashboard.weekly')}`"
        :icon="DocumentTextIcon"
        color="info"
      />
      <AdminStatCard
        :label="$t('dashboard.revenue')"
        :value="formatCurrency(stats.total_revenue)"
        :description="`${formatCurrency(stats.month_revenue)} ${$t('dashboard.thisMonth')}`"
        :icon="BanknotesIcon"
        color="success"
      />
      <AdminStatCard
        :label="$t('dashboard.views')"
        :value="formatNumber(stats.total_views)"
        :description="$t('dashboard.totalViews')"
        :icon="EyeIcon"
        color="gray"
      />
    </div>

    <!-- Pending Vacancies (Moderation) -->
    <AppCard v-if="pendingVacancies.length > 0" :title="$t('dashboard.pendingVacancies')">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-surface-200 dark:border-surface-800">
              <th class="text-left py-3 px-4 font-medium text-surface-500 dark:text-surface-400">{{ $t('vacancies.vacancy') }}</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500 dark:text-surface-400">{{ $t('vacancies.company') }}</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500 dark:text-surface-400">{{ $t('vacancies.category') }}</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500 dark:text-surface-400">{{ $t('vacancies.city') }}</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500 dark:text-surface-400">{{ $t('common.date') }}</th>
              <th class="text-right py-3 px-4 font-medium text-surface-500 dark:text-surface-400">{{ $t('common.actions') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="vacancy in pendingVacancies"
              :key="vacancy.id"
              class="border-b border-surface-100 dark:border-surface-800/50 hover:bg-surface-50 dark:hover:bg-surface-800/30"
            >
              <td class="py-3 px-4 font-medium text-surface-900 dark:text-surface-100">
                {{ vacancy.title_uz?.substring(0, 40) }}
              </td>
              <td class="py-3 px-4 text-surface-600 dark:text-surface-400">
                {{ vacancy.employer?.company_name || '—' }}
              </td>
              <td class="py-3 px-4 text-surface-600 dark:text-surface-400">{{ vacancy.category }}</td>
              <td class="py-3 px-4 text-surface-600 dark:text-surface-400">{{ vacancy.city }}</td>
              <td class="py-3 px-4 text-surface-500 dark:text-surface-400 text-xs">
                {{ formatDate(vacancy.created_at) }}
              </td>
              <td class="py-3 px-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button
                    @click="approveVacancy(vacancy)"
                    :disabled="vacancy._loading"
                    class="px-3 py-1.5 text-xs font-medium bg-success-50 dark:bg-success-950/30 text-success-700 dark:text-success-400 hover:bg-success-100 dark:hover:bg-success-900/40 rounded-lg transition-colors"
                  >
                    {{ $t('dashboard.approve') }}
                  </button>
                  <button
                    @click="rejectVacancy(vacancy)"
                    :disabled="vacancy._loading"
                    class="px-3 py-1.5 text-xs font-medium bg-danger-50 dark:bg-danger-950/30 text-danger-700 dark:text-danger-400 hover:bg-danger-100 dark:hover:bg-danger-900/40 rounded-lg transition-colors"
                  >
                    {{ $t('dashboard.reject') }}
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </AppCard>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Registration Chart -->
      <AppCard :title="$t('dashboard.registrations')">
        <div class="h-64">
          <Bar v-if="registrationData" :data="registrationData" :options="barOptions" />
        </div>
      </AppCard>

      <!-- Revenue Chart -->
      <AppCard :title="$t('dashboard.monthlyRevenue')">
        <div class="h-64">
          <Line v-if="revenueData" :data="revenueData" :options="lineOptions" />
        </div>
      </AppCard>
    </div>

    <!-- Bottom Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Top Categories -->
      <AppCard :title="$t('dashboard.topCategories')">
        <div class="h-64 flex items-center justify-center">
          <Doughnut v-if="categoriesData" :data="categoriesData" :options="doughnutOptions" />
        </div>
      </AppCard>

      <!-- Latest Vacancies -->
      <AppCard :title="$t('dashboard.latestVacancies')">
        <div class="space-y-3">
          <div
            v-for="vacancy in latestVacancies"
            :key="vacancy.id"
            class="flex items-center justify-between py-2 border-b border-surface-100 dark:border-surface-800/50 last:border-0 cursor-pointer hover:bg-surface-50 dark:hover:bg-surface-800/30 -mx-2 px-2 rounded-lg transition-colors"
            @click="$router.push(`/vacancies/${vacancy.id}`)"
          >
            <div class="min-w-0 flex-1">
              <p class="text-sm font-medium text-surface-900 dark:text-surface-100 truncate">{{ vacancy.title_uz }}</p>
              <p class="text-xs text-surface-500 dark:text-surface-400">{{ vacancy.employer?.company_name }} · {{ vacancy.category }}</p>
            </div>
            <span
              :class="[
                'text-xs px-2 py-0.5 rounded-full font-medium shrink-0 ml-3',
                statusClass(vacancy.status),
              ]"
            >
              {{ vacancy.status }}
            </span>
          </div>
          <p v-if="latestVacancies.length === 0" class="text-sm text-center text-surface-500 dark:text-surface-400 py-4">
            {{ $t('common.noData') }}
          </p>
        </div>
      </AppCard>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { toast } from 'vue-sonner';
import AppCard from '@panel/components/ui/AppCard.vue';
import AdminStatCard from '../../components/AdminStatCard.vue';
import {
  UsersIcon,
  UserGroupIcon,
  BriefcaseIcon,
  DocumentTextIcon,
  BanknotesIcon,
  EyeIcon,
} from '@heroicons/vue/24/outline';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  LineElement,
  PointElement,
  ArcElement,
  Filler,
  Tooltip,
  Legend,
} from 'chart.js';
import { Bar, Line, Doughnut } from 'vue-chartjs';

ChartJS.register(CategoryScale, LinearScale, BarElement, LineElement, PointElement, ArcElement, Filler, Tooltip, Legend);

const stats = ref({
  total_users: 0, today_users: 0, yesterday_users: 0,
  workers: 0, employers: 0,
  active_vacancies: 0, pending_vacancies: 0,
  total_apps: 0, today_apps: 0, week_apps: 0,
  total_revenue: 0, month_revenue: 0,
  total_views: 0,
});
const userTrend = ref(0);
const pendingVacancies = ref([]);
const latestVacancies = ref([]);
const registrationData = ref(null);
const revenueData = ref(null);
const categoriesData = ref(null);

const barOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: false } },
  scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
};
const lineOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: false } },
  scales: { y: { beginAtZero: true } },
};
const doughnutOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { position: 'bottom' } },
};

function formatNumber(n) {
  return n != null ? Number(n).toLocaleString() : '0';
}

function formatCurrency(n) {
  return n != null ? Number(n).toLocaleString() + " so'm" : "0 so'm";
}

function formatDate(d) {
  if (!d) return '';
  return new Date(d).toLocaleDateString('uz-UZ', { day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit' });
}

function statusClass(status) {
  return {
    active: 'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400',
    pending: 'bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-400',
    closed: 'bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-400',
    draft: 'bg-surface-100 text-surface-600 dark:bg-surface-800 dark:text-surface-400',
    paused: 'bg-info-100 text-info-700 dark:bg-info-900/30 dark:text-info-400',
    expired: 'bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-400',
  }[status] || 'bg-surface-100 text-surface-600';
}

async function approveVacancy(vacancy) {
  vacancy._loading = true;
  try {
    await axios.post(`/api/admin/vacancies/${vacancy.id}/approve`);
    pendingVacancies.value = pendingVacancies.value.filter(v => v.id !== vacancy.id);
    stats.value.pending_vacancies--;
    stats.value.active_vacancies++;
    toast.success('Vakansiya tasdiqlandi');
  } catch (err) {
    toast.error(err.response?.data?.message || 'Xatolik');
  }
  vacancy._loading = false;
}

async function rejectVacancy(vacancy) {
  vacancy._loading = true;
  try {
    await axios.post(`/api/admin/vacancies/${vacancy.id}/reject`);
    pendingVacancies.value = pendingVacancies.value.filter(v => v.id !== vacancy.id);
    stats.value.pending_vacancies--;
    toast.success('Vakansiya rad etildi');
  } catch (err) {
    toast.error(err.response?.data?.message || 'Xatolik');
  }
  vacancy._loading = false;
}

onMounted(async () => {
  // Fetch all dashboard data in parallel
  const [statsRes, pendingRes, latestRes, regRes, revRes, catRes] = await Promise.allSettled([
    axios.get('/api/admin/dashboard/stats'),
    axios.get('/api/admin/dashboard/pending-vacancies'),
    axios.get('/api/admin/dashboard/latest-vacancies'),
    axios.get('/api/admin/dashboard/charts/registrations'),
    axios.get('/api/admin/dashboard/charts/revenue'),
    axios.get('/api/admin/dashboard/charts/categories'),
  ]);

  if (statsRes.status === 'fulfilled') {
    stats.value = statsRes.value.data.stats;
    userTrend.value = statsRes.value.data.user_trend;
  }

  if (pendingRes.status === 'fulfilled') {
    pendingVacancies.value = pendingRes.value.data.vacancies || [];
  }

  if (latestRes.status === 'fulfilled') {
    latestVacancies.value = latestRes.value.data.vacancies || [];
  }

  if (regRes.status === 'fulfilled') {
    const d = regRes.value.data;
    registrationData.value = {
      labels: d.labels,
      datasets: [{
        label: 'Yangi foydalanuvchilar',
        data: d.counts,
        backgroundColor: 'rgba(99, 102, 241, 0.8)',
        borderColor: 'rgb(99, 102, 241)',
        borderRadius: 4,
      }],
    };
  }

  if (revRes.status === 'fulfilled') {
    const d = revRes.value.data;
    revenueData.value = {
      labels: d.labels,
      datasets: [{
        label: 'Daromad',
        data: d.amounts,
        backgroundColor: 'rgba(16, 185, 129, 0.15)',
        borderColor: 'rgb(16, 185, 129)',
        fill: true,
        tension: 0.3,
      }],
    };
  }

  if (catRes.status === 'fulfilled') {
    const d = catRes.value.data;
    categoriesData.value = {
      labels: d.labels,
      datasets: [{
        label: 'Vakansiyalar',
        data: d.counts,
        backgroundColor: [
          'rgba(99, 102, 241, 0.8)',
          'rgba(139, 92, 246, 0.8)',
          'rgba(236, 72, 153, 0.8)',
          'rgba(245, 158, 11, 0.8)',
          'rgba(16, 185, 129, 0.8)',
          'rgba(59, 130, 246, 0.8)',
        ],
        borderWidth: 0,
      }],
    };
  }
});
</script>
