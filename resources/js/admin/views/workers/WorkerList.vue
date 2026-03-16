<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">{{ $t('workers.title') }}</h1>
        <p class="text-sm text-surface-500 mt-1">{{ $t('workers.subtitle') }}</p>
      </div>
      <button
        @click="exportPdf"
        :disabled="exporting || loading"
        class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-xl transition-all"
        :class="exporting
          ? 'bg-surface-200 dark:bg-surface-700 text-surface-500 cursor-not-allowed'
          : 'bg-brand-600 text-white hover:bg-brand-700 shadow-sm hover:shadow-md'"
      >
        <ArrowDownTrayIcon class="w-4 h-4" :class="{ 'animate-bounce': exporting }" />
        {{ exporting ? $t('workers.exporting') : $t('workers.exportPdf') }}
      </button>
    </div>

    <!-- Summary cards -->
    <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div v-for="i in 4" :key="i" class="rounded-2xl bg-white dark:bg-surface-900 border border-surface-100 dark:border-surface-800 p-5">
        <div class="animate-pulse space-y-3">
          <div class="h-4 bg-surface-200 dark:bg-surface-700 rounded w-1/2"></div>
          <div class="h-8 bg-surface-200 dark:bg-surface-700 rounded w-2/3"></div>
        </div>
      </div>
    </div>
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div
        v-for="card in summaryCards"
        :key="card.label"
        class="rounded-2xl bg-white dark:bg-surface-900 border border-surface-100 dark:border-surface-800 p-5 hover:shadow-md transition-shadow"
      >
        <div class="flex items-center justify-between mb-3">
          <span class="text-sm font-medium text-surface-500">{{ card.label }}</span>
          <div class="w-10 h-10 rounded-xl flex items-center justify-center" :class="card.bgClass">
            <component :is="card.icon" class="w-5 h-5" :class="card.iconClass" />
          </div>
        </div>
        <p class="text-3xl font-bold" :class="card.valueClass">{{ card.value.toLocaleString() }}</p>
      </div>
    </div>

    <!-- Region detail — back button -->
    <div v-if="selectedRegion" class="flex items-center gap-3">
      <button
        @click="selectedRegion = null"
        class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium rounded-lg bg-surface-100 dark:bg-surface-800 text-surface-600 dark:text-surface-300 hover:bg-surface-200 dark:hover:bg-surface-700 transition-colors"
      >
        <ArrowLeftIcon class="w-4 h-4" />
        {{ $t('workers.backToRegions') }}
      </button>
      <div class="flex items-center gap-2">
        <MapPinIcon class="w-5 h-5 text-brand-500" />
        <span class="text-lg font-semibold text-surface-900 dark:text-surface-100">
          {{ selectedRegion.name_uz }}
        </span>
        <span class="text-sm text-surface-500">— {{ $t('workers.regionDetails') }}</span>
      </div>
    </div>

    <!-- Regions table -->
    <AppCard v-if="!selectedRegion" noPadding>
      <div class="p-4 border-b border-surface-100 dark:border-surface-800">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center">
              <GlobeAltIcon class="w-5 h-5 text-brand-600 dark:text-brand-400" />
            </div>
            <h2 class="text-lg font-semibold text-surface-900 dark:text-surface-100">{{ $t('workers.allRegions') }}</h2>
          </div>
          <AppSearchInput
            v-model="searchRegion"
            :placeholder="$t('workers.searchRegion')"
            class="w-56"
            size="sm"
          />
        </div>
      </div>

      <!-- Loading skeleton -->
      <div v-if="loading" class="p-6">
        <div class="animate-pulse space-y-4">
          <div v-for="i in 8" :key="i" class="flex justify-between">
            <div class="h-4 bg-surface-200 dark:bg-surface-700 rounded w-1/3"></div>
            <div class="flex gap-6">
              <div class="h-4 bg-surface-200 dark:bg-surface-700 rounded w-16"></div>
              <div class="h-4 bg-surface-200 dark:bg-surface-700 rounded w-16"></div>
              <div class="h-4 bg-surface-200 dark:bg-surface-700 rounded w-12"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Regions data -->
      <div v-else class="overflow-x-auto">
        <table class="w-full text-sm" id="regions-table">
          <thead>
            <tr class="border-b border-surface-200 dark:border-surface-800 bg-surface-50 dark:bg-surface-800/50">
              <th class="text-left py-3 px-5 font-semibold text-surface-600 dark:text-surface-400 w-8">#</th>
              <th class="text-left py-3 px-5 font-semibold text-surface-600 dark:text-surface-400">{{ $t('workers.region') }}</th>
              <th class="text-center py-3 px-5 font-semibold text-surface-600 dark:text-surface-400">
                <div class="inline-flex items-center gap-1.5">
                  <UserGroupIcon class="w-4 h-4" />
                  {{ $t('workers.workersCount') }}
                </div>
              </th>
              <th class="text-center py-3 px-5 font-semibold text-surface-600 dark:text-surface-400">
                <div class="inline-flex items-center gap-1.5">
                  <BriefcaseIcon class="w-4 h-4" />
                  {{ $t('workers.vacanciesCount') }}
                </div>
              </th>
              <th class="text-center py-3 px-5 font-semibold text-surface-600 dark:text-surface-400">{{ $t('workers.districtsCount') }}</th>
              <th class="text-right py-3 px-5 font-semibold text-surface-600 dark:text-surface-400">{{ $t('common.actions') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(region, idx) in filteredRegions"
              :key="region.key"
              class="border-b border-surface-100 dark:border-surface-800/50 hover:bg-brand-50/50 dark:hover:bg-brand-900/10 cursor-pointer transition-colors group"
              @click="openRegion(region)"
            >
              <td class="py-3.5 px-5 text-surface-400 font-mono text-xs">{{ idx + 1 }}</td>
              <td class="py-3.5 px-5">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-lg bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center group-hover:bg-brand-200 dark:group-hover:bg-brand-900/50 transition-colors">
                    <MapPinIcon class="w-4 h-4 text-brand-600 dark:text-brand-400" />
                  </div>
                  <div>
                    <p class="font-medium text-surface-900 dark:text-surface-100">{{ region.name_uz }}</p>
                    <p class="text-xs text-surface-400">{{ region.name_ru }}</p>
                  </div>
                </div>
              </td>
              <td class="py-3.5 px-5 text-center">
                <span
                  class="inline-flex items-center justify-center min-w-[2.5rem] px-2.5 py-1 rounded-lg text-sm font-bold"
                  :class="region.workers_count > 0
                    ? 'bg-brand-100 dark:bg-brand-900/30 text-brand-700 dark:text-brand-400'
                    : 'bg-surface-100 dark:bg-surface-800 text-surface-400'"
                >
                  {{ region.workers_count }}
                </span>
              </td>
              <td class="py-3.5 px-5 text-center">
                <span
                  class="inline-flex items-center justify-center min-w-[2.5rem] px-2.5 py-1 rounded-lg text-sm font-bold"
                  :class="region.vacancies_count > 0
                    ? 'bg-success-100 dark:bg-success-900/30 text-success-700 dark:text-success-400'
                    : 'bg-surface-100 dark:bg-surface-800 text-surface-400'"
                >
                  {{ region.vacancies_count }}
                </span>
              </td>
              <td class="py-3.5 px-5 text-center text-surface-500">{{ region.districts_count }}</td>
              <td class="py-3.5 px-5 text-right">
                <button class="inline-flex items-center gap-1 text-xs font-medium text-brand-600 dark:text-brand-400 opacity-0 group-hover:opacity-100 transition-opacity">
                  {{ $t('common.actions') }}
                  <ChevronRightIcon class="w-3.5 h-3.5" />
                </button>
              </td>
            </tr>
          </tbody>
          <!-- Totals footer -->
          <tfoot>
            <tr class="bg-surface-50 dark:bg-surface-800/50 border-t-2 border-surface-200 dark:border-surface-700">
              <td class="py-3.5 px-5"></td>
              <td class="py-3.5 px-5 font-bold text-surface-900 dark:text-surface-100">{{ $t('workers.total') }}</td>
              <td class="py-3.5 px-5 text-center">
                <span class="inline-flex items-center justify-center min-w-[2.5rem] px-2.5 py-1 rounded-lg text-sm font-bold bg-brand-600 text-white">
                  {{ totalRegionWorkers.toLocaleString() }}
                </span>
              </td>
              <td class="py-3.5 px-5 text-center">
                <span class="inline-flex items-center justify-center min-w-[2.5rem] px-2.5 py-1 rounded-lg text-sm font-bold bg-success-600 text-white">
                  {{ totalRegionVacancies.toLocaleString() }}
                </span>
              </td>
              <td class="py-3.5 px-5 text-center font-bold text-surface-700 dark:text-surface-300">
                {{ totalDistricts }}
              </td>
              <td class="py-3.5 px-5"></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </AppCard>

    <!-- District detail table -->
    <AppCard v-if="selectedRegion" noPadding>
      <div class="p-4 border-b border-surface-100 dark:border-surface-800">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-xl bg-info-100 dark:bg-info-900/30 flex items-center justify-center">
            <BuildingOffice2Icon class="w-5 h-5 text-info-600 dark:text-info-400" />
          </div>
          <div>
            <h2 class="text-lg font-semibold text-surface-900 dark:text-surface-100">
              {{ selectedRegion.name_uz }}
            </h2>
            <p class="text-xs text-surface-500">
              {{ selectedRegion.districts.length }} {{ $t('workers.district').toLowerCase() }}
            </p>
          </div>
        </div>
      </div>

      <div v-if="selectedRegion.districts.length === 0" class="p-12 text-center">
        <MapPinIcon class="w-12 h-12 text-surface-300 dark:text-surface-600 mx-auto mb-3" />
        <p class="text-surface-500">{{ $t('workers.noDistricts') }}</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-sm" id="districts-table">
          <thead>
            <tr class="border-b border-surface-200 dark:border-surface-800 bg-surface-50 dark:bg-surface-800/50">
              <th class="text-left py-3 px-5 font-semibold text-surface-600 dark:text-surface-400 w-8">#</th>
              <th class="text-left py-3 px-5 font-semibold text-surface-600 dark:text-surface-400">{{ $t('workers.district') }}</th>
              <th class="text-center py-3 px-5 font-semibold text-surface-600 dark:text-surface-400">{{ $t('workers.type') }}</th>
              <th class="text-center py-3 px-5 font-semibold text-surface-600 dark:text-surface-400">
                <div class="inline-flex items-center gap-1.5">
                  <UserGroupIcon class="w-4 h-4" />
                  {{ $t('workers.workersCount') }}
                </div>
              </th>
              <th class="text-center py-3 px-5 font-semibold text-surface-600 dark:text-surface-400">
                <div class="inline-flex items-center gap-1.5">
                  <BriefcaseIcon class="w-4 h-4" />
                  {{ $t('workers.vacanciesCount') }}
                </div>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(district, idx) in sortedDistricts"
              :key="district.id"
              class="border-b border-surface-100 dark:border-surface-800/50 hover:bg-surface-50 dark:hover:bg-surface-800/30 transition-colors"
            >
              <td class="py-3.5 px-5 text-surface-400 font-mono text-xs">{{ idx + 1 }}</td>
              <td class="py-3.5 px-5">
                <div class="flex items-center gap-3">
                  <div
                    class="w-7 h-7 rounded-lg flex items-center justify-center"
                    :class="district.type === 'shahar'
                      ? 'bg-info-100 dark:bg-info-900/30'
                      : 'bg-warning-100 dark:bg-warning-900/30'"
                  >
                    <component
                      :is="district.type === 'shahar' ? BuildingOffice2Icon : HomeModernIcon"
                      class="w-3.5 h-3.5"
                      :class="district.type === 'shahar'
                        ? 'text-info-600 dark:text-info-400'
                        : 'text-warning-600 dark:text-warning-400'"
                    />
                  </div>
                  <div>
                    <p class="font-medium text-surface-900 dark:text-surface-100">{{ district.name_uz }}</p>
                    <p class="text-xs text-surface-400">{{ district.name_ru }}</p>
                  </div>
                </div>
              </td>
              <td class="py-3.5 px-5 text-center">
                <span
                  class="inline-flex px-2 py-0.5 rounded-md text-xs font-semibold"
                  :class="district.type === 'shahar'
                    ? 'bg-info-100 dark:bg-info-900/30 text-info-700 dark:text-info-400'
                    : 'bg-warning-100 dark:bg-warning-900/30 text-warning-700 dark:text-warning-400'"
                >
                  {{ $t(`workers.${district.type}`) }}
                </span>
              </td>
              <td class="py-3.5 px-5 text-center">
                <span
                  class="inline-flex items-center justify-center min-w-[2rem] px-2 py-0.5 rounded-md text-sm font-bold"
                  :class="district.workers_count > 0
                    ? 'bg-brand-100 dark:bg-brand-900/30 text-brand-700 dark:text-brand-400'
                    : 'text-surface-400'"
                >
                  {{ district.workers_count }}
                </span>
              </td>
              <td class="py-3.5 px-5 text-center">
                <span
                  class="inline-flex items-center justify-center min-w-[2rem] px-2 py-0.5 rounded-md text-sm font-bold"
                  :class="district.vacancies_count > 0
                    ? 'bg-success-100 dark:bg-success-900/30 text-success-700 dark:text-success-400'
                    : 'text-surface-400'"
                >
                  {{ district.vacancies_count }}
                </span>
              </td>
            </tr>
          </tbody>
          <!-- Totals footer -->
          <tfoot>
            <tr class="bg-surface-50 dark:bg-surface-800/50 border-t-2 border-surface-200 dark:border-surface-700">
              <td class="py-3.5 px-5"></td>
              <td class="py-3.5 px-5 font-bold text-surface-900 dark:text-surface-100">{{ $t('workers.total') }}</td>
              <td class="py-3.5 px-5"></td>
              <td class="py-3.5 px-5 text-center">
                <span class="inline-flex items-center justify-center min-w-[2rem] px-2.5 py-1 rounded-lg text-sm font-bold bg-brand-600 text-white">
                  {{ selectedRegion.workers_count }}
                </span>
              </td>
              <td class="py-3.5 px-5 text-center">
                <span class="inline-flex items-center justify-center min-w-[2rem] px-2.5 py-1 rounded-lg text-sm font-bold bg-success-600 text-white">
                  {{ selectedRegion.vacancies_count }}
                </span>
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
    </AppCard>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import { toast } from 'vue-sonner';
import { useI18n } from 'vue-i18n';
import AppCard from '@panel/components/ui/AppCard.vue';
import AppSearchInput from '@panel/components/ui/AppSearchInput.vue';
import {
  ArrowDownTrayIcon, ArrowLeftIcon, MapPinIcon, GlobeAltIcon,
  UserGroupIcon, BriefcaseIcon, ChevronRightIcon, BuildingOffice2Icon,
  HomeModernIcon, UsersIcon, ClipboardDocumentListIcon,
} from '@heroicons/vue/24/outline';

const { t } = useI18n();

const loading = ref(true);
const exporting = ref(false);
const summary = ref({});
const regions = ref([]);
const selectedRegion = ref(null);
const searchRegion = ref('');

const summaryCards = computed(() => [
  {
    label: t('workers.totalWorkers'),
    value: summary.value.total_workers || 0,
    icon: UserGroupIcon,
    bgClass: 'bg-brand-100 dark:bg-brand-900/30',
    iconClass: 'text-brand-600 dark:text-brand-400',
    valueClass: 'text-brand-700 dark:text-brand-400',
  },
  {
    label: t('workers.activeWorkers'),
    value: summary.value.active_workers || 0,
    icon: UsersIcon,
    bgClass: 'bg-success-100 dark:bg-success-900/30',
    iconClass: 'text-success-600 dark:text-success-400',
    valueClass: 'text-success-700 dark:text-success-400',
  },
  {
    label: t('workers.activeVacancies'),
    value: summary.value.total_active_vacancies || 0,
    icon: BriefcaseIcon,
    bgClass: 'bg-info-100 dark:bg-info-900/30',
    iconClass: 'text-info-600 dark:text-info-400',
    valueClass: 'text-info-700 dark:text-info-400',
  },
  {
    label: t('workers.totalApplications'),
    value: summary.value.total_applications || 0,
    icon: ClipboardDocumentListIcon,
    bgClass: 'bg-warning-100 dark:bg-warning-900/30',
    iconClass: 'text-warning-600 dark:text-warning-400',
    valueClass: 'text-warning-700 dark:text-warning-400',
  },
]);

const filteredRegions = computed(() => {
  if (!searchRegion.value) return regions.value;
  const q = searchRegion.value.toLowerCase();
  return regions.value.filter(r =>
    r.name_uz.toLowerCase().includes(q) || r.name_ru.toLowerCase().includes(q)
  );
});

const totalRegionWorkers = computed(() =>
  regions.value.reduce((sum, r) => sum + r.workers_count, 0)
);

const totalRegionVacancies = computed(() =>
  regions.value.reduce((sum, r) => sum + r.vacancies_count, 0)
);

const totalDistricts = computed(() =>
  regions.value.reduce((sum, r) => sum + r.districts_count, 0)
);

const sortedDistricts = computed(() => {
  if (!selectedRegion.value) return [];
  return [...selectedRegion.value.districts].sort((a, b) => b.workers_count - a.workers_count || b.vacancies_count - a.vacancies_count);
});

function openRegion(region) {
  selectedRegion.value = region;
}

async function fetchStats() {
  loading.value = true;
  try {
    const res = await axios.get('/api/admin/workers/regional-stats');
    summary.value = res.data.summary || {};
    regions.value = res.data.regions || [];
  } catch (err) {
    toast.error('Statistikani yuklashda xatolik');
  } finally {
    loading.value = false;
  }
}

function exportPdf() {
  exporting.value = true;

  try {
    const isDistrict = !!selectedRegion.value;
    const title = isDistrict
      ? `${selectedRegion.value.name_uz} — ${t('workers.regionDetails')}`
      : t('workers.allRegions');

    // Build table data
    const headers = isDistrict
      ? ['#', t('workers.district'), t('workers.type'), t('workers.workersCount'), t('workers.vacanciesCount')]
      : ['#', t('workers.region'), t('workers.workersCount'), t('workers.vacanciesCount'), t('workers.districtsCount')];

    const rows = isDistrict
      ? sortedDistricts.value.map((d, i) => [
          i + 1,
          d.name_uz,
          t(`workers.${d.type}`),
          d.workers_count,
          d.vacancies_count,
        ])
      : filteredRegions.value.map((r, i) => [
          i + 1,
          r.name_uz,
          r.workers_count,
          r.vacancies_count,
          r.districts_count,
        ]);

    // Add totals row
    if (isDistrict) {
      rows.push(['', t('workers.total'), '', selectedRegion.value.workers_count, selectedRegion.value.vacancies_count]);
    } else {
      rows.push(['', t('workers.total'), totalRegionWorkers.value, totalRegionVacancies.value, totalDistricts.value]);
    }

    // Generate PDF using HTML + print
    const printContent = buildPrintHtml(title, headers, rows);
    const printWindow = window.open('', '_blank');
    printWindow.document.write(printContent);
    printWindow.document.close();
    printWindow.focus();
    setTimeout(() => {
      printWindow.print();
      printWindow.close();
    }, 500);

    toast.success('PDF tayyorlandi');
  } catch (err) {
    toast.error('PDF yaratishda xatolik');
  } finally {
    exporting.value = false;
  }
}

function buildPrintHtml(title, headers, rows) {
  const now = new Date();
  const date = now.toLocaleDateString('uz-UZ', {
    day: '2-digit', month: '2-digit', year: 'numeric',
  });
  const time = now.toLocaleTimeString('uz-UZ', { hour: '2-digit', minute: '2-digit' });

  const logoSvg = `<svg xmlns="http://www.w3.org/2000/svg" width="160" height="40" viewBox="0 0 200 48" fill="none">
    <rect x="2" y="2" width="44" height="44" rx="12" fill="#0D9488"/>
    <path d="M15 14L15 34" stroke="white" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M15 24L27 14" stroke="white" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M15 24L27 34" stroke="white" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M30 17L35 17L35 31L30 31" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" opacity="0.7"/>
    <text x="58" y="22" font-family="system-ui, -apple-system, 'Segoe UI', sans-serif" font-size="16" font-weight="800" fill="#0D9488" letter-spacing="0.5">KADR</text>
    <text x="58" y="39" font-family="system-ui, -apple-system, 'Segoe UI', sans-serif" font-size="16" font-weight="900" fill="#F59E0B" letter-spacing="1">GO</text>
    <circle cx="84" cy="35" r="3" fill="#F59E0B"/>
  </svg>`;

  const s = summary.value;
  const isDistrict = !!selectedRegion.value;

  const summaryHtml = `
    <div class="summary-grid">
      <div class="summary-card">
        <div class="summary-icon" style="background:#f0fdfa;color:#0d9488;">
          <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
        </div>
        <div>
          <div class="summary-label">${t('workers.totalWorkers')}</div>
          <div class="summary-value" style="color:#0d9488;">${(s.total_workers || 0).toLocaleString()}</div>
        </div>
      </div>
      <div class="summary-card">
        <div class="summary-icon" style="background:#f0fdf4;color:#16a34a;">
          <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
        </div>
        <div>
          <div class="summary-label">${t('workers.activeWorkers')}</div>
          <div class="summary-value" style="color:#16a34a;">${(s.active_workers || 0).toLocaleString()}</div>
        </div>
      </div>
      <div class="summary-card">
        <div class="summary-icon" style="background:#eff6ff;color:#2563eb;">
          <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0M12 12.75h.008v.008H12v-.008z"/></svg>
        </div>
        <div>
          <div class="summary-label">${t('workers.activeVacancies')}</div>
          <div class="summary-value" style="color:#2563eb;">${(s.total_active_vacancies || 0).toLocaleString()}</div>
        </div>
      </div>
      <div class="summary-card">
        <div class="summary-icon" style="background:#fffbeb;color:#d97706;">
          <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15a2.25 2.25 0 012.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/></svg>
        </div>
        <div>
          <div class="summary-label">${t('workers.totalApplications')}</div>
          <div class="summary-value" style="color:#d97706;">${(s.total_applications || 0).toLocaleString()}</div>
        </div>
      </div>
    </div>`;

  const headerCells = headers.map((h, i) =>
    `<th${i >= 2 ? ' class="center"' : ''}>${h}</th>`
  ).join('');

  const bodyRows = rows.map((row, idx) => {
    const isLast = idx === rows.length - 1;
    const cls = isLast ? ' class="total-row"' : (idx % 2 === 0 ? ' class="even"' : '');
    const cells = row.map((cell, ci) => {
      const center = ci >= 2 ? ' class="center"' : '';
      if (isLast && ci === 0) return `<td></td>`;
      if (isLast) return `<td${center}><strong>${cell}</strong></td>`;
      if (ci === 0) return `<td class="row-num">${cell}</td>`;
      return `<td${center}>${cell}</td>`;
    }).join('');
    return `<tr${cls}>${cells}</tr>`;
  }).join('');

  const regionNote = isDistrict
    ? `<div class="region-badge">${selectedRegion.value.name_uz} <span class="region-ru">(${selectedRegion.value.name_ru})</span></div>`
    : '';

  return `<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>${title} — KadrGo</title>
  <style>
    @page { margin: 12mm 10mm; size: A4; }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
      color: #1e293b;
      font-size: 13px;
      line-height: 1.5;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }

    /* Header */
    .header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      padding-bottom: 16px;
      margin-bottom: 20px;
      border-bottom: 3px solid #0d9488;
    }
    .header-left { display: flex; flex-direction: column; gap: 8px; }
    .header-title {
      font-size: 18px;
      font-weight: 700;
      color: #0f172a;
      margin-top: 4px;
    }
    .header-right {
      text-align: right;
      font-size: 11px;
      color: #64748b;
      line-height: 1.6;
    }
    .header-right strong { color: #334155; }

    /* Region badge */
    .region-badge {
      display: inline-block;
      background: #f0fdfa;
      border: 1px solid #99f6e4;
      color: #0d9488;
      font-weight: 600;
      font-size: 13px;
      padding: 6px 14px;
      border-radius: 8px;
      margin-bottom: 16px;
    }
    .region-ru { font-weight: 400; color: #64748b; font-size: 12px; }

    /* Summary cards */
    .summary-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 12px;
      margin-bottom: 20px;
    }
    .summary-card {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 12px;
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 10px;
    }
    .summary-icon {
      width: 36px;
      height: 36px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }
    .summary-label {
      font-size: 10px;
      font-weight: 500;
      color: #94a3b8;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .summary-value {
      font-size: 20px;
      font-weight: 800;
      line-height: 1.2;
    }

    /* Table */
    table {
      width: 100%;
      border-collapse: collapse;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      overflow: hidden;
    }
    th {
      background: #0d9488;
      color: white;
      padding: 10px 14px;
      text-align: left;
      font-weight: 600;
      font-size: 12px;
      text-transform: uppercase;
      letter-spacing: 0.3px;
    }
    th.center, td.center { text-align: center; }
    td {
      padding: 9px 14px;
      border-bottom: 1px solid #f1f5f9;
      font-size: 13px;
    }
    .row-num {
      color: #94a3b8;
      font-size: 11px;
      font-family: 'Courier New', monospace;
    }
    tr.even { background: #f8fafc; }
    .total-row {
      background: #f0fdfa !important;
      border-top: 2px solid #0d9488;
    }
    .total-row td {
      padding: 11px 14px;
      font-weight: 700;
      color: #0d9488;
      font-size: 13px;
    }

    /* Footer */
    .footer {
      margin-top: 24px;
      padding-top: 12px;
      border-top: 1px solid #e2e8f0;
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 10px;
      color: #94a3b8;
    }
    .footer-left { display: flex; align-items: center; gap: 6px; }
    .footer-dot { width: 4px; height: 4px; background: #0d9488; border-radius: 50%; }

    @media print {
      .summary-card { break-inside: avoid; }
      table { page-break-inside: auto; }
      tr { page-break-inside: avoid; }
    }
  </style>
</head>
<body>
  <div class="header">
    <div class="header-left">
      ${logoSvg}
      <div class="header-title">${title}</div>
    </div>
    <div class="header-right">
      <div><strong>${date}</strong></div>
      <div>${time}</div>
      <div>kadrgo.uz</div>
    </div>
  </div>
  ${summaryHtml}
  ${regionNote}
  <table>
    <thead><tr>${headerCells}</tr></thead>
    <tbody>${bodyRows}</tbody>
  </table>
  <div class="footer">
    <div class="footer-left">
      <div class="footer-dot"></div>
      KadrGo — Mehnat bozori platformasi
    </div>
    <div>${t('workers.regions')}: ${s.total_regions || regions.value.length} | ${date}</div>
  </div>
</body>
</html>`;
}

onMounted(fetchStats);
</script>
