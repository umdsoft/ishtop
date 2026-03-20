<template>
  <div class="space-y-6">
    <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">{{ $t('dashboard.title') }}</h1>

    <!-- Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
      <template v-if="loadingStats">
        <div v-for="i in 6" :key="i" class="rounded-xl border border-surface-200 dark:border-surface-700/60 bg-white dark:bg-surface-800/80 p-4 animate-pulse">
          <div class="flex items-center justify-between mb-3">
            <div class="h-3 w-20 bg-surface-200 dark:bg-surface-700 rounded" />
            <div class="h-8 w-8 bg-surface-200 dark:bg-surface-700 rounded-lg" />
          </div>
          <div class="h-7 w-16 bg-surface-200 dark:bg-surface-700 rounded mb-2" />
          <div class="h-3 w-24 bg-surface-100 dark:bg-surface-700/50 rounded" />
        </div>
      </template>
      <template v-else>
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
      </template>
    </div>

    <!-- Pending Vacancies (Moderation) -->
    <AppCard v-if="loadingPending || pendingVacancies.length > 0" :title="$t('dashboard.pendingVacancies')">
      <!-- Skeleton table -->
      <div v-if="loadingPending" class="space-y-4 animate-pulse">
        <div v-for="i in 3" :key="i" class="flex items-center gap-4 py-3">
          <div class="h-4 w-1/4 bg-surface-200 dark:bg-surface-700 rounded" />
          <div class="h-4 w-1/6 bg-surface-200 dark:bg-surface-700 rounded" />
          <div class="h-4 w-1/6 bg-surface-100 dark:bg-surface-700/50 rounded" />
          <div class="h-4 w-1/6 bg-surface-100 dark:bg-surface-700/50 rounded" />
          <div class="flex-1" />
          <div class="flex gap-2">
            <div class="h-7 w-16 bg-surface-200 dark:bg-surface-700 rounded-lg" />
            <div class="h-7 w-16 bg-surface-200 dark:bg-surface-700 rounded-lg" />
          </div>
        </div>
      </div>
      <!-- Real table -->
      <div v-else class="overflow-x-auto">
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
              <td class="py-3 px-4 text-surface-600 dark:text-surface-400">{{ vacancy.category_name || vacancy.category }}</td>
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
          <div v-if="loadingCharts" class="h-full flex items-end gap-2 animate-pulse pb-6 px-4">
            <div v-for="i in 14" :key="i" class="flex-1 bg-surface-200 dark:bg-surface-700 rounded-t" :style="{ height: randomHeight() }" />
          </div>
          <Bar v-else-if="registrationData" :data="registrationData" :options="barOptions" />
          <div v-else class="h-full flex items-center justify-center text-surface-400 text-sm">{{ $t('common.noData') }}</div>
        </div>
      </AppCard>

      <!-- Revenue Chart -->
      <AppCard :title="$t('dashboard.monthlyRevenue')">
        <div class="h-64">
          <div v-if="loadingCharts" class="h-full flex items-center justify-center animate-pulse">
            <svg class="w-full h-3/4 px-4" viewBox="0 0 300 100" preserveAspectRatio="none">
              <path d="M0,80 Q50,40 100,60 T200,30 T300,50" fill="none" stroke="currentColor" stroke-width="2" class="text-surface-200 dark:text-surface-700" />
              <path d="M0,80 Q50,40 100,60 T200,30 T300,50 V100 H0 Z" class="text-surface-100 dark:text-surface-800 fill-current" />
            </svg>
          </div>
          <Line v-else-if="revenueData" :data="revenueData" :options="lineOptions" />
          <div v-else class="h-full flex items-center justify-center text-surface-400 text-sm">{{ $t('common.noData') }}</div>
        </div>
      </AppCard>
    </div>

    <!-- Bottom Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Top Categories -->
      <AppCard :title="$t('dashboard.topCategories')">
        <div v-if="loadingCharts" class="space-y-3 animate-pulse">
          <div v-for="i in 6" :key="i" class="flex items-center gap-3">
            <div class="h-3 rounded-full bg-surface-200 dark:bg-surface-700 flex-1" :style="{ maxWidth: `${80 - i * 8}%` }" />
            <div class="h-3 w-8 bg-surface-200 dark:bg-surface-700 rounded" />
          </div>
        </div>
        <div v-else-if="categoriesRaw.length > 0" class="flex flex-col gap-4">
          <!-- Pie Chart -->
          <div class="h-48 flex items-center justify-center">
            <Doughnut :data="categoriesChartData" :options="doughnutOptions" />
          </div>
          <!-- Legend list -->
          <div class="space-y-2">
            <div v-for="(cat, i) in categoriesRaw" :key="i" class="flex items-center gap-3">
              <div class="w-2.5 h-2.5 rounded-full shrink-0" :style="{ backgroundColor: categoryColors[i] }" />
              <span class="text-sm text-surface-700 dark:text-surface-300 flex-1 truncate">{{ cat.label }}</span>
              <div class="flex items-center gap-2 shrink-0">
                <div class="w-20 h-1.5 rounded-full bg-surface-100 dark:bg-surface-800 overflow-hidden">
                  <div class="h-full rounded-full" :style="{ width: cat.pct + '%', backgroundColor: categoryColors[i] }" />
                </div>
                <span class="text-xs font-medium text-surface-500 dark:text-surface-400 w-8 text-right">{{ cat.count }}</span>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="h-32 flex items-center justify-center text-surface-400 text-sm">{{ $t('common.noData') }}</div>
      </AppCard>

      <!-- Latest Vacancies -->
      <AppCard :title="$t('dashboard.latestVacancies')">
        <div class="space-y-0">
          <template v-if="loadingLatest">
            <div v-for="i in 5" :key="i" class="flex items-center gap-3 py-3 animate-pulse border-b border-surface-100 dark:border-surface-800/50 last:border-0">
              <div class="min-w-0 flex-1 space-y-2">
                <div class="h-4 w-3/4 bg-surface-200 dark:bg-surface-700 rounded" />
                <div class="h-3 w-1/2 bg-surface-100 dark:bg-surface-700/50 rounded" />
              </div>
              <div class="h-6 w-6 bg-surface-200 dark:bg-surface-700 rounded" />
            </div>
          </template>
          <template v-else>
            <div
              v-for="vacancy in latestVacancies"
              :key="vacancy.id"
              class="flex items-center gap-3 py-3 border-b border-surface-100 dark:border-surface-800/50 last:border-0"
            >
              <div class="min-w-0 flex-1 cursor-pointer" @click="$router.push(`/vacancies/${vacancy.id}`)">
                <p class="text-sm font-medium text-surface-900 dark:text-surface-100 truncate">{{ vacancy.title_uz }}</p>
                <p class="text-xs text-surface-500 dark:text-surface-400 mt-0.5">
                  {{ vacancy.employer?.company_name }}
                  <span v-if="vacancy.category_name"> · {{ vacancy.category_name }}</span>
                  <span v-if="vacancy.city"> · {{ vacancy.city }}</span>
                </p>
              </div>
              <button
                @click.stop="toggleVacancyStatus(vacancy)"
                :disabled="vacancy._toggling"
                :class="[
                  'shrink-0 relative w-9 h-5 rounded-full transition-colors duration-200 focus:outline-none',
                  vacancy.status === 'active'
                    ? 'bg-success-500'
                    : 'bg-surface-300 dark:bg-surface-600',
                  vacancy._toggling ? 'opacity-50' : '',
                ]"
                :title="vacancy.status === 'active' ? 'Noaktiv qilish' : 'Aktiv qilish'"
              >
                <span
                  :class="[
                    'absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200',
                    vacancy.status === 'active' ? 'translate-x-4' : 'translate-x-0',
                  ]"
                />
              </button>
            </div>
            <p v-if="latestVacancies.length === 0" class="text-sm text-center text-surface-500 dark:text-surface-400 py-4">
              {{ $t('common.noData') }}
            </p>
          </template>
        </div>
      </AppCard>
    </div>

    <!-- Close Reason Modal -->
    <div v-if="showReasonModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="cancelClose">
      <div class="bg-white dark:bg-surface-900 rounded-xl shadow-xl w-full max-w-md mx-4 border border-surface-200 dark:border-surface-800">
        <div class="px-6 py-4 border-b border-surface-200 dark:border-surface-800">
          <h3 class="text-lg font-semibold text-surface-900 dark:text-surface-100">Vakansiyani noaktiv qilish</h3>
          <p class="text-sm text-surface-500 dark:text-surface-400 mt-1">Iltimos, sababni kiriting</p>
        </div>
        <div class="px-6 py-4">
          <textarea
            v-model="closeReason"
            rows="3"
            class="w-full px-3 py-2 bg-surface-50 dark:bg-surface-800 border border-surface-300 dark:border-surface-700 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 resize-none"
            placeholder="Masalan: Vakansiya muddati tugagan, nomuvofiq kontent..."
          />
        </div>
        <div class="px-6 py-4 border-t border-surface-200 dark:border-surface-800 flex justify-end gap-3">
          <button @click="cancelClose"
            class="px-4 py-2 text-sm font-medium text-surface-700 dark:text-surface-300 hover:bg-surface-100 dark:hover:bg-surface-800 rounded-lg transition-colors">
            Bekor qilish
          </button>
          <button @click="confirmClose" :disabled="!closeReason.trim()"
            class="px-4 py-2 bg-danger-500 text-white text-sm font-medium rounded-lg hover:bg-danger-600 transition-colors disabled:opacity-50">
            Noaktiv qilish
          </button>
        </div>
      </div>
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
import { computed } from 'vue';

ChartJS.register(CategoryScale, LinearScale, BarElement, LineElement, PointElement, ArcElement, Filler, Tooltip, Legend);

const loadingStats = ref(true);
const loadingPending = ref(true);
const loadingLatest = ref(true);
const loadingCharts = ref(true);

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
const categoriesRaw = ref([]);
const categoryColors = [
  '#6366f1', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981', '#3b82f6',
];

const categoriesChartData = computed(() => ({
  labels: categoriesRaw.value.map(c => c.label),
  datasets: [{
    data: categoriesRaw.value.map(c => c.count),
    backgroundColor: categoryColors.slice(0, categoriesRaw.value.length),
    borderWidth: 0,
    hoverOffset: 6,
  }],
}));

const doughnutOptions = {
  responsive: true,
  maintainAspectRatio: false,
  cutout: '60%',
  plugins: { legend: { display: false } },
};

// Close reason modal
const showReasonModal = ref(false);
const closeReason = ref('');
const closingVacancy = ref(null);

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

// Random heights for bar chart skeleton
const heights = Array.from({ length: 14 }, () => `${20 + Math.random() * 60}%`);
let hIdx = 0;
function randomHeight() {
  return heights[hIdx++ % heights.length];
}

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

function toggleVacancyStatus(vacancy) {
  if (vacancy.status === 'active') {
    // Show reason modal before deactivating
    closingVacancy.value = vacancy;
    closeReason.value = '';
    showReasonModal.value = true;
  } else {
    // Reactivate directly
    doToggle(vacancy, 'active');
  }
}

function cancelClose() {
  showReasonModal.value = false;
  closingVacancy.value = null;
  closeReason.value = '';
}

async function confirmClose() {
  if (!closingVacancy.value || !closeReason.value.trim()) return;
  showReasonModal.value = false;
  await doToggle(closingVacancy.value, 'closed', closeReason.value.trim());
  closingVacancy.value = null;
  closeReason.value = '';
}

async function doToggle(vacancy, newStatus, reason = null) {
  vacancy._toggling = true;
  try {
    const payload = { status: newStatus };
    if (reason) payload.close_reason = reason;
    await axios.put(`/api/admin/vacancies/${vacancy.id}`, payload);
    vacancy.status = newStatus;
    if (newStatus === 'active') {
      stats.value.active_vacancies++;
    } else {
      stats.value.active_vacancies = Math.max(0, stats.value.active_vacancies - 1);
    }
    toast.success(newStatus === 'active' ? 'Vakansiya faollashtirildi' : 'Vakansiya noaktiv qilindi');
  } catch (err) {
    toast.error(err.response?.data?.message || 'Xatolik');
  }
  vacancy._toggling = false;
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
  // Fetch stats first (most visible)
  axios.get('/api/admin/dashboard/stats').then(res => {
    stats.value = res.data.stats;
    userTrend.value = res.data.user_trend;
  }).catch(() => {}).finally(() => { loadingStats.value = false; });

  // Pending vacancies
  axios.get('/api/admin/dashboard/pending-vacancies').then(res => {
    pendingVacancies.value = res.data.vacancies || [];
  }).catch(() => {}).finally(() => { loadingPending.value = false; });

  // Latest vacancies
  axios.get('/api/admin/dashboard/latest-vacancies').then(res => {
    latestVacancies.value = res.data.vacancies || [];
  }).catch(() => {}).finally(() => { loadingLatest.value = false; });

  // Charts (all 3 in parallel, single loading flag)
  Promise.allSettled([
    axios.get('/api/admin/dashboard/charts/registrations'),
    axios.get('/api/admin/dashboard/charts/revenue'),
    axios.get('/api/admin/dashboard/charts/categories'),
  ]).then(([regRes, revRes, catRes]) => {
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
      const maxCount = Math.max(...d.counts, 1);
      categoriesRaw.value = d.labels.map((label, i) => ({
        label,
        count: d.counts[i],
        pct: Math.round((d.counts[i] / maxCount) * 100),
      }));
    }
  }).finally(() => { loadingCharts.value = false; });
});
</script>
