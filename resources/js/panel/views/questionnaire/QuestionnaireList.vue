<template>
  <div>
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">Savolnomalar</h1>
        <p class="text-surface-600 dark:text-surface-400 mt-1">
          Vakansiyalar uchun savolnomalarni boshqaring
        </p>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex items-center justify-center py-20">
      <AppLoadingSpinner size="lg" text="Vakansiyalar yuklanmoqda..." />
    </div>

    <!-- Vacancy List -->
    <div v-else-if="vacancies.length > 0" class="space-y-3">
      <div
        v-for="vacancy in vacancies"
        :key="vacancy.id"
        class="group bg-surface-0 dark:bg-surface-900 border border-surface-200 dark:border-surface-800 rounded-xl p-5 hover:border-brand-300 dark:hover:border-brand-700 hover:shadow-md transition-all cursor-pointer"
        @click="openBuilder(vacancy.id)"
      >
        <div class="flex items-center justify-between">
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-3 mb-1.5">
              <h3 class="text-base font-semibold text-surface-900 dark:text-surface-100 truncate">
                {{ vacancy.title }}
              </h3>
              <AppBadge :variant="getStatusVariant(vacancy.status)" size="sm">
                {{ getStatusLabel(vacancy.status) }}
              </AppBadge>
            </div>
            <div class="flex items-center gap-4 text-sm text-surface-500 dark:text-surface-400">
              <span>{{ vacancy.applications_count || 0 }} ariza</span>
              <span>{{ formatDate(vacancy.created_at) }}</span>
            </div>
          </div>

          <div class="flex items-center gap-4">
            <!-- Questionnaire status -->
            <div class="text-right">
              <AppBadge
                v-if="vacancy.has_questionnaire"
                variant="success"
                size="sm"
              >
                <ClipboardDocumentCheckIcon class="w-3.5 h-3.5" />
                Savolnoma mavjud
              </AppBadge>
              <AppBadge
                v-else
                variant="default"
                size="sm"
              >
                Savolnoma yo'q
              </AppBadge>
            </div>

            <ChevronRightIcon class="w-5 h-5 text-surface-400 group-hover:text-brand-500 transition-colors" />
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-20">
      <ClipboardDocumentListIcon class="h-16 w-16 mx-auto text-surface-400 dark:text-surface-500 mb-4" />
      <h3 class="text-lg font-semibold text-surface-900 dark:text-surface-100 mb-2">
        Vakansiyalar topilmadi
      </h3>
      <p class="text-surface-600 dark:text-surface-400 mb-4">
        Savolnoma yaratish uchun avval vakansiya yarating
      </p>
      <AppButton variant="primary" @click="$router.push('/dashboard/vacancies/create')">
        Vakansiya yaratish
      </AppButton>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { toast } from 'vue-sonner';
import {
  ClipboardDocumentListIcon,
  ClipboardDocumentCheckIcon,
  ChevronRightIcon,
} from '@heroicons/vue/24/outline';
import AppButton from '../../components/ui/AppButton.vue';
import AppBadge from '../../components/ui/AppBadge.vue';
import AppLoadingSpinner from '../../components/ui/AppLoadingSpinner.vue';

const router = useRouter();

const vacancies = ref([]);
const loading = ref(true);

onMounted(() => {
  fetchVacancies();
});

async function fetchVacancies() {
  loading.value = true;
  try {
    const { data } = await axios.get('/api/recruiter/vacancies', {
      params: { per_page: 100 },
    });
    vacancies.value = data.data || [];
  } catch (error) {
    toast.error('Vakansiyalarni yuklashda xatolik');
  } finally {
    loading.value = false;
  }
}

function openBuilder(vacancyId) {
  router.push({ name: 'questionnaire-builder', params: { vacancyId } });
}

function getStatusVariant(status) {
  const variants = {
    active: 'success',
    pending: 'warning',
    draft: 'default',
    closed: 'default',
  };
  return variants[status] || 'default';
}

function getStatusLabel(status) {
  const labels = {
    active: 'Faol',
    pending: 'Kutilmoqda',
    draft: 'Qoralama',
    closed: 'Yopilgan',
  };
  return labels[status] || status;
}

function formatDate(date) {
  if (!date) return '';
  return new Date(date).toLocaleDateString('uz-UZ', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
}
</script>
