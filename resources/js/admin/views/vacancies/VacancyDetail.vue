<template>
  <div class="space-y-6">
    <div class="flex items-center gap-3">
      <button @click="$router.back()" class="p-2 hover:bg-surface-100 dark:hover:bg-surface-800 rounded-lg transition-colors">
        <ArrowLeftIcon class="w-5 h-5" />
      </button>
      <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">{{ vacancy?.title_uz || 'Vakansiya' }}</h1>
      <span v-if="vacancy" :class="['text-xs px-2 py-0.5 rounded-full font-medium ml-2', statusClass(vacancy.status)]">
        {{ statusLabel(vacancy.status) }}
      </span>
    </div>

    <div v-if="loading" class="text-center py-12 text-surface-500">{{ $t('common.loading') }}</div>

    <div v-else-if="vacancy" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Main Details -->
      <AppCard title="Vakansiya ma'lumotlari" class="lg:col-span-2">
        <dl class="grid grid-cols-2 gap-4">
          <div>
            <dt class="text-sm text-surface-500">Sarlavha (uz)</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100">{{ vacancy.title_uz || '—' }}</dd>
          </div>
          <div>
            <dt class="text-sm text-surface-500">Sarlavha (ru)</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100">{{ vacancy.title_ru || '—' }}</dd>
          </div>
          <div class="col-span-2">
            <dt class="text-sm text-surface-500">Tavsif</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100 whitespace-pre-line">{{ vacancy.description || '—' }}</dd>
          </div>
          <div>
            <dt class="text-sm text-surface-500">Maosh (dan)</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100">{{ vacancy.salary_from ? Number(vacancy.salary_from).toLocaleString() : '—' }}</dd>
          </div>
          <div>
            <dt class="text-sm text-surface-500">Maosh (gacha)</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100">{{ vacancy.salary_to ? Number(vacancy.salary_to).toLocaleString() : '—' }}</dd>
          </div>
          <div>
            <dt class="text-sm text-surface-500">Ish turi</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100">{{ vacancy.work_type || '—' }}</dd>
          </div>
          <div>
            <dt class="text-sm text-surface-500">Kategoriya</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100">{{ vacancy.category?.name || '—' }}</dd>
          </div>
          <div>
            <dt class="text-sm text-surface-500">Shahar</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100">{{ vacancy.city || '—' }}</dd>
          </div>
          <div>
            <dt class="text-sm text-surface-500">Ko'rishlar</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100">{{ vacancy.views_count || 0 }}</dd>
          </div>
          <div>
            <dt class="text-sm text-surface-500">Arizalar soni</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100">{{ vacancy.applications_count || 0 }}</dd>
          </div>
          <div>
            <dt class="text-sm text-surface-500">{{ $t('common.date') }}</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100">{{ formatDate(vacancy.created_at) }}</dd>
          </div>
        </dl>
      </AppCard>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- Actions -->
        <AppCard title="Amallar">
          <div class="space-y-3">
            <button
              v-if="vacancy.status === 'pending'"
              @click="approveVacancy"
              class="w-full py-2 px-4 text-sm font-medium rounded-lg bg-success-50 text-success-700 hover:bg-success-100 dark:bg-success-950/30 dark:text-success-400 transition-colors"
            >
              <CheckIcon class="w-4 h-4 inline mr-1" /> Tasdiqlash
            </button>
            <button
              v-if="vacancy.status === 'pending'"
              @click="rejectVacancy"
              class="w-full py-2 px-4 text-sm font-medium rounded-lg bg-danger-50 text-danger-700 hover:bg-danger-100 dark:bg-danger-950/30 dark:text-danger-400 transition-colors"
            >
              <XMarkIcon class="w-4 h-4 inline mr-1" /> Rad etish
            </button>
            <p v-if="vacancy.status !== 'pending'" class="text-sm text-surface-500 text-center py-2">
              Amalni bajarish mumkin emas
            </p>
          </div>
        </AppCard>

        <!-- Employer Info -->
        <AppCard title="Ish beruvchi">
          <div class="space-y-2 text-sm" v-if="vacancy.employer">
            <div>
              <span class="text-surface-500">Kompaniya: </span>
              <span class="font-medium text-surface-900 dark:text-surface-100">{{ vacancy.employer.company_name || '—' }}</span>
            </div>
            <div>
              <span class="text-surface-500">Telefon: </span>
              <span class="font-medium text-surface-900 dark:text-surface-100">{{ vacancy.employer.phone || '—' }}</span>
            </div>
            <div v-if="vacancy.employer.user">
              <span class="text-surface-500">Foydalanuvchi: </span>
              <span class="font-medium text-surface-900 dark:text-surface-100">{{ vacancy.employer.user.first_name }} {{ vacancy.employer.user.last_name }}</span>
            </div>
          </div>
          <p v-else class="text-sm text-surface-500">Ma'lumot yo'q</p>
        </AppCard>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import { toast } from 'vue-sonner';
import AppCard from '@panel/components/ui/AppCard.vue';
import { ArrowLeftIcon, CheckIcon, XMarkIcon } from '@heroicons/vue/24/outline';

const route = useRoute();
const vacancy = ref(null);
const loading = ref(true);

function formatDate(d) {
  if (!d) return '';
  return new Date(d).toLocaleDateString('uz-UZ', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function statusClass(status) {
  const map = {
    active: 'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400',
    pending: 'bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-400',
    closed: 'bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-400',
    expired: 'bg-surface-100 text-surface-700 dark:bg-surface-800 dark:text-surface-400',
    rejected: 'bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-400',
  };
  return map[status] || 'bg-surface-100 text-surface-600';
}

function statusLabel(status) {
  const map = {
    active: 'Faol',
    pending: 'Kutilmoqda',
    closed: 'Yopilgan',
    expired: 'Muddati o\'tgan',
    rejected: 'Rad etilgan',
  };
  return map[status] || status;
}

async function fetchVacancy() {
  try {
    const res = await axios.get(`/api/admin/vacancies/${route.params.id}`);
    vacancy.value = res.data.vacancy || res.data;
  } catch (err) {
    toast.error('Vakansiya topilmadi');
  } finally {
    loading.value = false;
  }
}

async function approveVacancy() {
  try {
    const res = await axios.post(`/api/admin/vacancies/${vacancy.value.id}/approve`);
    vacancy.value.status = 'active';
    toast.success(res.data.message || 'Vakansiya tasdiqlandi');
  } catch (err) {
    toast.error(err.response?.data?.message || 'Xatolik');
  }
}

async function rejectVacancy() {
  try {
    const res = await axios.post(`/api/admin/vacancies/${vacancy.value.id}/reject`);
    vacancy.value.status = 'rejected';
    toast.success(res.data.message || 'Vakansiya rad etildi');
  } catch (err) {
    toast.error(err.response?.data?.message || 'Xatolik');
  }
}

onMounted(fetchVacancy);
</script>
