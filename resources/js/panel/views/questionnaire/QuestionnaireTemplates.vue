<template>
  <div>
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">Savolnoma shablonlari</h1>
        <p class="text-surface-600 dark:text-surface-400 mt-1">
          Saqlangan shablonlarni boshqaring va qayta ishlating
        </p>
      </div>
      <AppButton variant="primary" @click="$router.push({ name: 'template-create' })">
        <PlusIcon class="w-4 h-4" />
        Shablon yaratish
      </AppButton>
    </div>

    <!-- Stats -->
    <div v-if="!loading && templates.length > 0" class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
      <div class="bg-surface-0 dark:bg-surface-900 border border-surface-200 dark:border-surface-800 rounded-xl px-4 py-3">
        <p class="text-2xl font-bold text-surface-900 dark:text-surface-100">{{ templates.length }}</p>
        <p class="text-xs text-surface-500 dark:text-surface-400">Jami shablonlar</p>
      </div>
      <div class="bg-surface-0 dark:bg-surface-900 border border-surface-200 dark:border-surface-800 rounded-xl px-4 py-3">
        <p class="text-2xl font-bold text-brand-600 dark:text-brand-400">{{ totalQuestions }}</p>
        <p class="text-xs text-surface-500 dark:text-surface-400">Jami savollar</p>
      </div>
      <div class="bg-surface-0 dark:bg-surface-900 border border-surface-200 dark:border-surface-800 rounded-xl px-4 py-3">
        <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ totalUsage }}</p>
        <p class="text-xs text-surface-500 dark:text-surface-400">Ishlatilgan</p>
      </div>
      <div class="bg-surface-0 dark:bg-surface-900 border border-surface-200 dark:border-surface-800 rounded-xl px-4 py-3">
        <p class="text-2xl font-bold text-surface-900 dark:text-surface-100">{{ categories.length }}</p>
        <p class="text-xs text-surface-500 dark:text-surface-400">Kategoriyalar</p>
      </div>
    </div>

    <!-- Search & Filter -->
    <div v-if="!loading && templates.length > 0" class="flex flex-col sm:flex-row gap-3 mb-5">
      <div class="relative flex-1">
        <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-surface-400" />
        <input
          v-model="searchQuery"
          type="text"
          class="w-full pl-9 pr-3 py-2 rounded-lg border border-surface-300 dark:border-surface-600 bg-surface-0 dark:bg-surface-800 text-surface-900 dark:text-surface-100 text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent placeholder:text-surface-400"
          placeholder="Shablon qidirish..."
        />
      </div>
      <select
        v-if="categories.length > 0"
        v-model="filterCategory"
        class="px-3 py-2 rounded-lg border border-surface-300 dark:border-surface-600 bg-surface-0 dark:bg-surface-800 text-surface-900 dark:text-surface-100 text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent"
      >
        <option value="">Barcha kategoriyalar</option>
        <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
      </select>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex items-center justify-center py-20">
      <AppLoadingSpinner size="lg" text="Shablonlar yuklanmoqda..." />
    </div>

    <!-- Templates Grid -->
    <div v-else-if="filteredTemplates.length > 0" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
      <div
        v-for="template in filteredTemplates"
        :key="template.id"
        class="group bg-surface-0 dark:bg-surface-900 border border-surface-200 dark:border-surface-800 rounded-xl overflow-hidden hover:border-brand-300 dark:hover:border-brand-700 hover:shadow-lg transition-all"
      >
        <!-- Card header with color stripe -->
        <div class="h-1.5" :class="getCategoryColor(template.category)" />

        <div class="p-5">
          <!-- Top row -->
          <div class="flex items-start justify-between mb-3">
            <div class="flex-1 min-w-0">
              <h3 class="text-base font-semibold text-surface-900 dark:text-surface-100 truncate mb-1.5">
                {{ template.name }}
              </h3>
              <div class="flex items-center gap-2 flex-wrap">
                <AppBadge v-if="template.category" size="sm" variant="primary">
                  {{ template.category }}
                </AppBadge>
                <AppBadge v-if="template.is_public" size="sm" variant="info">
                  Umumiy
                </AppBadge>
              </div>
            </div>
            <AppDropdown :items="getTemplateActions(template)">
              <template #trigger>
                <button class="p-1.5 rounded-md hover:bg-surface-100 dark:hover:bg-surface-800 transition-colors opacity-0 group-hover:opacity-100">
                  <EllipsisVerticalIcon class="h-5 w-5 text-surface-500" />
                </button>
              </template>
            </AppDropdown>
          </div>

          <!-- Stats row -->
          <div class="flex items-center gap-4 text-sm text-surface-500 dark:text-surface-400 mb-4">
            <span class="flex items-center gap-1.5">
              <ClipboardDocumentListIcon class="h-4 w-4" />
              {{ template.questions_data?.length || 0 }} ta savol
            </span>
            <span class="flex items-center gap-1.5">
              <ArrowPathIcon class="h-4 w-4" />
              {{ template.usage_count || 0 }}x ishlatilgan
            </span>
          </div>

          <!-- Questions preview -->
          <div class="bg-surface-50 dark:bg-surface-800/50 rounded-lg p-3 mb-4">
            <div v-if="template.questions_data?.length" class="space-y-2">
              <div
                v-for="(q, i) in template.questions_data.slice(0, 3)"
                :key="i"
                class="flex items-start gap-2 text-sm"
              >
                <span class="text-surface-400 text-xs mt-0.5 w-5 text-right shrink-0">{{ i + 1 }}.</span>
                <div class="flex-1 min-w-0">
                  <span class="text-surface-700 dark:text-surface-300 line-clamp-1">{{ q.text_uz }}</span>
                  <span class="text-xs text-surface-400 dark:text-surface-500">{{ getTypeLabel(q.type) }}</span>
                </div>
              </div>
              <p v-if="template.questions_data.length > 3" class="text-xs text-brand-600 dark:text-brand-400 pl-7 font-medium">
                +{{ template.questions_data.length - 3 }} ta ko'proq savol
              </p>
            </div>
            <p v-else class="text-sm text-surface-400 dark:text-surface-500 text-center py-2">
              Savollar yo'q
            </p>
          </div>

          <!-- Actions row -->
          <div class="flex items-center justify-between pt-3 border-t border-surface-100 dark:border-surface-800">
            <span class="text-xs text-surface-500 dark:text-surface-400">
              {{ formatDate(template.created_at) }}
            </span>
            <div class="flex items-center gap-2">
              <AppButton
                variant="outline"
                size="sm"
                @click="$router.push({ name: 'template-edit', params: { templateId: template.id } })"
              >
                <PencilIcon class="w-3.5 h-3.5" />
                Tahrirlash
              </AppButton>
              <AppButton
                variant="primary"
                size="sm"
                @click="openApplyModal(template)"
              >
                Qo'llash
              </AppButton>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- No results after filter -->
    <div v-else-if="templates.length > 0 && filteredTemplates.length === 0" class="text-center py-16">
      <MagnifyingGlassIcon class="h-12 w-12 mx-auto text-surface-400 dark:text-surface-500 mb-3" />
      <h3 class="text-base font-semibold text-surface-900 dark:text-surface-100 mb-1">
        Natija topilmadi
      </h3>
      <p class="text-sm text-surface-600 dark:text-surface-400">
        Qidiruv so'zini o'zgartirib ko'ring
      </p>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-20">
      <div class="w-20 h-20 mx-auto mb-5 rounded-2xl bg-brand-50 dark:bg-brand-950/30 flex items-center justify-center">
        <DocumentDuplicateIcon class="h-10 w-10 text-brand-500" />
      </div>
      <h3 class="text-lg font-semibold text-surface-900 dark:text-surface-100 mb-2">
        Shablonlar topilmadi
      </h3>
      <p class="text-surface-600 dark:text-surface-400 mb-6 max-w-sm mx-auto">
        Savolnoma shablonlarini yaratib, ularni turli vakansiyalarga tezkor qo'llang
      </p>
      <div class="flex items-center justify-center gap-3">
        <AppButton variant="primary" @click="$router.push({ name: 'template-create' })">
          <PlusIcon class="w-4 h-4" />
          Shablon yaratish
        </AppButton>
        <AppButton variant="outline" @click="$router.push({ name: 'questionnaires' })">
          Savolnomalarga o'tish
        </AppButton>
      </div>
    </div>

    <!-- Apply Template Modal -->
    <AppModal
      :show="showApplyModal"
      size="md"
      title="Shablonni vakansiyaga qo'llash"
      @close="showApplyModal = false"
    >
      <div v-if="selectedTemplate" class="space-y-4">
        <div class="p-3 rounded-lg bg-surface-50 dark:bg-surface-800">
          <p class="text-sm font-medium text-surface-900 dark:text-surface-100">
            {{ selectedTemplate.name }}
          </p>
          <p class="text-xs text-surface-500 dark:text-surface-400 mt-0.5">
            {{ selectedTemplate.questions_data?.length || 0 }} ta savol
          </p>
        </div>

        <div>
          <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-2">
            Vakansiyani tanlang
          </label>

          <div v-if="loadingVacancies" class="flex items-center justify-center py-6">
            <AppLoadingSpinner size="sm" text="Vakansiyalar yuklanmoqda..." />
          </div>

          <div v-else-if="availableVacancies.length > 0" class="space-y-2 max-h-60 overflow-y-auto">
            <button
              v-for="vacancy in availableVacancies"
              :key="vacancy.id"
              class="w-full text-left p-3 rounded-lg border transition-all"
              :class="selectedVacancyId === vacancy.id
                ? 'border-brand-500 bg-brand-50 dark:bg-brand-950/30'
                : 'border-surface-200 dark:border-surface-700 hover:border-surface-300 dark:hover:border-surface-600'"
              @click="selectedVacancyId = vacancy.id"
            >
              <p class="text-sm font-medium text-surface-900 dark:text-surface-100">
                {{ vacancy.title_uz || vacancy.title_ru }}
              </p>
              <div class="flex items-center gap-2 mt-0.5">
                <AppBadge :variant="getStatusVariant(vacancy.status)" size="sm">
                  {{ getStatusLabel(vacancy.status) }}
                </AppBadge>
                <span v-if="vacancy.has_questionnaire" class="text-xs text-warning-600 dark:text-warning-400">
                  Savolnoma mavjud
                </span>
              </div>
            </button>
          </div>

          <p v-else class="text-sm text-surface-500 dark:text-surface-400 text-center py-4">
            Savolnomasi yo'q vakansiya topilmadi
          </p>
        </div>
      </div>

      <template #footer>
        <div class="flex justify-end gap-3">
          <AppButton variant="outline" @click="showApplyModal = false">
            Bekor qilish
          </AppButton>
          <AppButton
            variant="primary"
            :loading="applying"
            :disabled="!selectedVacancyId || applying"
            @click="applyTemplate"
          >
            Qo'llash
          </AppButton>
        </div>
      </template>
    </AppModal>

    <!-- Delete Confirmation -->
    <AppConfirmDialog
      :open="showDeleteDialog"
      type="danger"
      title="Shablonni o'chirish"
      message="Ushbu shablonni o'chirishni tasdiqlaysizmi? Bu amalni qaytarib bo'lmaydi."
      confirm-text="O'chirish"
      cancel-text="Bekor qilish"
      :loading="deleteLoading"
      @confirm="confirmDelete"
      @cancel="showDeleteDialog = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { toast } from 'vue-sonner';
import {
  ClipboardDocumentListIcon,
  DocumentDuplicateIcon,
  EllipsisVerticalIcon,
  ArrowPathIcon,
  PlusIcon,
  PencilIcon,
  MagnifyingGlassIcon,
} from '@heroicons/vue/24/outline';
import AppButton from '../../components/ui/AppButton.vue';
import AppBadge from '../../components/ui/AppBadge.vue';
import AppDropdown from '../../components/ui/AppDropdown.vue';
import AppModal from '../../components/ui/AppModal.vue';
import AppLoadingSpinner from '../../components/ui/AppLoadingSpinner.vue';
import AppConfirmDialog from '../../components/ui/AppConfirmDialog.vue';

const router = useRouter();

const templates = ref([]);
const loading = ref(true);
const searchQuery = ref('');
const filterCategory = ref('');

// Delete
const showDeleteDialog = ref(false);
const deleteLoading = ref(false);
const templateToDelete = ref(null);

// Apply
const showApplyModal = ref(false);
const selectedTemplate = ref(null);
const selectedVacancyId = ref(null);
const availableVacancies = ref([]);
const loadingVacancies = ref(false);
const applying = ref(false);

// Computed
const totalQuestions = computed(() =>
  templates.value.reduce((sum, t) => sum + (t.questions_data?.length || 0), 0)
);

const totalUsage = computed(() =>
  templates.value.reduce((sum, t) => sum + (t.usage_count || 0), 0)
);

const categories = computed(() => {
  const cats = new Set();
  templates.value.forEach(t => {
    if (t.category) cats.add(t.category);
  });
  return Array.from(cats).sort();
});

const filteredTemplates = computed(() => {
  let result = templates.value;

  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase();
    result = result.filter(t =>
      t.name.toLowerCase().includes(q) ||
      (t.category && t.category.toLowerCase().includes(q))
    );
  }

  if (filterCategory.value) {
    result = result.filter(t => t.category === filterCategory.value);
  }

  return result;
});

onMounted(() => {
  fetchTemplates();
});

async function fetchTemplates() {
  loading.value = true;
  try {
    const { data } = await axios.get('/api/recruiter/templates', {
      params: { per_page: 50 },
    });
    templates.value = data.data || [];
  } catch {
    toast.error('Shablonlarni yuklashda xatolik');
  } finally {
    loading.value = false;
  }
}

// ---- Apply ----
async function fetchVacancies() {
  loadingVacancies.value = true;
  try {
    const { data } = await axios.get('/api/recruiter/vacancies', {
      params: { per_page: 100 },
    });
    availableVacancies.value = data.vacancies?.data || [];
  } catch {
    availableVacancies.value = [];
  } finally {
    loadingVacancies.value = false;
  }
}

function openApplyModal(template) {
  selectedTemplate.value = template;
  selectedVacancyId.value = null;
  showApplyModal.value = true;
  fetchVacancies();
}

async function applyTemplate() {
  if (!selectedTemplate.value || !selectedVacancyId.value) return;

  applying.value = true;
  try {
    await axios.post(`/api/recruiter/templates/${selectedTemplate.value.id}/apply`, {
      vacancy_id: selectedVacancyId.value,
    });

    toast.success(`"${selectedTemplate.value.name}" shabloni qo'llanildi`);
    showApplyModal.value = false;
    router.push({ name: 'questionnaire-builder', params: { vacancyId: selectedVacancyId.value } });
  } catch (error) {
    const msg = error.response?.data?.message || 'Shablonni qo\'llashda xatolik';
    toast.error(msg);
  } finally {
    applying.value = false;
  }
}

// ---- Actions & Delete ----
function getTemplateActions(template) {
  return [
    {
      label: 'Tahrirlash',
      onClick: () => router.push({ name: 'template-edit', params: { templateId: template.id } }),
    },
    {
      label: 'Vakansiyaga qo\'llash',
      onClick: () => openApplyModal(template),
    },
    {
      label: 'O\'chirish',
      danger: true,
      onClick: () => {
        templateToDelete.value = template;
        showDeleteDialog.value = true;
      },
    },
  ];
}

async function confirmDelete() {
  if (!templateToDelete.value) return;
  deleteLoading.value = true;
  try {
    await axios.delete(`/api/recruiter/templates/${templateToDelete.value.id}`);
    templates.value = templates.value.filter(t => t.id !== templateToDelete.value.id);
    toast.success('Shablon o\'chirildi');
  } catch {
    toast.error('Shablonni o\'chirishda xatolik');
  } finally {
    deleteLoading.value = false;
    showDeleteDialog.value = false;
    templateToDelete.value = null;
  }
}

// ---- Helpers ----
function getStatusVariant(status) {
  const variants = { active: 'success', pending: 'warning', draft: 'default', closed: 'default' };
  return variants[status] || 'default';
}

function getStatusLabel(status) {
  const labels = { active: 'Faol', pending: 'Kutilmoqda', draft: 'Qoralama', closed: 'Yopilgan' };
  return labels[status] || status;
}

function getTypeLabel(type) {
  const labels = {
    text: 'Matn',
    single_choice: 'Bitta tanlov',
    multiple_choice: 'Ko\'p tanlov',
    rating: 'Baho',
    yes_no: 'Ha/Yo\'q',
  };
  return labels[type] || type;
}

function getCategoryColor(category) {
  if (!category) return 'bg-surface-300 dark:bg-surface-600';
  const colors = [
    'bg-brand-500',
    'bg-green-500',
    'bg-amber-500',
    'bg-purple-500',
    'bg-rose-500',
    'bg-cyan-500',
  ];
  let hash = 0;
  for (let i = 0; i < category.length; i++) {
    hash = category.charCodeAt(i) + ((hash << 5) - hash);
  }
  return colors[Math.abs(hash) % colors.length];
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
