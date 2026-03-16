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
  const date = new Date().toLocaleDateString('uz-UZ', {
    day: '2-digit', month: '2-digit', year: 'numeric',
  });

  const headerCells = headers.map(h => `<th>${h}</th>`).join('');
  const bodyRows = rows.map((row, idx) => {
    const isLast = idx === rows.length - 1;
    const cls = isLast ? ' class="total-row"' : '';
    const cells = row.map((cell, ci) => {
      const align = ci >= 2 ? ' style="text-align:center"' : '';
      return `<td${align}>${cell}</td>`;
    }).join('');
    return `<tr${cls}>${cells}</tr>`;
  }).join('');

  return `<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>${title} — KadrGo</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Segoe UI', Tahoma, sans-serif; padding: 30px; color: #1a1a1a; }
    .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; border-bottom: 2px solid #2563eb; padding-bottom: 12px; }
    .header h1 { font-size: 20px; color: #1e40af; }
    .header .date { font-size: 12px; color: #6b7280; }
    .logo { font-size: 22px; font-weight: 800; color: #2563eb; }
    .logo span { color: #f97316; }
    table { width: 100%; border-collapse: collapse; font-size: 13px; }
    th { background: #f1f5f9; padding: 10px 12px; text-align: left; font-weight: 600; border-bottom: 2px solid #e2e8f0; }
    td { padding: 8px 12px; border-bottom: 1px solid #e2e8f0; }
    tr:hover { background: #f8fafc; }
    .total-row { background: #eff6ff !important; font-weight: 700; border-top: 2px solid #2563eb; }
    .total-row td { padding: 10px 12px; }
    .footer { margin-top: 20px; text-align: center; font-size: 11px; color: #9ca3af; }
    @media print { body { padding: 15px; } }
  </style>
</head>
<body>
  <div class="header">
    <div>
      <div class="logo">KADR<span>GO</span></div>
      <h1>${title}</h1>
    </div>
    <div class="date">${date}</div>
  </div>
  <table>
    <thead><tr>${headerCells}</tr></thead>
    <tbody>${bodyRows}</tbody>
  </table>
  <div class="footer">KadrGo Admin Panel — ${date}</div>
</body>
</html>`;
}

onMounted(fetchStats);
</script>
