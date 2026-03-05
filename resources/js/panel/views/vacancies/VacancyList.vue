<template>
  <div>
    <!-- Header -->
    <div class="mb-6">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">Vakansiyalar</h1>
          <p class="text-surface-600 dark:text-surface-400 mt-1">Barcha vakansiyalaringizni boshqaring</p>
        </div>
        <router-link to="/dashboard/vacancies/create">
          <AppButton variant="primary">
            <template #icon-left>
              <PlusIcon class="h-5 w-5" />
            </template>
            Yangi vakansiya
          </AppButton>
        </router-link>
      </div>

      <!-- Filters -->
      <AppCard no-padding>
        <div class="p-4">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <AppSearchInput
              v-model="filters.search"
              placeholder="Vakansiya qidirish..."
              @search="handleSearch"
            />

            <AppSelect
              v-model="filters.status"
              :options="statusOptions"
              label-key="label"
              value-key="value"
              placeholder="Status"
            />

            <AppSelect
              v-model="filters.employment_type"
              :options="employmentTypeOptions"
              label-key="label"
              value-key="value"
              placeholder="Bandlik turi"
            />

            <AppSelect
              v-model="filters.category_id"
              :options="categories"
              label-key="name"
              value-key="id"
              placeholder="Kategoriya"
              searchable
            />
          </div>
        </div>
      </AppCard>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
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
            <BriefcaseIcon class="h-6 w-6 text-surface-600 dark:text-surface-400" />
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

    <!-- Vacancies Table -->
    <AppCard no-padding>
      <AppTable
        :columns="columns"
        :data="filteredVacancies"
        :hoverable="true"
        :clickable="true"
        @row-click="handleRowClick"
      >
        <template #cell-title="{ row }">
          <div>
            <p class="font-medium text-surface-900 dark:text-surface-100">{{ row.title }}</p>
            <p class="text-sm text-surface-500 dark:text-surface-400">{{ row.company_name }}</p>
          </div>
        </template>

        <template #cell-status="{ row }">
          <AppBadge :variant="getStatusVariant(row.status)">
            {{ getStatusLabel(row.status) }}
          </AppBadge>
        </template>

        <template #cell-employment_type="{ row }">
          <span class="text-sm text-surface-700 dark:text-surface-300">
            {{ getEmploymentTypeLabel(row.employment_type) }}
          </span>
        </template>

        <template #cell-salary="{ row }">
          <span class="text-sm text-surface-700 dark:text-surface-300">
            {{ formatSalary(row.salary_min, row.salary_max) }}
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

        <template #cell-created_at="{ row }">
          <span class="text-sm text-surface-600 dark:text-surface-400">
            {{ formatDate(row.created_at) }}
          </span>
        </template>

        <template #cell-actions="{ row }">
          <div class="flex items-center gap-2" @click.stop>
            <AppTooltip content="Ko'rish" position="top">
              <button
                class="p-1.5 rounded-md hover:bg-surface-100 dark:hover:bg-surface-800 text-surface-600 dark:text-surface-400 transition-colors"
                @click="viewVacancy(row.id)"
              >
                <EyeIcon class="h-5 w-5" />
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

      <div v-if="filteredVacancies.length > 0" class="px-6 py-4 border-t border-surface-200 dark:border-surface-700">
        <AppPagination
          v-model:current-page="currentPage"
          :total="totalVacancies"
          :per-page="perPage"
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
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { toast } from 'vue-sonner';
import {
  PlusIcon,
  EyeIcon,
  PencilIcon,
  TrashIcon,
  CheckCircleIcon,
  ClockIcon,
  BriefcaseIcon,
  UsersIcon,
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

const router = useRouter();

// Mock data (replace with API calls)
const stats = ref({
  active: 12,
  pending: 3,
  closed: 24,
  total_applications: 487,
});

const categories = ref([
  { id: 1, name: 'IT va Texnologiya' },
  { id: 2, name: 'Savdo va Marketing' },
  { id: 3, name: 'Moliya va Buxgalteriya' },
  { id: 4, name: 'Qurilish' },
  { id: 5, name: 'Ovqatlanish' },
]);

const vacancies = ref([
  {
    id: 1,
    title: 'Senior PHP Developer',
    company_name: 'TechCorp',
    status: 'active',
    employment_type: 'full_time',
    salary_min: 5000000,
    salary_max: 8000000,
    applications_count: 45,
    new_applications_count: 3,
    created_at: '2024-02-20',
  },
  {
    id: 2,
    title: 'Ofitsiant',
    company_name: 'Grand Hotel',
    status: 'active',
    employment_type: 'part_time',
    salary_min: 2000000,
    salary_max: 3000000,
    applications_count: 78,
    new_applications_count: 12,
    created_at: '2024-02-18',
  },
  {
    id: 3,
    title: 'Savdo bo\'limi menejeri',
    company_name: 'FoodChain',
    status: 'pending',
    employment_type: 'full_time',
    salary_min: 4000000,
    salary_max: 6000000,
    applications_count: 23,
    new_applications_count: 0,
    created_at: '2024-02-15',
  },
]);

const filters = ref({
  search: '',
  status: null,
  employment_type: null,
  category_id: null,
});

const currentPage = ref(1);
const perPage = ref(10);

const statusOptions = [
  { label: 'Barcha statuslar', value: null },
  { label: 'Faol', value: 'active' },
  { label: 'Kutilmoqda', value: 'pending' },
  { label: 'Yopilgan', value: 'closed' },
];

const employmentTypeOptions = [
  { label: 'Barcha turlar', value: null },
  { label: 'To\'liq ish kuni', value: 'full_time' },
  { label: 'Yarim ish kuni', value: 'part_time' },
  { label: 'Masofaviy', value: 'remote' },
  { label: 'Freelance', value: 'freelance' },
];

const columns = [
  { key: 'title', label: 'Vakansiya', sortable: true },
  { key: 'status', label: 'Status', sortable: true },
  { key: 'employment_type', label: 'Bandlik turi', sortable: false },
  { key: 'salary', label: 'Maosh', sortable: false },
  { key: 'applications_count', label: 'Arizalar', sortable: true },
  { key: 'created_at', label: 'Yaratilgan', sortable: true },
  { key: 'actions', label: '', sortable: false },
];

const showDeleteDialog = ref(false);
const deleteLoading = ref(false);
const vacancyToDelete = ref(null);

const filteredVacancies = computed(() => {
  let result = vacancies.value;

  if (filters.value.search) {
    const search = filters.value.search.toLowerCase();
    result = result.filter(v =>
      v.title.toLowerCase().includes(search) ||
      v.company_name.toLowerCase().includes(search)
    );
  }

  if (filters.value.status) {
    result = result.filter(v => v.status === filters.value.status);
  }

  if (filters.value.employment_type) {
    result = result.filter(v => v.employment_type === filters.value.employment_type);
  }

  return result;
});

const totalVacancies = computed(() => filteredVacancies.value.length);

function getStatusVariant(status) {
  const variants = {
    active: 'success',
    pending: 'warning',
    closed: 'default',
  };
  return variants[status] || 'default';
}

function getStatusLabel(status) {
  const labels = {
    active: 'Faol',
    pending: 'Kutilmoqda',
    closed: 'Yopilgan',
  };
  return labels[status] || status;
}

function getEmploymentTypeLabel(type) {
  const labels = {
    full_time: 'To\'liq ish kuni',
    part_time: 'Yarim ish kuni',
    remote: 'Masofaviy',
    freelance: 'Freelance',
  };
  return labels[type] || type;
}

function formatSalary(min, max) {
  if (!min && !max) return 'Kelishiladi';

  const formatNumber = (num) => {
    return new Intl.NumberFormat('uz-UZ').format(num);
  };

  if (min && max) {
    return `${formatNumber(min)} - ${formatNumber(max)} so'm`;
  }

  if (min) {
    return `${formatNumber(min)}+ so'm`;
  }

  return `${formatNumber(max)} so'm gacha`;
}

function formatDate(date) {
  return new Date(date).toLocaleDateString('uz-UZ', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
}

function handleSearch(query) {
  // Debounced search already handled by AppSearchInput
  console.log('Searching:', query);
}

function handleRowClick(row) {
  viewVacancy(row.id);
}

function viewVacancy(id) {
  router.push(`/dashboard/vacancies/${id}`);
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

  // Simulate API call
  await new Promise(resolve => setTimeout(resolve, 1000));

  vacancies.value = vacancies.value.filter(v => v.id !== vacancyToDelete.value);

  deleteLoading.value = false;
  showDeleteDialog.value = false;
  vacancyToDelete.value = null;

  toast.success('Vakansiya muvaffaqiyatli o\'chirildi');
}
</script>
