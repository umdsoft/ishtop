<template>
  <div class="space-y-6">
    <!-- Welcome Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl md:text-3xl font-bold text-surface-900 dark:text-surface-100">
          {{ $t('dashboard.welcome', { name: user?.first_name || 'User' }) }}
        </h1>
        <p class="mt-1 text-surface-600 dark:text-surface-400">
          {{ $t('dashboard.overview') }}
        </p>
      </div>

      <router-link to="/dashboard/vacancies/create">
        <AppButton variant="primary">
          <template #icon-left>
            <PlusIcon class="w-5 h-5" />
          </template>
          Yangi e'lon
        </AppButton>
      </router-link>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
      <AppCard>
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-full bg-brand-100 dark:bg-brand-900/50 flex items-center justify-center">
            <BriefcaseIcon class="w-6 h-6 text-brand-600 dark:text-brand-400" />
          </div>
          <div class="flex-1">
            <p class="text-sm text-surface-600 dark:text-surface-400">{{ $t('dashboard.activeVacancies') }}</p>
            <p class="text-2xl font-bold text-surface-900 dark:text-surface-100">
              {{ statsLoading ? '—' : stats.active_vacancies }}
            </p>
            <p class="text-xs text-surface-500 dark:text-surface-400">
              Jami: {{ stats.total_vacancies }}
            </p>
          </div>
        </div>
      </AppCard>

      <AppCard>
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-full bg-info-100 dark:bg-info-900/50 flex items-center justify-center">
            <DocumentTextIcon class="w-6 h-6 text-info-600 dark:text-info-400" />
          </div>
          <div class="flex-1">
            <p class="text-sm text-surface-600 dark:text-surface-400">{{ $t('dashboard.todayApplications') }}</p>
            <p class="text-2xl font-bold text-surface-900 dark:text-surface-100">
              {{ statsLoading ? '—' : stats.total_applications }}
            </p>
            <p class="text-xs text-surface-500 dark:text-surface-400">
              Yangi: {{ stats.new_applications }}
            </p>
          </div>
        </div>
      </AppCard>

      <AppCard>
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-full bg-warning-100 dark:bg-warning-900/50 flex items-center justify-center">
            <EyeIcon class="w-6 h-6 text-warning-600 dark:text-warning-400" />
          </div>
          <div class="flex-1">
            <p class="text-sm text-surface-600 dark:text-surface-400">Ko'rishlar</p>
            <p class="text-2xl font-bold text-surface-900 dark:text-surface-100">
              {{ statsLoading ? '—' : stats.total_views }}
            </p>
          </div>
        </div>
      </AppCard>

      <AppCard>
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-full bg-success-100 dark:bg-success-900/50 flex items-center justify-center">
            <CheckCircleIcon class="w-6 h-6 text-success-600 dark:text-success-400" />
          </div>
          <div class="flex-1">
            <p class="text-sm text-surface-600 dark:text-surface-400">Ishga olindi</p>
            <p class="text-2xl font-bold text-surface-900 dark:text-surface-100">
              {{ statsLoading ? '—' : stats.hired_count }}
            </p>
          </div>
        </div>
      </AppCard>
    </div>

    <!-- Lists -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Recent Applications -->
      <AppCard :title="$t('dashboard.recentApplications')">
        <template v-if="applicationsLoading">
          <div class="flex items-center justify-center py-8">
            <div class="w-6 h-6 border-2 border-brand-500 border-t-transparent rounded-full animate-spin"></div>
          </div>
        </template>

        <template v-else-if="applications.length === 0">
          <div class="text-center py-8">
            <DocumentTextIcon class="w-12 h-12 mx-auto text-surface-300 dark:text-surface-600" />
            <p class="mt-3 text-sm text-surface-500 dark:text-surface-400">
              Hali arizalar yo'q
            </p>
            <p class="text-xs text-surface-400 dark:text-surface-500 mt-1">
              E'lon joylashtirsangiz, arizalar bu yerda ko'rinadi
            </p>
          </div>
        </template>

        <div v-else class="space-y-1">
          <router-link
            v-for="app in applications"
            :key="app.id"
            :to="`/dashboard/vacancies/${app.vacancy?.id}`"
            class="flex items-center gap-3 p-3 hover:bg-surface-50 dark:hover:bg-surface-800 rounded-lg transition-colors"
          >
            <div class="w-10 h-10 rounded-full bg-brand-100 dark:bg-brand-900 flex items-center justify-center text-brand-600 dark:text-brand-400 font-semibold text-sm">
              {{ getInitials(app.worker?.full_name) }}
            </div>
            <div class="flex-1 min-w-0">
              <p class="font-medium text-surface-900 dark:text-surface-100 truncate">
                {{ app.worker?.full_name || 'Nomallum' }}
              </p>
              <p class="text-xs text-surface-500 dark:text-surface-400 truncate">
                {{ app.vacancy?.title || '—' }}
              </p>
            </div>
            <div class="text-right shrink-0">
              <span
                class="inline-block px-2 py-1 text-xs font-semibold rounded-full"
                :class="stageClass(app.stage)"
              >
                {{ stageLabel(app.stage) }}
              </span>
              <p class="text-xs text-surface-500 dark:text-surface-400 mt-1">
                {{ timeAgo(app.created_at) }}
              </p>
            </div>
          </router-link>
        </div>
      </AppCard>

      <!-- Quick Actions / Get Started -->
      <AppCard :title="stats.total_vacancies > 0 ? $t('dashboard.quickActions') : 'Boshlash'">
        <div class="space-y-3">
          <router-link
            to="/dashboard/vacancies/create"
            class="flex items-center gap-3 p-3 hover:bg-surface-50 dark:hover:bg-surface-800 rounded-lg transition-colors"
          >
            <div class="w-10 h-10 rounded-full bg-brand-100 dark:bg-brand-900/50 flex items-center justify-center">
              <PlusIcon class="w-5 h-5 text-brand-600 dark:text-brand-400" />
            </div>
            <div class="flex-1">
              <p class="font-medium text-surface-900 dark:text-surface-100">Yangi e'lon yaratish</p>
              <p class="text-xs text-surface-500 dark:text-surface-400">Vakansiya e'lonini joylashtiring</p>
            </div>
          </router-link>

          <router-link
            to="/dashboard/vacancies"
            class="flex items-center gap-3 p-3 hover:bg-surface-50 dark:hover:bg-surface-800 rounded-lg transition-colors"
          >
            <div class="w-10 h-10 rounded-full bg-info-100 dark:bg-info-900/50 flex items-center justify-center">
              <BriefcaseIcon class="w-5 h-5 text-info-600 dark:text-info-400" />
            </div>
            <div class="flex-1">
              <p class="font-medium text-surface-900 dark:text-surface-100">E'lonlarni boshqarish</p>
              <p class="text-xs text-surface-500 dark:text-surface-400">Barcha vakansiyalaringiz</p>
            </div>
          </router-link>

          <router-link
            to="/dashboard/candidates"
            class="flex items-center gap-3 p-3 hover:bg-surface-50 dark:hover:bg-surface-800 rounded-lg transition-colors"
          >
            <div class="w-10 h-10 rounded-full bg-success-100 dark:bg-success-900/50 flex items-center justify-center">
              <UsersIcon class="w-5 h-5 text-success-600 dark:text-success-400" />
            </div>
            <div class="flex-1">
              <p class="font-medium text-surface-900 dark:text-surface-100">Nomzodlar</p>
              <p class="text-xs text-surface-500 dark:text-surface-400">Tavsiya etilgan nomzodlar</p>
            </div>
          </router-link>

          <router-link
            to="/dashboard/settings/company"
            class="flex items-center gap-3 p-3 hover:bg-surface-50 dark:hover:bg-surface-800 rounded-lg transition-colors"
          >
            <div class="w-10 h-10 rounded-full bg-warning-100 dark:bg-warning-900/50 flex items-center justify-center">
              <BuildingOfficeIcon class="w-5 h-5 text-warning-600 dark:text-warning-400" />
            </div>
            <div class="flex-1">
              <p class="font-medium text-surface-900 dark:text-surface-100">Kompaniya sozlamalari</p>
              <p class="text-xs text-surface-500 dark:text-surface-400">Profil va kompaniya ma'lumotlari</p>
            </div>
          </router-link>
        </div>
      </AppCard>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '../../stores/auth';
import axios from 'axios';
import AppCard from '../../components/ui/AppCard.vue';
import AppButton from '../../components/ui/AppButton.vue';
import {
  PlusIcon,
  BriefcaseIcon,
  DocumentTextIcon,
  CheckCircleIcon,
  EyeIcon,
  UsersIcon,
  BuildingOfficeIcon,
} from '@heroicons/vue/24/outline';

const authStore = useAuthStore();
const user = computed(() => authStore.user);

const statsLoading = ref(true);
const applicationsLoading = ref(true);

const stats = ref({
  total_vacancies: 0,
  active_vacancies: 0,
  total_applications: 0,
  new_applications: 0,
  total_views: 0,
  hired_count: 0,
});

const applications = ref([]);

async function fetchStats() {
  try {
    const response = await axios.get('/api/recruiter/dashboard');
    stats.value = response.data.stats;
  } catch (e) {
    console.error('Dashboard stats error:', e);
  } finally {
    statsLoading.value = false;
  }
}

async function fetchApplications() {
  try {
    const response = await axios.get('/api/recruiter/dashboard/recent-apps');
    applications.value = response.data.applications || [];
  } catch (e) {
    console.error('Recent applications error:', e);
  } finally {
    applicationsLoading.value = false;
  }
}

function getInitials(name) {
  if (!name) return '?';
  return name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
}

function stageLabel(stage) {
  const labels = {
    new: 'Yangi',
    screening: 'Ko\'rib chiqilmoqda',
    interview: 'Suhbat',
    test: 'Test',
    offer: 'Taklif',
    hired: 'Ishga olindi',
    rejected: 'Rad etildi',
  };
  return labels[stage] || stage;
}

function stageClass(stage) {
  const classes = {
    new: 'bg-info-100 dark:bg-info-900 text-info-700 dark:text-info-300',
    screening: 'bg-warning-100 dark:bg-warning-900 text-warning-700 dark:text-warning-300',
    interview: 'bg-brand-100 dark:bg-brand-900 text-brand-700 dark:text-brand-300',
    test: 'bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300',
    offer: 'bg-success-100 dark:bg-success-900 text-success-700 dark:text-success-300',
    hired: 'bg-success-100 dark:bg-success-900 text-success-700 dark:text-success-300',
    rejected: 'bg-danger-100 dark:bg-danger-900 text-danger-700 dark:text-danger-300',
  };
  return classes[stage] || 'bg-surface-100 dark:bg-surface-800 text-surface-700 dark:text-surface-300';
}

function timeAgo(dateStr) {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  const now = new Date();
  const diffMs = now - date;
  const diffMin = Math.floor(diffMs / 60000);

  if (diffMin < 1) return 'hozirgina';
  if (diffMin < 60) return `${diffMin} min`;
  const diffHr = Math.floor(diffMin / 60);
  if (diffHr < 24) return `${diffHr} soat`;
  const diffDay = Math.floor(diffHr / 24);
  if (diffDay < 30) return `${diffDay} kun`;
  return `${Math.floor(diffDay / 30)} oy`;
}

onMounted(() => {
  fetchStats();
  fetchApplications();
});
</script>
