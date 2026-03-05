<template>
  <div class="space-y-6">
    <!-- Header -->
    <div>
      <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">Statistika</h1>
      <p class="mt-1 text-surface-600 dark:text-surface-400">Vakansiya va arizalar bo'yicha analitika</p>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex items-center justify-center py-12">
      <div class="w-8 h-8 border-2 border-brand-500 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <template v-else>
      <!-- Overview Stats -->
      <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <AppCard>
          <p class="text-sm text-surface-500 dark:text-surface-400">Ko'rishlar</p>
          <p class="text-2xl font-bold text-surface-900 dark:text-surface-100 mt-1">
            {{ formatNumber(overview.total_views) }}
          </p>
        </AppCard>
        <AppCard>
          <p class="text-sm text-surface-500 dark:text-surface-400">Arizalar</p>
          <p class="text-2xl font-bold text-surface-900 dark:text-surface-100 mt-1">
            {{ formatNumber(overview.total_applications) }}
          </p>
        </AppCard>
        <AppCard>
          <p class="text-sm text-surface-500 dark:text-surface-400">Ishga olindi</p>
          <p class="text-2xl font-bold text-success-600 dark:text-success-400 mt-1">
            {{ overview.hired_count }}
          </p>
        </AppCard>
        <AppCard>
          <p class="text-sm text-surface-500 dark:text-surface-400">O'rt. ball</p>
          <p class="text-2xl font-bold text-surface-900 dark:text-surface-100 mt-1">
            {{ overview.avg_questionnaire_score }}
          </p>
        </AppCard>
        <AppCard>
          <p class="text-sm text-surface-500 dark:text-surface-400">Konversiya</p>
          <p class="text-2xl font-bold text-brand-600 dark:text-brand-400 mt-1">
            {{ overview.conversion_rate }}%
          </p>
        </AppCard>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Funnel -->
        <AppCard title="Ariza bosqichlari">
          <div v-if="funnelTotal === 0" class="text-center py-8">
            <ChartBarIcon class="w-12 h-12 mx-auto text-surface-300 dark:text-surface-600" />
            <p class="mt-3 text-sm text-surface-500 dark:text-surface-400">Hali arizalar yo'q</p>
          </div>
          <div v-else class="space-y-3">
            <div v-for="stage in funnelStages" :key="stage.key">
              <div class="flex items-center justify-between text-sm mb-1">
                <span class="text-surface-700 dark:text-surface-300">{{ stage.label }}</span>
                <span class="font-medium text-surface-900 dark:text-surface-100">{{ funnel[stage.key] || 0 }}</span>
              </div>
              <div class="w-full h-2 bg-surface-100 dark:bg-surface-800 rounded-full overflow-hidden">
                <div
                  class="h-full rounded-full transition-all duration-500"
                  :class="stage.color"
                  :style="{ width: funnelPercent(stage.key) + '%' }"
                ></div>
              </div>
            </div>
          </div>
        </AppCard>

        <!-- Time to Hire -->
        <AppCard title="Ishga olish tezligi">
          <div v-if="timeToHire.total_hires === 0" class="text-center py-8">
            <ClockIcon class="w-12 h-12 mx-auto text-surface-300 dark:text-surface-600" />
            <p class="mt-3 text-sm text-surface-500 dark:text-surface-400">Hali ishga olinganlar yo'q</p>
          </div>
          <template v-else>
            <div class="text-center mb-6">
              <p class="text-4xl font-bold text-surface-900 dark:text-surface-100">
                {{ timeToHire.avg_days_to_hire }}
              </p>
              <p class="text-sm text-surface-500 dark:text-surface-400">o'rtacha kun</p>
              <p class="text-xs text-surface-400 mt-1">Jami {{ timeToHire.total_hires }} ta ishga olindi</p>
            </div>

            <div v-if="timeToHire.by_vacancy?.length" class="space-y-2 border-t border-surface-100 dark:border-surface-800 pt-4">
              <p class="text-sm font-medium text-surface-700 dark:text-surface-300 mb-2">Vakansiya bo'yicha</p>
              <div
                v-for="item in timeToHire.by_vacancy"
                :key="item.vacancy"
                class="flex items-center justify-between text-sm"
              >
                <span class="text-surface-600 dark:text-surface-400 truncate mr-2">{{ item.vacancy }}</span>
                <span class="font-medium text-surface-900 dark:text-surface-100 shrink-0">
                  {{ item.avg_days }} kun ({{ item.count }})
                </span>
              </div>
            </div>
          </template>
        </AppCard>
      </div>

      <!-- Questionnaire Stats -->
      <AppCard title="Savolnoma statistikasi">
        <div v-if="questionnaireStats.total_responses === 0" class="text-center py-8">
          <DocumentTextIcon class="w-12 h-12 mx-auto text-surface-300 dark:text-surface-600" />
          <p class="mt-3 text-sm text-surface-500 dark:text-surface-400">Hali savolnoma javoblari yo'q</p>
        </div>
        <template v-else>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-6">
            <div class="text-center">
              <p class="text-3xl font-bold text-surface-900 dark:text-surface-100">{{ questionnaireStats.total_responses }}</p>
              <p class="text-sm text-surface-500 dark:text-surface-400">Jami javoblar</p>
            </div>
            <div class="text-center">
              <p class="text-3xl font-bold text-brand-600 dark:text-brand-400">{{ questionnaireStats.avg_score }}</p>
              <p class="text-sm text-surface-500 dark:text-surface-400">O'rtacha ball</p>
            </div>
            <div class="text-center">
              <p class="text-3xl font-bold text-success-600 dark:text-success-400">{{ questionnaireStats.pass_rate }}%</p>
              <p class="text-sm text-surface-500 dark:text-surface-400">O'tish foizi</p>
            </div>
          </div>

          <!-- Score distribution -->
          <div v-if="Object.keys(questionnaireStats.score_distribution || {}).length > 0">
            <p class="text-sm font-medium text-surface-700 dark:text-surface-300 mb-3">Ball taqsimoti</p>
            <div class="flex items-end gap-1 h-32">
              <div
                v-for="(count, range) in questionnaireStats.score_distribution"
                :key="range"
                class="flex-1 flex flex-col items-center"
              >
                <div
                  class="w-full bg-brand-500 dark:bg-brand-400 rounded-t transition-all"
                  :style="{ height: scoreBarHeight(count) + '%', minHeight: count > 0 ? '4px' : '0' }"
                ></div>
                <p class="text-xs text-surface-500 dark:text-surface-400 mt-1">{{ range }}</p>
              </div>
            </div>
          </div>
        </template>
      </AppCard>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import AppCard from '../../components/ui/AppCard.vue';
import {
  ChartBarIcon,
  ClockIcon,
  DocumentTextIcon,
} from '@heroicons/vue/24/outline';

const loading = ref(true);

const overview = ref({
  total_views: 0,
  total_applications: 0,
  hired_count: 0,
  avg_questionnaire_score: 0,
  conversion_rate: 0,
});

const funnel = ref({});
const timeToHire = ref({ avg_days_to_hire: 0, total_hires: 0, by_vacancy: [] });
const questionnaireStats = ref({ total_responses: 0, avg_score: 0, pass_rate: 0, score_distribution: {} });

const funnelStages = [
  { key: 'new', label: 'Yangi', color: 'bg-info-500' },
  { key: 'reviewed', label: 'Ko\'rib chiqilgan', color: 'bg-brand-500' },
  { key: 'shortlisted', label: 'Tanlangan', color: 'bg-warning-500' },
  { key: 'interview', label: 'Suhbat', color: 'bg-purple-500' },
  { key: 'offered', label: 'Taklif', color: 'bg-success-400' },
  { key: 'hired', label: 'Ishga olindi', color: 'bg-success-600' },
  { key: 'rejected', label: 'Rad etildi', color: 'bg-danger-500' },
];

const funnelTotal = computed(() =>
  Object.values(funnel.value).reduce((sum, v) => sum + (v || 0), 0)
);

function funnelPercent(key) {
  if (funnelTotal.value === 0) return 0;
  return Math.round(((funnel.value[key] || 0) / funnelTotal.value) * 100);
}

function formatNumber(n) {
  return new Intl.NumberFormat('uz-UZ').format(n || 0);
}

function scoreBarHeight(count) {
  const maxCount = Math.max(...Object.values(questionnaireStats.value.score_distribution || { 0: 1 }));
  return maxCount > 0 ? Math.round((count / maxCount) * 100) : 0;
}

async function fetchAll() {
  loading.value = true;
  try {
    const [overviewRes, funnelRes, tthRes, qsRes] = await Promise.all([
      axios.get('/api/recruiter/analytics/overview'),
      axios.get('/api/recruiter/analytics/funnel'),
      axios.get('/api/recruiter/analytics/time-to-hire'),
      axios.get('/api/recruiter/analytics/questionnaire-stats'),
    ]);

    overview.value = overviewRes.data.overview || overview.value;
    funnel.value = funnelRes.data.funnel || {};
    timeToHire.value = tthRes.data || timeToHire.value;
    questionnaireStats.value = qsRes.data || questionnaireStats.value;
  } catch (e) {
    console.error('Analytics error:', e);
  } finally {
    loading.value = false;
  }
}

onMounted(() => fetchAll());
</script>
