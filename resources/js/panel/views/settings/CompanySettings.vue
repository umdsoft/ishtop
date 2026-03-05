<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">
          Kompaniyalar
        </h1>
        <p class="text-surface-600 dark:text-surface-400 mt-1">
          Kompaniyalaringizni boshqaring va ular orasida almashing
        </p>
      </div>
      <AppButton variant="primary" @click="openCreateModal">
        <template #icon-left>
          <PlusIcon class="w-5 h-5" />
        </template>
        Yangi kompaniya
      </AppButton>
    </div>

    <!-- Loading state -->
    <div v-if="pageLoading" class="flex justify-center py-12">
      <AppLoadingSpinner size="lg" text="Yuklanmoqda..." />
    </div>

    <!-- Company cards grid -->
    <div v-else-if="companies.length > 0" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
      <div
        v-for="company in companies"
        :key="company.id"
        class="relative bg-surface-0 dark:bg-surface-900 border rounded-xl p-5 transition-all"
        :class="company.id === activeCompanyId
          ? 'border-brand-500 ring-2 ring-brand-500/20'
          : 'border-surface-200 dark:border-surface-800 hover:border-surface-300 dark:hover:border-surface-700'"
      >
        <!-- Active badge -->
        <div
          v-if="company.id === activeCompanyId"
          class="absolute top-3 right-3 px-2 py-0.5 bg-brand-100 dark:bg-brand-900 text-brand-700 dark:text-brand-300 text-xs font-medium rounded-full"
        >
          Aktiv
        </div>

        <!-- Company info -->
        <div class="flex items-start gap-3 mb-4">
          <div class="w-12 h-12 rounded-xl bg-surface-100 dark:bg-surface-800 flex items-center justify-center text-lg font-bold text-surface-600 dark:text-surface-400 shrink-0">
            {{ company.company_name?.[0]?.toUpperCase() || 'K' }}
          </div>
          <div class="flex-1 min-w-0 pr-12">
            <h3 class="font-semibold text-surface-900 dark:text-surface-100 truncate">
              {{ company.company_name }}
            </h3>
            <p class="text-sm text-surface-500 dark:text-surface-400">
              {{ company.industry || "Soha ko'rsatilmagan" }}
            </p>
          </div>
        </div>

        <!-- Company details -->
        <div class="space-y-1.5 text-sm text-surface-600 dark:text-surface-400 mb-4">
          <p v-if="company.phone" class="flex items-center gap-2">
            <PhoneIcon class="w-4 h-4 text-surface-400" />
            {{ company.phone }}
          </p>
          <p v-if="company.address" class="flex items-center gap-2 truncate">
            <MapPinIcon class="w-4 h-4 text-surface-400 shrink-0" />
            <span class="truncate">{{ company.address }}</span>
          </p>
          <p v-if="company.employees_count" class="flex items-center gap-2">
            <UsersIcon class="w-4 h-4 text-surface-400" />
            {{ company.employees_count }} xodim
          </p>
          <p v-if="company.website" class="flex items-center gap-2 truncate">
            <GlobeAltIcon class="w-4 h-4 text-surface-400 shrink-0" />
            <span class="truncate">{{ company.website }}</span>
          </p>
        </div>

        <!-- Verification badge -->
        <div v-if="company.verification_level && company.verification_level !== 'new'" class="mb-4">
          <span
            class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium rounded-full"
            :class="company.verification_level === 'verified' || company.verification_level === 'top'
              ? 'bg-success-100 dark:bg-success-900/20 text-success-700 dark:text-success-400'
              : 'bg-warning-100 dark:bg-warning-900/20 text-warning-700 dark:text-warning-400'"
          >
            <CheckBadgeIcon class="w-3.5 h-3.5" />
            {{ company.verification_level === 'top' ? 'TOP' : company.verification_level === 'verified' ? 'Tasdiqlangan' : 'Kutilmoqda' }}
          </span>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-2 pt-3 border-t border-surface-100 dark:border-surface-800">
          <AppButton
            v-if="company.id !== activeCompanyId"
            variant="primary"
            size="sm"
            @click="handleSwitch(company.id)"
            :loading="switchingId === company.id"
          >
            Tanlash
          </AppButton>
          <AppButton
            variant="secondary"
            size="sm"
            @click="openEditModal(company)"
          >
            Tahrirlash
          </AppButton>
          <AppButton
            v-if="companies.length > 1"
            variant="ghost"
            size="sm"
            class="!text-danger-600 dark:!text-danger-400"
            @click="confirmDelete(company)"
          >
            O'chirish
          </AppButton>
        </div>
      </div>
    </div>

    <!-- Empty state -->
    <div v-else class="text-center py-12">
      <BuildingOfficeIcon class="w-16 h-16 mx-auto text-surface-300 dark:text-surface-600 mb-4" />
      <h3 class="text-lg font-medium text-surface-900 dark:text-surface-100 mb-2">
        Kompaniya topilmadi
      </h3>
      <p class="text-surface-500 dark:text-surface-400 mb-6">
        Birinchi kompaniyangizni yarating
      </p>
      <AppButton variant="primary" @click="openCreateModal">
        Kompaniya yaratish
      </AppButton>
    </div>

    <!-- Create/Edit Modal -->
    <AppModal
      :show="showModal"
      :title="editingCompany ? 'Kompaniyani tahrirlash' : 'Yangi kompaniya'"
      size="lg"
      @close="closeModal"
    >
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <AppInput
          v-model="form.company_name"
          label="Kompaniya nomi"
          placeholder="Masalan: TechStar LLC"
          required
          :error="formErrors.company_name?.[0]"
        />

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <AppSelect
            v-model="form.industry"
            label="Soha"
            placeholder="Soha tanlang"
            :options="industryOptions"
          />
          <AppSelect
            v-model="form.employees_count"
            label="Xodimlar soni"
            placeholder="Tanlang"
            :options="employeeOptions"
          />
        </div>

        <AppTextarea
          v-model="form.description"
          label="Tavsif"
          placeholder="Kompaniya haqida qisqacha ma'lumot"
          :rows="3"
        />

        <AppInput
          v-model="form.address"
          label="Manzil"
          placeholder="Masalan: Toshkent, Amir Temur 1"
        />

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <AppInput
            v-model="form.phone"
            label="Telefon"
            placeholder="+998901234567"
          />
          <AppInput
            v-model="form.website"
            label="Veb-sayt"
            placeholder="https://example.com"
          />
        </div>

        <AppInput
          v-if="editingCompany"
          v-model="form.stir_number"
          label="STIR raqami"
          placeholder="123456789"
        />
      </form>

      <template #footer>
        <AppButton variant="secondary" @click="closeModal">
          Bekor qilish
        </AppButton>
        <AppButton variant="primary" @click="handleSubmit" :loading="saving">
          {{ editingCompany ? 'Saqlash' : 'Yaratish' }}
        </AppButton>
      </template>
    </AppModal>

    <!-- Delete confirmation -->
    <AppConfirmDialog
      :open="showDeleteConfirm"
      title="Kompaniyani o'chirish"
      :message="`'${deletingCompany?.company_name}' kompaniyasini o'chirmoqchimisiz? Barcha vakansiyalar va ma'lumotlar saqlanib qoladi.`"
      confirm-text="O'chirish"
      type="danger"
      @confirm="handleDelete"
      @cancel="showDeleteConfirm = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '../../stores/auth';
import { toast } from 'vue-sonner';
import axios from 'axios';
import AppButton from '../../components/ui/AppButton.vue';
import AppInput from '../../components/ui/AppInput.vue';
import AppSelect from '../../components/ui/AppSelect.vue';
import AppTextarea from '../../components/ui/AppTextarea.vue';
import AppModal from '../../components/ui/AppModal.vue';
import AppConfirmDialog from '../../components/ui/AppConfirmDialog.vue';
import AppLoadingSpinner from '../../components/ui/AppLoadingSpinner.vue';
import {
  PlusIcon,
  PhoneIcon,
  MapPinIcon,
  UsersIcon,
  GlobeAltIcon,
  BuildingOfficeIcon,
  CheckBadgeIcon,
} from '@heroicons/vue/24/outline';

const authStore = useAuthStore();

const pageLoading = ref(false);
const saving = ref(false);
const switchingId = ref(null);

const companies = computed(() => authStore.companies);
const activeCompanyId = computed(() => authStore.activeCompanyId);

// Modal state
const showModal = ref(false);
const editingCompany = ref(null);
const formErrors = ref({});

const defaultForm = {
  company_name: '',
  industry: null,
  description: '',
  address: '',
  phone: '',
  website: '',
  employees_count: null,
  stir_number: '',
};
const form = ref({ ...defaultForm });

// Delete state
const showDeleteConfirm = ref(false);
const deletingCompany = ref(null);

const industryOptions = [
  { value: 'it', label: 'IT va Texnologiya' },
  { value: 'finance', label: 'Moliya va Bank' },
  { value: 'education', label: "Ta'lim" },
  { value: 'medicine', label: 'Tibbiyot' },
  { value: 'retail', label: 'Savdo' },
  { value: 'construction', label: 'Qurilish' },
  { value: 'production', label: 'Ishlab chiqarish' },
  { value: 'logistics', label: 'Logistika va Transport' },
  { value: 'horeca', label: 'HoReCa' },
  { value: 'marketing', label: 'Marketing va Reklama' },
  { value: 'other', label: 'Boshqa' },
];

const employeeOptions = [
  { value: '1-10', label: '1-10' },
  { value: '11-50', label: '11-50' },
  { value: '51-200', label: '51-200' },
  { value: '201-500', label: '201-500' },
  { value: '500+', label: '500+' },
];

onMounted(async () => {
  pageLoading.value = true;
  await authStore.fetchCompanies();
  pageLoading.value = false;
});

function openCreateModal() {
  editingCompany.value = null;
  form.value = { ...defaultForm };
  formErrors.value = {};
  showModal.value = true;
}

function openEditModal(company) {
  editingCompany.value = company;
  form.value = {
    company_name: company.company_name || '',
    industry: company.industry
      ? industryOptions.find(o => o.value === company.industry) || null
      : null,
    description: company.description || '',
    address: company.address || '',
    phone: company.phone || '',
    website: company.website || '',
    employees_count: company.employees_count
      ? employeeOptions.find(o => o.value === company.employees_count) || null
      : null,
    stir_number: company.stir_number || '',
  };
  formErrors.value = {};
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
  editingCompany.value = null;
}

function getFormData() {
  return {
    company_name: form.value.company_name,
    industry: form.value.industry?.value || form.value.industry || null,
    description: form.value.description || null,
    address: form.value.address || null,
    phone: form.value.phone || null,
    website: form.value.website || null,
    employees_count: form.value.employees_count?.value || form.value.employees_count || null,
    stir_number: form.value.stir_number || null,
  };
}

async function handleSubmit() {
  saving.value = true;
  formErrors.value = {};

  try {
    if (editingCompany.value) {
      await axios.put(`/api/recruiter/companies/${editingCompany.value.id}`, getFormData());
      toast.success('Kompaniya yangilandi');
    } else {
      await axios.post('/api/recruiter/companies', getFormData());
      toast.success('Yangi kompaniya yaratildi');
    }
    await authStore.fetchCompanies();
    await authStore.fetchUser();
    closeModal();
  } catch (error) {
    if (error.response?.status === 422) {
      formErrors.value = error.response.data.errors || {};
    } else {
      toast.error(error.response?.data?.message || 'Xatolik yuz berdi');
    }
  } finally {
    saving.value = false;
  }
}

async function handleSwitch(companyId) {
  switchingId.value = companyId;
  const result = await authStore.switchCompany(companyId);
  switchingId.value = null;
  if (result.success) {
    toast.success('Aktiv kompaniya almashtirildi');
  } else {
    toast.error(result.message);
  }
}

function confirmDelete(company) {
  deletingCompany.value = company;
  showDeleteConfirm.value = true;
}

async function handleDelete() {
  try {
    await axios.delete(`/api/recruiter/companies/${deletingCompany.value.id}`);
    toast.success("Kompaniya o'chirildi");
    await authStore.fetchCompanies();
    await authStore.fetchUser();
  } catch (error) {
    toast.error(error.response?.data?.message || 'Xatolik yuz berdi');
  }
  showDeleteConfirm.value = false;
  deletingCompany.value = null;
}
</script>
