<template>
  <div>
    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center py-20">
      <AppLoadingSpinner size="lg" text="Vakansiya yuklanmoqda..." />
    </div>

    <!-- Content -->
    <div v-else-if="vacancy">
      <!-- Header -->
      <div class="mb-6">
        <div class="flex items-center gap-3 mb-4">
          <button
            class="p-2 rounded-lg hover:bg-surface-100 dark:hover:bg-surface-800 transition-colors"
            @click="$router.push('/dashboard/vacancies')"
          >
            <ArrowLeftIcon class="h-5 w-5 text-surface-600 dark:text-surface-400" />
          </button>
          <div class="flex-1">
            <div class="flex items-center gap-3">
              <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">{{ vacancy.title }}</h1>
              <AppBadge :variant="getStatusVariant(vacancy.status)">
                {{ getStatusLabel(vacancy.status) }}
              </AppBadge>
            </div>
            <p class="text-surface-600 dark:text-surface-400 mt-1">
              {{ vacancy.company_name }} • {{ formatDate(vacancy.created_at) }}
            </p>
          </div>
          <div class="flex items-center gap-2">
            <AppButton
              variant="outline"
              @click="$router.push(`/dashboard/vacancies/${vacancy.id}/edit`)"
            >
              <template #icon-left>
                <PencilIcon class="h-5 w-5" />
              </template>
              Tahrirlash
            </AppButton>
            <AppDropdown
              :items="actionMenuItems"
              label="Amallar"
              variant="outline"
            />
          </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-4 gap-4">
          <AppCard>
            <div class="text-center">
              <p class="text-sm text-surface-600 dark:text-surface-400">Arizalar</p>
              <p class="text-2xl font-bold text-surface-900 dark:text-surface-100 mt-1">
                {{ vacancy.applications_count }}
              </p>
            </div>
          </AppCard>
          <AppCard>
            <div class="text-center">
              <p class="text-sm text-surface-600 dark:text-surface-400">Yangi</p>
              <p class="text-2xl font-bold text-brand-600 dark:text-brand-400 mt-1">
                {{ vacancy.new_applications_count }}
              </p>
            </div>
          </AppCard>
          <AppCard>
            <div class="text-center">
              <p class="text-sm text-surface-600 dark:text-surface-400">Ko'rilgan</p>
              <p class="text-2xl font-bold text-surface-900 dark:text-surface-100 mt-1">
                {{ vacancy.views_count }}
              </p>
            </div>
          </AppCard>
          <AppCard>
            <div class="text-center">
              <p class="text-sm text-surface-600 dark:text-surface-400">O'rtacha ball</p>
              <p class="text-2xl font-bold text-success-600 dark:text-success-400 mt-1">
                {{ vacancy.avg_score || '—' }}
              </p>
            </div>
          </AppCard>
        </div>
      </div>

      <!-- Tabs -->
      <AppTabs v-model="activeTab" :tabs="tabs" variant="underline">
        <!-- Pipeline Tab -->
        <template #panel-pipeline>
          <div class="bg-surface-50 dark:bg-surface-900 rounded-lg p-8 text-center">
            <BriefcaseIcon class="h-16 w-16 mx-auto text-surface-400 dark:text-surface-500 mb-4" />
            <h3 class="text-lg font-semibold text-surface-900 dark:text-surface-100 mb-2">
              Kanban Pipeline
            </h3>
            <p class="text-surface-600 dark:text-surface-400 mb-4">
              Kandidatlarni drag-and-drop bilan boshqaring
            </p>
            <p class="text-sm text-surface-500 dark:text-surface-400">
              Bu funksiya tez orada qo'shiladi
            </p>
          </div>
        </template>

        <!-- Questionnaire Tab -->
        <template #panel-questionnaire>
          <div v-if="vacancy.questionnaire">
            <AppCard>
              <div class="flex items-center justify-between mb-6">
                <div>
                  <h3 class="text-lg font-semibold text-surface-900 dark:text-surface-100">
                    {{ vacancy.questionnaire.title }}
                  </h3>
                  <p class="text-sm text-surface-600 dark:text-surface-400 mt-1">
                    {{ vacancy.questionnaire.questions_count }} ta savol •
                    Maksimal: {{ vacancy.questionnaire.total_score }} ball
                  </p>
                </div>
                <AppButton variant="outline">
                  <template #icon-left>
                    <PencilIcon class="h-5 w-5" />
                  </template>
                  Tahrirlash
                </AppButton>
              </div>

              <div class="space-y-4">
                <div
                  v-for="(question, index) in vacancy.questionnaire.questions"
                  :key="question.id"
                  class="p-4 bg-surface-50 dark:bg-surface-900 rounded-lg"
                >
                  <div class="flex items-start justify-between mb-2">
                    <div class="flex-1">
                      <div class="flex items-center gap-2 mb-1">
                        <span class="text-sm font-medium text-surface-500 dark:text-surface-400">
                          Savol {{ index + 1 }}
                        </span>
                        <AppBadge size="sm" variant="primary">
                          {{ getQuestionTypeLabel(question.type) }}
                        </AppBadge>
                        <AppBadge v-if="question.is_knockout" size="sm" variant="danger">
                          Knockout
                        </AppBadge>
                      </div>
                      <p class="font-medium text-surface-900 dark:text-surface-100">
                        {{ question.question }}
                      </p>
                    </div>
                    <div class="text-right ml-4">
                      <p class="text-sm text-surface-600 dark:text-surface-400">Og'irlik</p>
                      <p class="font-semibold text-surface-900 dark:text-surface-100">
                        {{ question.weight }}
                      </p>
                    </div>
                  </div>

                  <!-- Options for choice questions -->
                  <div v-if="['single_choice', 'multi_select'].includes(question.type)" class="mt-3 pl-4 space-y-1">
                    <div
                      v-for="option in question.options"
                      :key="option.id"
                      class="flex items-center gap-2 text-sm"
                    >
                      <div :class="[
                        'w-1.5 h-1.5 rounded-full',
                        option.is_correct ? 'bg-success-500' : 'bg-surface-400',
                      ]" />
                      <span :class="[
                        option.is_correct
                          ? 'text-success-700 dark:text-success-400 font-medium'
                          : 'text-surface-700 dark:text-surface-300',
                      ]">
                        {{ option.text }}
                        <span v-if="option.is_correct" class="text-xs">({{ option.score }} ball)</span>
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </AppCard>
          </div>
          <div v-else class="bg-surface-50 dark:bg-surface-900 rounded-lg p-8 text-center">
            <ClipboardDocumentListIcon class="h-16 w-16 mx-auto text-surface-400 dark:text-surface-500 mb-4" />
            <h3 class="text-lg font-semibold text-surface-900 dark:text-surface-100 mb-2">
              Savolnoma yo'q
            </h3>
            <p class="text-surface-600 dark:text-surface-400 mb-4">
              Ushbu vakansiya uchun hali savolnoma yaratilmagan
            </p>
            <AppButton variant="primary">
              Savolnoma yaratish
            </AppButton>
          </div>
        </template>

        <!-- Analytics Tab -->
        <template #panel-analytics>
          <div class="space-y-6">
            <!-- Overview Cards -->
            <div class="grid grid-cols-3 gap-4">
              <AppCard>
                <div>
                  <p class="text-sm text-surface-600 dark:text-surface-400">Jami arizalar</p>
                  <p class="text-3xl font-bold text-surface-900 dark:text-surface-100 mt-2">
                    {{ vacancy.applications_count }}
                  </p>
                  <div class="flex items-center gap-1 mt-2 text-success-600 dark:text-success-400">
                    <ArrowTrendingUpIcon class="h-4 w-4" />
                    <span class="text-sm font-medium">+12%</span>
                    <span class="text-xs text-surface-500">oxirgi hafta</span>
                  </div>
                </div>
              </AppCard>

              <AppCard>
                <div>
                  <p class="text-sm text-surface-600 dark:text-surface-400">Malakali kandidatlar</p>
                  <p class="text-3xl font-bold text-surface-900 dark:text-surface-100 mt-2">
                    {{ vacancy.qualified_count || 0 }}
                  </p>
                  <div class="mt-2">
                    <AppProgressBar
                      :value="(vacancy.qualified_count || 0)"
                      :max="vacancy.applications_count"
                      :show-percentage="false"
                      size="sm"
                      color="success"
                    />
                  </div>
                </div>
              </AppCard>

              <AppCard>
                <div>
                  <p class="text-sm text-surface-600 dark:text-surface-400">Konversiya</p>
                  <p class="text-3xl font-bold text-surface-900 dark:text-surface-100 mt-2">
                    24%
                  </p>
                  <p class="text-xs text-surface-500 dark:text-surface-400 mt-2">
                    Ko'rilgan → Ariza
                  </p>
                </div>
              </AppCard>
            </div>

            <!-- Charts Placeholder -->
            <div class="grid grid-cols-2 gap-6">
              <AppCard>
                <template #header>
                  <h3 class="text-sm font-semibold text-surface-900 dark:text-surface-100">
                    Arizalar dinamikasi
                  </h3>
                </template>
                <div class="h-64 flex items-center justify-center bg-surface-50 dark:bg-surface-900 rounded-lg">
                  <p class="text-surface-500 dark:text-surface-400">Chart: Arizalar dinamikasi</p>
                </div>
              </AppCard>

              <AppCard>
                <template #header>
                  <h3 class="text-sm font-semibold text-surface-900 dark:text-surface-100">
                    Ball taqsimoti
                  </h3>
                </template>
                <div class="h-64 flex items-center justify-center bg-surface-50 dark:bg-surface-900 rounded-lg">
                  <p class="text-surface-500 dark:text-surface-400">Chart: Ball taqsimoti</p>
                </div>
              </AppCard>
            </div>

            <!-- Source Breakdown -->
            <AppCard>
              <template #header>
                <h3 class="text-sm font-semibold text-surface-900 dark:text-surface-100">
                  Manba bo'yicha
                </h3>
              </template>
              <div class="space-y-3">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-3 flex-1">
                    <div class="w-10 h-10 rounded-lg bg-brand-100 dark:bg-brand-900/20 flex items-center justify-center">
                      <span class="text-lg">📱</span>
                    </div>
                    <div class="flex-1">
                      <p class="text-sm font-medium text-surface-900 dark:text-surface-100">Telegram Bot</p>
                      <AppProgressBar :value="85" :show-percentage="false" size="xs" />
                    </div>
                  </div>
                  <div class="text-right ml-4">
                    <p class="text-sm font-semibold text-surface-900 dark:text-surface-100">38</p>
                    <p class="text-xs text-surface-500 dark:text-surface-400">85%</p>
                  </div>
                </div>

                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-3 flex-1">
                    <div class="w-10 h-10 rounded-lg bg-success-100 dark:bg-success-900/20 flex items-center justify-center">
                      <span class="text-lg">🔗</span>
                    </div>
                    <div class="flex-1">
                      <p class="text-sm font-medium text-surface-900 dark:text-surface-100">Direct Link</p>
                      <AppProgressBar :value="15" :show-percentage="false" size="xs" color="success" />
                    </div>
                  </div>
                  <div class="text-right ml-4">
                    <p class="text-sm font-semibold text-surface-900 dark:text-surface-100">7</p>
                    <p class="text-xs text-surface-500 dark:text-surface-400">15%</p>
                  </div>
                </div>
              </div>
            </AppCard>
          </div>
        </template>
      </AppTabs>
    </div>

    <!-- Not Found -->
    <div v-else class="flex items-center justify-center py-20">
      <AppEmptyState
        title="Vakansiya topilmadi"
        description="Bunday vakansiya mavjud emas yoki o'chirilgan"
        action="Vakansiyalar ro'yxatiga qaytish"
        @action="$router.push('/dashboard/vacancies')"
      />
    </div>

    <!-- Close Confirmation Dialog -->
    <AppConfirmDialog
      :open="showCloseDialog"
      type="warning"
      title="Vakansiyani yopish"
      message="Ushbu vakansiyani yopmoqchimisiz? Yopilgan vakansiyalarga yangi arizalar kelmaydi."
      confirm-text="Yopish"
      cancel-text="Bekor qilish"
      @confirm="confirmClose"
      @cancel="showCloseDialog = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { toast } from 'vue-sonner';
import {
  ArrowLeftIcon,
  PencilIcon,
  BriefcaseIcon,
  ClipboardDocumentListIcon,
  ArrowTrendingUpIcon,
} from '@heroicons/vue/24/outline';
import AppCard from '../../components/ui/AppCard.vue';
import AppButton from '../../components/ui/AppButton.vue';
import AppBadge from '../../components/ui/AppBadge.vue';
import AppTabs from '../../components/ui/AppTabs.vue';
import AppDropdown from '../../components/ui/AppDropdown.vue';
import AppLoadingSpinner from '../../components/ui/AppLoadingSpinner.vue';
import AppEmptyState from '../../components/ui/AppEmptyState.vue';
import AppConfirmDialog from '../../components/ui/AppConfirmDialog.vue';
import AppProgressBar from '../../components/ui/AppProgressBar.vue';

const route = useRoute();
const router = useRouter();

const vacancy = ref(null);
const loading = ref(true);
const activeTab = ref('pipeline');
const showCloseDialog = ref(false);

const tabs = [
  { key: 'pipeline', label: 'Pipeline' },
  { key: 'questionnaire', label: 'Savolnoma' },
  { key: 'analytics', label: 'Analitika' },
];

const actionMenuItems = [
  {
    label: 'Telegram kanalda e\'lon qilish',
    icon: null,
    onClick: () => toast.info('Telegram e\'lon funksiyasi'),
  },
  {
    label: 'Havola nusxalash',
    icon: null,
    onClick: () => {
      navigator.clipboard.writeText(window.location.href);
      toast.success('Havola nusxalandi');
    },
  },
  {
    label: 'Vakansiyani yopish',
    icon: null,
    danger: true,
    onClick: () => showCloseDialog.value = true,
  },
];

onMounted(async () => {
  await loadVacancy();
});

async function loadVacancy() {
  loading.value = true;

  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 800));

    // Mock data
    vacancy.value = {
      id: route.params.id,
      title: 'Senior PHP Developer',
      company_name: 'TechCorp',
      status: 'active',
      created_at: '2024-02-20',
      applications_count: 45,
      new_applications_count: 8,
      views_count: 234,
      avg_score: 7.8,
      qualified_count: 18,
      questionnaire: {
        title: 'PHP Developer Savolnomasi',
        questions_count: 8,
        total_score: 100,
        questions: [
          {
            id: 1,
            type: 'single_choice',
            question: 'Qancha yillik PHP tajribangiz bor?',
            weight: 15,
            is_knockout: true,
            options: [
              { id: 1, text: '0-1 yil', is_correct: false, score: 0 },
              { id: 2, text: '1-3 yil', is_correct: false, score: 5 },
              { id: 3, text: '3-5 yil', is_correct: true, score: 10 },
              { id: 4, text: '5+ yil', is_correct: true, score: 15 },
            ],
          },
          {
            id: 2,
            type: 'multi_select',
            question: 'Qaysi frameworklarni bilasiz?',
            weight: 20,
            is_knockout: false,
            options: [
              { id: 5, text: 'Laravel', is_correct: true, score: 10 },
              { id: 6, text: 'Symfony', is_correct: true, score: 5 },
              { id: 7, text: 'CodeIgniter', is_correct: false, score: 0 },
              { id: 8, text: 'Yii', is_correct: false, score: 0 },
            ],
          },
        ],
      },
    };
  } catch (error) {
    vacancy.value = null;
  } finally {
    loading.value = false;
  }
}

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

function getQuestionTypeLabel(type) {
  const labels = {
    single_choice: 'Bir tanlov',
    multi_select: 'Ko\'p tanlov',
    number_range: 'Raqam',
    open_text: 'Ochiq javob',
    knockout: 'Knockout',
    file_upload: 'Fayl yuklash',
  };
  return labels[type] || type;
}

function formatDate(date) {
  return new Date(date).toLocaleDateString('uz-UZ', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });
}

async function confirmClose() {
  try {
    await new Promise(resolve => setTimeout(resolve, 1000));
    vacancy.value.status = 'closed';
    showCloseDialog.value = false;
    toast.success('Vakansiya yopildi');
  } catch (error) {
    toast.error('Xatolik yuz berdi');
  }
}
</script>
