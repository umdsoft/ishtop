<template>
  <div>
    <!-- Limit Warning Banner -->
    <div
      v-if="subscription && !subscription.can_create_vacancy"
      class="mb-6 p-4 rounded-xl border border-amber-300 dark:border-amber-500/30 bg-amber-50 dark:bg-amber-950/40"
    >
      <div class="flex items-start gap-3">
        <div class="shrink-0 mt-0.5 w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-500/20 flex items-center justify-center">
          <ExclamationTriangleIcon class="h-5 w-5 text-amber-600 dark:text-amber-400" />
        </div>
        <div class="flex-1">
          <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">
            Vakansiya limiti tugadi
          </p>
          <p class="text-sm text-amber-700 dark:text-amber-200/70 mt-1">
            {{ getPlanLabel(subscription.plan) }} rejada maksimum {{ subscription.limits?.max_vacancies }} ta faol vakansiya yaratish mumkin.
            Ko'proq vakansiya yaratish uchun obunangizni yangilang.
          </p>
          <router-link
            to="/dashboard/settings/billing"
            class="inline-flex items-center gap-1.5 mt-3 px-3 py-1.5 text-sm font-medium text-white bg-brand-600 hover:bg-brand-500 rounded-lg transition-colors"
          >
            <ArrowUpCircleIcon class="h-4 w-4" />
            Obunani yangilash
          </router-link>
        </div>
      </div>
    </div>

    <!-- Header -->
    <div class="mb-6">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">Vakansiyalar</h1>
          <p class="text-surface-600 dark:text-surface-400 mt-1">
            Barcha vakansiyalaringizni boshqaring
            <span v-if="subscription && subscription.remaining_vacancies !== null" class="text-surface-500">
              &middot; {{ subscription.remaining_vacancies }} ta vakansiya qoldi
            </span>
          </p>
        </div>
        <div class="flex items-center gap-3">
          <router-link to="/dashboard/vacancies/create">
            <AppButton
              variant="primary"
              :disabled="subscription && !subscription.can_create_vacancy"
            >
              <template #icon-left>
                <PlusIcon class="h-5 w-5" />
              </template>
              Yangi vakansiya
            </AppButton>
          </router-link>
        </div>
      </div>

      <!-- Filters -->
      <AppCard no-padding>
        <div class="p-4">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <AppSearchInput
              v-model="filters.search"
              placeholder="Vakansiya qidirish..."
              @search="fetchVacancies"
            />

            <AppSelect
              v-model="filters.status"
              :options="statusOptions"
              label-key="label"
              value-key="value"
              placeholder="Status"
              @update:model-value="fetchVacancies"
            />

            <AppSelect
              v-model="filters.work_type"
              :options="workTypeOptions"
              label-key="label"
              value-key="value"
              placeholder="Bandlik turi"
              @update:model-value="fetchVacancies"
            />

            <AppSelect
              v-model="filters.category"
              :options="categoryOptions"
              label-key="label"
              value-key="value"
              placeholder="Kategoriya"
              searchable
              @update:model-value="fetchVacancies"
            />
          </div>
        </div>
      </AppCard>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
      <AppCard>
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-surface-600 dark:text-surface-400">Faol</p>
            <p class="text-2xl font-bold text-surface-900 dark:text-surface-100 mt-1">{{ stats.active }}</p>
          </div>
          <div class="w-12 h-12 rounded-lg bg-success-100 dark:bg-success-900/20 flex items-center justify-center">
            <CheckCircleIcon class="h-6 w-6 text-success-600 dark:text-success-400" />
          </div>
        </div>
      </AppCard>

      <AppCard>
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-surface-600 dark:text-surface-400">Kutilmoqda</p>
            <p class="text-2xl font-bold text-surface-900 dark:text-surface-100 mt-1">{{ stats.pending }}</p>
          </div>
          <div class="w-12 h-12 rounded-lg bg-warning-100 dark:bg-warning-900/20 flex items-center justify-center">
            <ClockIcon class="h-6 w-6 text-warning-600 dark:text-warning-400" />
          </div>
        </div>
      </AppCard>

      <AppCard>
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-surface-600 dark:text-surface-400">Yopilgan</p>
            <p class="text-2xl font-bold text-surface-900 dark:text-surface-100 mt-1">{{ stats.closed }}</p>
          </div>
          <div class="w-12 h-12 rounded-lg bg-surface-100 dark:bg-surface-800 flex items-center justify-center">
            <ArchiveBoxIcon class="h-6 w-6 text-surface-600 dark:text-surface-400" />
          </div>
        </div>
      </AppCard>

      <AppCard>
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-surface-600 dark:text-surface-400">Jami arizalar</p>
            <p class="text-2xl font-bold text-surface-900 dark:text-surface-100 mt-1">{{ stats.total_applications }}</p>
          </div>
          <div class="w-12 h-12 rounded-lg bg-brand-100 dark:bg-brand-900/20 flex items-center justify-center">
            <UsersIcon class="h-6 w-6 text-brand-600 dark:text-brand-400" />
          </div>
        </div>
      </AppCard>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center py-20">
      <AppLoadingSpinner size="lg" text="Vakansiyalar yuklanmoqda..." />
    </div>

    <!-- Vacancies Table -->
    <AppCard v-else no-padding>
      <AppTable
        :columns="columns"
        :data="vacancies"
        :hoverable="true"
        :clickable="true"
        @row-click="handleRowClick"
      >
        <template #cell-title="{ row }">
          <div class="flex items-center gap-2">
            <div>
              <p class="font-medium text-surface-900 dark:text-surface-100">{{ row.title_uz || row.title_ru }}</p>
              <p class="text-sm text-surface-500 dark:text-surface-400">{{ row.employer?.company_name || '' }}</p>
            </div>
            <span
              v-if="row.language"
              class="shrink-0 px-1.5 py-0.5 text-[10px] font-bold uppercase rounded bg-surface-100 dark:bg-surface-700 text-surface-500 dark:text-surface-400"
            >
              {{ row.language }}
            </span>
          </div>
        </template>

        <template #cell-status="{ row }">
          <div class="flex items-center gap-2">
            <AppBadge :variant="getStatusVariant(row.status)">
              {{ getStatusLabel(row.status) }}
            </AppBadge>
            <button
              v-if="['active', 'paused', 'draft', 'pending'].includes(row.status)"
              class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
              :class="row.status === 'active' ? 'bg-success-500' : 'bg-surface-600'"
              :title="row.status === 'active' ? 'To\'xtatish' : 'Faollashtirish'"
              @click.stop="toggleVacancyStatus(row)"
            >
              <span
                class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                :class="row.status === 'active' ? 'translate-x-4' : 'translate-x-0'"
              />
            </button>
          </div>
        </template>

        <template #cell-work_type="{ row }">
          <span class="text-sm text-surface-700 dark:text-surface-300">
            {{ getWorkTypeLabel(row.work_type) }}
          </span>
        </template>

        <template #cell-salary="{ row }">
          <span class="text-sm text-surface-700 dark:text-surface-300">
            {{ formatSalary(row.salary_min, row.salary_max, row.salary_type) }}
          </span>
        </template>

        <template #cell-applications_count="{ row }">
          <div class="flex items-center gap-2">
            <span class="text-sm font-medium text-surface-900 dark:text-surface-100">
              {{ row.applications_count }}
            </span>
            <AppBadge v-if="row.new_applications_count > 0" variant="danger" size="sm">
              +{{ row.new_applications_count }}
            </AppBadge>
          </div>
        </template>

        <template #cell-recommended_count="{ row }">
          <div
            class="flex items-center gap-1.5 cursor-pointer hover:opacity-80 transition-opacity"
            @click.stop="viewRecommended(row.id)"
          >
            <SparklesIcon class="h-4 w-4 text-brand-500 dark:text-brand-400" />
            <span class="text-sm font-medium text-brand-600 dark:text-brand-400">
              {{ row.recommended_count ?? 0 }}
            </span>
          </div>
        </template>

        <template #cell-created_at="{ row }">
          <span class="text-sm text-surface-600 dark:text-surface-400">
            {{ formatDate(row.created_at) }}
          </span>
        </template>

        <template #cell-actions="{ row }">
          <div class="flex items-center gap-1" @click.stop>
            <AppTooltip content="Ko'rish" position="top">
              <button
                class="p-1.5 rounded-md hover:bg-surface-100 dark:hover:bg-surface-800 text-surface-600 dark:text-surface-400 transition-colors"
                @click="viewVacancy(row.id)"
              >
                <EyeIcon class="h-5 w-5" />
              </button>
            </AppTooltip>

            <AppTooltip content="Mos nomzodlar" position="top">
              <button
                class="p-1.5 rounded-md hover:bg-brand-50 dark:hover:bg-brand-900/20 text-brand-500 dark:text-brand-400 transition-colors"
                @click="viewRecommended(row.id)"
              >
                <SparklesIcon class="h-5 w-5" />
              </button>
            </AppTooltip>

            <AppTooltip content="Tahrirlash" position="top">
              <button
                class="p-1.5 rounded-md hover:bg-surface-100 dark:hover:bg-surface-800 text-surface-600 dark:text-surface-400 transition-colors"
                @click="editVacancy(row.id)"
              >
                <PencilIcon class="h-5 w-5" />
              </button>
            </AppTooltip>

            <AppTooltip content="O'chirish" position="top">
              <button
                class="p-1.5 rounded-md hover:bg-surface-100 dark:hover:bg-surface-800 text-danger-600 dark:text-danger-400 transition-colors"
                @click="deleteVacancy(row.id)"
              >
                <TrashIcon class="h-5 w-5" />
              </button>
            </AppTooltip>
          </div>
        </template>

        <template #empty>
          <AppEmptyState
            title="Vakansiyalar topilmadi"
            description="Yangi vakansiya yaratib boshlang"
            action="Vakansiya yaratish"
            @action="$router.push('/dashboard/vacancies/create')"
          />
        </template>
      </AppTable>

      <div v-if="vacancies.length > 0" class="px-6 py-4 border-t border-surface-200 dark:border-surface-700">
        <AppPagination
          v-model:current-page="currentPage"
          :total="totalVacancies"
          :per-page="perPage"
          @update:current-page="fetchVacancies"
        />
      </div>
    </AppCard>

    <!-- Delete Confirmation Dialog -->
    <AppConfirmDialog
      :open="showDeleteDialog"
      type="danger"
      title="Vakansiyani o'chirish"
      message="Ushbu vakansiyani o'chirishga ishonchingiz komilmi? Bu amalni qaytarib bo'lmaydi."
      confirm-text="O'chirish"
      cancel-text="Bekor qilish"
      :loading="deleteLoading"
      @confirm="confirmDelete"
      @cancel="showDeleteDialog = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { toast } from 'vue-sonner';
import axios from 'axios';
import { formatSalary, formatDate, getStatusVariant, getStatusLabel, getWorkTypeLabel } from '@/shared/formatters';
import {
  PlusIcon,
  EyeIcon,
  PencilIcon,
  TrashIcon,
  CheckCircleIcon,
  ClockIcon,
  ArchiveBoxIcon,
  UsersIcon,
  ExclamationTriangleIcon,
  ArrowUpCircleIcon,
  SparklesIcon,
} from '@heroicons/vue/24/outline';
import AppCard from '../../components/ui/AppCard.vue';
import AppButton from '../../components/ui/AppButton.vue';
import AppSearchInput from '../../components/ui/AppSearchInput.vue';
import AppSelect from '../../components/ui/AppSelect.vue';
import AppTable from '../../components/ui/AppTable.vue';
import AppBadge from '../../components/ui/AppBadge.vue';
import AppTooltip from '../../components/ui/AppTooltip.vue';
import AppPagination from '../../components/ui/AppPagination.vue';
import AppEmptyState from '../../components/ui/AppEmptyState.vue';
import AppConfirmDialog from '../../components/ui/AppConfirmDialog.vue';
import AppLoadingSpinner from '../../components/ui/AppLoadingSpinner.vue';

const router = useRouter();

const loading = ref(false);
const vacancies = ref([]);
const subscription = ref(null);
const stats = ref({
  active: 0,
  pending: 0,
  closed: 0,
  total_applications: 0,
});

const filters = ref({
  search: '',
  status: null,
  work_type: null,
  category: null,
});

const currentPage = ref(1);
const perPage = ref(15);
const totalVacancies = ref(0);

const statusOptions = [
  { label: 'Barcha statuslar', value: null },
  { label: 'Faol', value: 'active' },
  { label: 'Kutilmoqda', value: 'pending' },
  { label: 'To\'xtatilgan', value: 'paused' },
  { label: 'Yopilgan', value: 'closed' },
  { label: 'Qoralama', value: 'draft' },
];

const workTypeOptions = [
  { label: 'Barcha turlar', value: null },
  { label: 'To\'liq ish kuni', value: 'full_time' },
  { label: 'Yarim ish kuni', value: 'part_time' },
  { label: 'Masofaviy', value: 'remote' },
  { label: 'Freelance', value: 'freelance' },
];

const categoryOptions = [
  { label: 'Barcha kategoriyalar', value: null },
  { label: 'IT va Texnologiya', value: 'it' },
  { label: 'Savdo va Marketing', value: 'sales' },
  { label: 'Moliya va Buxgalteriya', value: 'finance' },
  { label: 'Qurilish', value: 'construction' },
  { label: 'Ovqatlanish', value: 'food' },
  { label: 'Ta\'lim', value: 'education' },
  { label: 'Tibbiyot', value: 'medicine' },
  { label: 'Transport va Logistika', value: 'transport' },
  { label: 'Boshqa', value: 'other' },
];

const columns = [
  { key: 'title', label: 'Vakansiya', sortable: true },
  { key: 'status', label: 'Status', sortable: true },
  { key: 'work_type', label: 'Bandlik turi', sortable: false },
  { key: 'salary', label: 'Maosh', sortable: false },
  { key: 'applications_count', label: 'Arizalar', sortable: true },
  { key: 'recommended_count', label: 'Mos nomzodlar', sortable: false },
  { key: 'created_at', label: 'Yaratilgan', sortable: true },
  { key: 'actions', label: '', sortable: false },
];

const showDeleteDialog = ref(false);
const deleteLoading = ref(false);
const vacancyToDelete = ref(null);

onMounted(() => {
  fetchVacancies();
});

async function fetchVacancies() {
  loading.value = true;

  try {
    const params = {
      page: currentPage.value,
      per_page: perPage.value,
    };

    // Get raw values from select objects
    const statusVal = filters.value.status;
    const workTypeVal = filters.value.work_type;
    const categoryVal = filters.value.category;

    if (filters.value.search) params.search = filters.value.search;
    if (statusVal?.value) params.status = statusVal.value;
    if (workTypeVal?.value) params.work_type = workTypeVal.value;
    if (categoryVal?.value) params.category = categoryVal.value;

    const { data } = await axios.get('/api/recruiter/vacancies', { params });

    vacancies.value = data.vacancies?.data || [];
    totalVacancies.value = data.vacancies?.total || 0;
    if (data.stats) {
      stats.value = data.stats;
    }
    if (data.subscription) {
      subscription.value = data.subscription;
    }
  } catch (error) {
    console.error('Failed to fetch vacancies:', error);
    toast.error('Vakansiyalarni yuklashda xatolik');
  } finally {
    loading.value = false;
  }
}

async function toggleVacancyStatus(vacancy) {
  const wasActive = vacancy.status === 'active';
  const action = wasActive ? 'to\'xtatish' : 'faollashtirish';

  try {
    const { data } = await axios.put(`/api/recruiter/vacancies/${vacancy.id}/toggle-status`);

    // Update local data
    const idx = vacancies.value.findIndex(v => v.id === vacancy.id);
    if (idx !== -1) {
      vacancies.value[idx].status = data.vacancy.status;
    }

    // Refresh stats
    fetchVacancies();

    toast.success(wasActive ? 'Vakansiya to\'xtatildi' : 'Vakansiya faollashtirildi');
  } catch (error) {
    console.error('Failed to toggle status:', error);
    if (error.response?.data?.limit_reached) {
      toast.error(error.response.data.message);
    } else {
      toast.error(`Statusni ${action}da xatolik`);
    }
  }
}

function getPlanLabel(plan) {
  const labels = {
    free: 'Bepul',
    business: 'Biznes',
    recruiter_pro: 'Recruiter Pro',
    agency: 'Agency',
    corporate: 'Korporativ',
  };
  return labels[plan] || plan;
}

function handleRowClick(row) {
  viewVacancy(row.id);
}

function viewVacancy(id) {
  router.push(`/dashboard/vacancies/${id}`);
}

function viewRecommended(id) {
  router.push({ name: 'vacancy-detail', params: { id }, query: { tab: 'recommended' } });
}

function editVacancy(id) {
  router.push(`/dashboard/vacancies/${id}/edit`);
}

function deleteVacancy(id) {
  vacancyToDelete.value = id;
  showDeleteDialog.value = true;
}

async function confirmDelete() {
  deleteLoading.value = true;

  try {
    await axios.delete(`/api/recruiter/vacancies/${vacancyToDelete.value}`);
    vacancies.value = vacancies.value.filter(v => v.id !== vacancyToDelete.value);
    totalVacancies.value--;
    toast.success('Vakansiya muvaffaqiyatli o\'chirildi');

    // Refresh stats
    fetchVacancies();
  } catch (error) {
    console.error('Failed to delete vacancy:', error);
    toast.error('Vakansiyani o\'chirishda xatolik');
  } finally {
    deleteLoading.value = false;
    showDeleteDialog.value = false;
    vacancyToDelete.value = null;
  }
}
</script>
