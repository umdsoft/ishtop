<template>
  <div>
    <!-- Loading -->
    <div v-if="loading" class="flex items-center justify-center py-20">
      <AppLoadingSpinner size="lg" text="Yuklanmoqda..." />
    </div>

    <!-- Vacancy not found -->
    <div v-else-if="!vacancy" class="text-center py-20">
      <ClipboardDocumentListIcon class="h-16 w-16 mx-auto text-surface-400 dark:text-surface-500 mb-4" />
      <h3 class="text-lg font-semibold text-surface-900 dark:text-surface-100 mb-2">
        Vakansiya topilmadi
      </h3>
      <p class="text-surface-600 dark:text-surface-400 mb-4">
        Bunday vakansiya mavjud emas
      </p>
      <AppButton variant="primary" @click="$router.push({ name: 'questionnaires' })">
        Orqaga qaytish
      </AppButton>
    </div>

    <!-- Main Content -->
    <div v-else>
      <!-- Header -->
      <div class="mb-6">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center gap-3">
            <button
              class="p-2 rounded-lg hover:bg-surface-100 dark:hover:bg-surface-800 transition-colors"
              @click="$router.push({ name: 'questionnaires' })"
            >
              <ArrowLeftIcon class="h-5 w-5 text-surface-600 dark:text-surface-400" />
            </button>
            <div>
              <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">
                {{ questionnaire ? 'Savolnomani tahrirlash' : 'Savolnoma yaratish' }}
              </h1>
              <p class="text-surface-600 dark:text-surface-400 mt-0.5">
                {{ vacancy.title_uz || vacancy.title_ru }}
              </p>
            </div>
          </div>
          <div v-if="questionnaire" class="flex items-center gap-3">
            <AppButton v-if="questions.length > 0" variant="outline" @click="showSaveTemplateModal = true">
              <template #icon-left>
                <DocumentDuplicateIcon class="h-5 w-5" />
              </template>
              Shablon sifatida saqlash
            </AppButton>
            <AppButton variant="outline" @click="showPreview = true">
              <template #icon-left>
                <EyeIcon class="h-5 w-5" />
              </template>
              Ko'rish
            </AppButton>
          </div>
        </div>

        <!-- Stats -->
        <div v-if="questionnaire" class="grid grid-cols-3 gap-4">
          <AppCard>
            <div class="text-center">
              <p class="text-sm text-surface-600 dark:text-surface-400">Savollar</p>
              <p class="text-2xl font-bold text-surface-900 dark:text-surface-100 mt-1">
                {{ questions.length }}
              </p>
            </div>
          </AppCard>
          <AppCard>
            <div class="text-center">
              <p class="text-sm text-surface-600 dark:text-surface-400">Jami og'irlik</p>
              <p class="text-2xl font-bold text-surface-900 dark:text-surface-100 mt-1">
                {{ totalWeight }}
              </p>
            </div>
          </AppCard>
          <AppCard>
            <div class="text-center">
              <p class="text-sm text-surface-600 dark:text-surface-400">Knockout</p>
              <p class="text-2xl font-bold text-surface-900 dark:text-surface-100 mt-1">
                {{ knockoutCount }}
              </p>
            </div>
          </AppCard>
        </div>
      </div>

      <!-- Create Questionnaire Form (if none exists) -->
      <div v-if="!questionnaire" class="space-y-6">
        <!-- Templates Section -->
        <AppCard v-if="templates.length > 0">
          <template #header>
            <h2 class="text-lg font-semibold text-surface-900 dark:text-surface-100">
              Shablondan yaratish
            </h2>
          </template>

          <p class="text-sm text-surface-600 dark:text-surface-400 mb-4">
            Tayyor shablonni tanlang va tezda savolnoma yarating
          </p>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <button
              v-for="tpl in templates"
              :key="tpl.id"
              class="text-left p-4 rounded-lg border border-surface-200 dark:border-surface-700 hover:border-brand-400 dark:hover:border-brand-600 hover:bg-surface-50 dark:hover:bg-surface-800 transition-all"
              :disabled="applyingTemplate"
              @click="applyTemplate(tpl)"
            >
              <div class="flex items-start justify-between mb-1">
                <h4 class="text-sm font-semibold text-surface-900 dark:text-surface-100">
                  {{ tpl.name }}
                </h4>
                <AppBadge v-if="tpl.is_public" size="sm" variant="info">Umumiy</AppBadge>
              </div>
              <div class="flex items-center gap-3 text-xs text-surface-500 dark:text-surface-400">
                <span>{{ tpl.questions_data?.length || 0 }} ta savol</span>
                <span v-if="tpl.category">{{ tpl.category }}</span>
                <span>{{ tpl.usage_count || 0 }}x ishlatilgan</span>
              </div>
            </button>
          </div>
        </AppCard>

        <!-- Manual Create -->
        <AppCard>
          <template #header>
            <h2 class="text-lg font-semibold text-surface-900 dark:text-surface-100">
              {{ templates.length > 0 ? 'Yoki noldan yarating' : 'Savolnoma sozlamalari' }}
            </h2>
          </template>

          <div class="space-y-4 max-w-lg">
            <AppInput
              v-model="createForm.title"
              label="Sarlavha"
              :placeholder="`${vacancy.title_uz || vacancy.title_ru} — Savolnoma`"
              required
            />
            <div class="grid grid-cols-2 gap-4">
              <AppInput
                v-model="createForm.time_limit_minutes"
                type="number"
                label="Vaqt chegarasi (daqiqa)"
                placeholder="30"
                hint="1 dan 120 gacha"
              />
              <AppInput
                v-model="createForm.passing_score"
                type="number"
                label="O'tish balli"
                placeholder="60"
                hint="0 dan 100 gacha"
              />
            </div>
            <AppInput
              v-model="createForm.auto_reject_below"
              type="number"
              label="Avtomatik rad etish chegarasi"
              placeholder="30"
              hint="Bu balldan past natijali nomzodlar avtomatik rad etiladi"
            />
          </div>

          <template #footer>
            <AppButton variant="primary" :loading="creating" @click="createQuestionnaire">
              Savolnoma yaratish
            </AppButton>
          </template>
        </AppCard>
      </div>

      <!-- Questions Editor -->
      <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Questions List -->
        <div class="lg:col-span-2">
          <AppCard>
            <template #header>
              <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-surface-900 dark:text-surface-100">
                  Savollar
                </h2>
                <AppButton variant="outline" size="sm" @click="openAddQuestion(null)">
                  <template #icon-left>
                    <PlusIcon class="h-4 w-4" />
                  </template>
                  Savol qo'shish
                </AppButton>
              </div>
            </template>

            <div v-if="questions.length > 0" class="space-y-4">
              <draggable
                v-model="questions"
                item-key="id"
                handle=".drag-handle"
                :animation="200"
                ghost-class="opacity-50"
                @end="handleReorder"
              >
                <template #item="{ element, index }">
                  <QuestionCardItem
                    :question="element"
                    :index="index"
                    :question-types="questionTypes"
                    show-weight
                  >
                    <template #actions>
                      <div class="flex flex-col gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button
                          class="p-1.5 rounded-lg hover:bg-brand-50 dark:hover:bg-brand-950/30 transition-colors"
                          title="Tahrirlash"
                          @click="openEditQuestion(element)"
                        >
                          <PencilIcon class="h-4 w-4 text-surface-400 hover:text-brand-600 dark:hover:text-brand-400" />
                        </button>
                        <button
                          class="p-1.5 rounded-lg hover:bg-surface-100 dark:hover:bg-surface-800 transition-colors"
                          title="Nusxa olish"
                          @click="duplicateQuestion(element)"
                        >
                          <DocumentDuplicateIcon class="h-4 w-4 text-surface-400 hover:text-surface-600 dark:hover:text-surface-300" />
                        </button>
                        <button
                          class="p-1.5 rounded-lg hover:bg-danger-50 dark:hover:bg-danger-950/30 transition-colors"
                          title="O'chirish"
                          @click="confirmDeleteQuestion(element)"
                        >
                          <TrashIcon class="h-4 w-4 text-surface-400 hover:text-danger-600 dark:hover:text-danger-400" />
                        </button>
                      </div>
                    </template>
                  </QuestionCardItem>
                </template>
              </draggable>
            </div>

            <div v-else class="text-center py-16">
              <div class="w-20 h-20 mx-auto mb-5 rounded-2xl bg-surface-100 dark:bg-surface-800 flex items-center justify-center">
                <ClipboardDocumentListIcon class="h-10 w-10 text-surface-400 dark:text-surface-500" />
              </div>
              <h3 class="text-lg font-semibold text-surface-900 dark:text-surface-100 mb-2">
                Savollar hali qo'shilmagan
              </h3>
              <p class="text-surface-500 dark:text-surface-400 mb-6 max-w-sm mx-auto">
                O'ng paneldagi savol turlaridan birini tanlang yoki quyidagi tugmani bosing
              </p>
              <AppButton variant="primary" @click="openAddQuestion(null)">
                <template #icon-left>
                  <PlusIcon class="h-5 w-5" />
                </template>
                Birinchi savolni qo'shish
              </AppButton>
            </div>
          </AppCard>
        </div>

        <!-- Right Sidebar -->
        <div class="space-y-6">
          <!-- Settings -->
          <AppCard>
            <template #header>
              <h3 class="text-sm font-semibold text-surface-900 dark:text-surface-100">Sozlamalar</h3>
            </template>

            <div class="space-y-4">
              <AppInput
                v-model="settingsForm.title"
                label="Sarlavha"
                placeholder="Savolnoma sarlavhasi"
                size="sm"
              />
              <AppInput
                v-model="settingsForm.time_limit_minutes"
                type="number"
                label="Vaqt (daqiqa)"
                placeholder="30"
                size="sm"
              />
              <AppInput
                v-model="settingsForm.passing_score"
                type="number"
                label="O'tish balli"
                placeholder="60"
                size="sm"
              />
              <AppInput
                v-model="settingsForm.auto_reject_below"
                type="number"
                label="Rad etish chegarasi"
                placeholder="30"
                size="sm"
              />
              <AppButton
                variant="primary"
                size="sm"
                full-width
                :loading="savingSettings"
                @click="saveSettings"
              >
                Sozlamalarni saqlash
              </AppButton>
            </div>
          </AppCard>

          <!-- Save as Template -->
          <AppCard v-if="questions.length > 0">
            <template #header>
              <h3 class="text-sm font-semibold text-surface-900 dark:text-surface-100">Shablon</h3>
            </template>
            <p class="text-sm text-surface-600 dark:text-surface-400 mb-3">
              Bu savolnomani shablon sifatida saqlang va boshqa vakansiyalarda qayta ishlating.
            </p>
            <AppButton
              variant="outline"
              size="sm"
              full-width
              @click="showSaveTemplateModal = true"
            >
              <template #icon-left>
                <DocumentDuplicateIcon class="h-4 w-4" />
              </template>
              Shablon sifatida saqlash
            </AppButton>
          </AppCard>

          <!-- Question Types -->
          <AppCard>
            <template #header>
              <h3 class="text-sm font-semibold text-surface-900 dark:text-surface-100">Savol turlari</h3>
            </template>

            <div class="space-y-1.5">
              <button
                v-for="type in questionTypes"
                :key="type.value"
                class="w-full flex items-center gap-3 p-3 rounded-xl hover:bg-surface-50 dark:hover:bg-surface-800 border border-transparent hover:border-surface-200 dark:hover:border-surface-700 transition-all text-left group/type"
                @click="openAddQuestion(type.value)"
              >
                <div
                  class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0 transition-transform group-hover/type:scale-110"
                  :class="getTypeColorFn(type.value).number"
                >
                  <span v-html="getTypeIndicatorFn(type.value)" class="[&>svg]:w-4 [&>svg]:h-4" />
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-surface-900 dark:text-surface-100">
                    {{ type.label }}
                  </p>
                  <p class="text-xs text-surface-500 dark:text-surface-400">
                    {{ type.description }}
                  </p>
                </div>
                <PlusIcon class="h-4 w-4 text-surface-400 opacity-0 group-hover/type:opacity-100 transition-opacity flex-shrink-0" />
              </button>
            </div>
          </AppCard>

          <!-- Help -->
          <AppCard>
            <div class="flex items-start gap-3">
              <div class="flex-shrink-0">
                <div class="w-10 h-10 rounded-lg bg-info-100 dark:bg-info-900/20 flex items-center justify-center">
                  <InformationCircleIcon class="h-6 w-6 text-info-600 dark:text-info-400" />
                </div>
              </div>
              <div>
                <h4 class="text-sm font-semibold text-surface-900 dark:text-surface-100 mb-1">Maslahat</h4>
                <p class="text-sm text-surface-600 dark:text-surface-400">
                  Savollar tartibini drag-and-drop bilan o'zgartiring. Knockout savollar rad etish uchun ishlatiladi.
                </p>
              </div>
            </div>
          </AppCard>
        </div>
      </div>
    </div>

    <!-- Add/Edit Question Modal -->
    <QuestionFormModal
      :show="showQuestionForm"
      :question="editingQuestion"
      :preselected-type="preselectedType"
      :question-types="questionTypes"
      show-scoring
      @close="closeQuestionForm"
      @save="handleQuestionSave"
    />

    <!-- Delete Confirmation -->
    <AppConfirmDialog
      :open="showDeleteDialog"
      type="danger"
      title="Savolni o'chirish"
      message="Ushbu savolni o'chirishni tasdiqlaysizmi? Bu amalni qaytarib bo'lmaydi."
      confirm-text="O'chirish"
      cancel-text="Bekor qilish"
      :loading="deleting"
      @confirm="confirmDelete"
      @cancel="showDeleteDialog = false"
    />

    <!-- Save as Template Modal -->
    <AppModal
      :show="showSaveTemplateModal"
      size="md"
      title="Shablon sifatida saqlash"
      @close="showSaveTemplateModal = false"
    >
      <div class="space-y-4">
        <AppInput
          v-model="templateForm.name"
          label="Shablon nomi"
          placeholder="Masalan: IT mutaxassis uchun savolnoma"
          required
        />
        <AppInput
          v-model="templateForm.category"
          label="Kategoriya"
          placeholder="Masalan: IT, Savdo, Umumiy"
        />
      </div>
      <template #footer>
        <div class="flex justify-end gap-3">
          <AppButton variant="outline" @click="showSaveTemplateModal = false">
            Bekor qilish
          </AppButton>
          <AppButton
            variant="primary"
            :loading="savingTemplate"
            :disabled="!templateForm.name.trim()"
            @click="saveAsTemplate"
          >
            Saqlash
          </AppButton>
        </div>
      </template>
    </AppModal>

    <!-- Preview Modal -->
    <AppModal
      :show="showPreview"
      size="2xl"
      title="Savolnoma ko'rinishi"
      @close="showPreview = false"
    >
      <div v-if="questionnaire" class="space-y-4">
        <div class="flex items-center justify-between mb-4 pb-4 border-b border-surface-200 dark:border-surface-800">
          <div>
            <h3 class="text-lg font-semibold text-surface-900 dark:text-surface-100">
              {{ questionnaire.title }}
            </h3>
            <p class="text-sm text-surface-500 dark:text-surface-400 mt-1">
              {{ questions.length }} ta savol
              <span v-if="questionnaire.time_limit_minutes">
                &bull; {{ questionnaire.time_limit_minutes }} daqiqa
              </span>
            </p>
          </div>
        </div>

        <div
          v-for="(question, index) in questions"
          :key="question.id"
          class="p-4 bg-surface-50 dark:bg-surface-900 rounded-lg"
        >
          <p class="text-sm text-surface-500 dark:text-surface-400 mb-1">
            Savol {{ index + 1 }}
            <span v-if="question.is_required" class="text-danger-500">*</span>
          </p>
          <p class="font-medium text-surface-900 dark:text-surface-100 mb-3">
            {{ question.text_uz }}
          </p>

          <!-- Choice options preview -->
          <div v-if="hasTypeOptions(question.type) && question.options?.length" class="space-y-2">
            <label
              v-for="option in question.options"
              :key="option.id"
              class="flex items-center gap-3 p-2 rounded-lg hover:bg-surface-100 dark:hover:bg-surface-800 cursor-pointer"
            >
              <input
                :type="question.type === 'multi_select' ? 'checkbox' : 'radio'"
                :name="`preview-q-${question.id}`"
                disabled
                class="w-4 h-4 border-surface-300 text-brand-600"
              />
              <span class="text-sm text-surface-700 dark:text-surface-300">{{ option.label_uz }}</span>
            </label>
          </div>

          <!-- Number input preview -->
          <div v-if="question.type === 'number_range'" class="mt-2">
            <input
              type="number"
              disabled
              placeholder="Raqam kiriting"
              class="w-full px-3 py-2 text-sm rounded-lg border border-surface-300 dark:border-surface-600 bg-surface-100 dark:bg-surface-800 text-surface-500"
            />
          </div>

          <!-- Text area preview -->
          <div v-if="question.type === 'open_text'" class="mt-2">
            <textarea
              disabled
              placeholder="Javobingizni yozing..."
              rows="3"
              class="w-full px-3 py-2 text-sm rounded-lg border border-surface-300 dark:border-surface-600 bg-surface-100 dark:bg-surface-800 text-surface-500 resize-none"
            />
          </div>

          <!-- File upload preview -->
          <div v-if="question.type === 'file_upload'" class="mt-2">
            <div class="flex items-center justify-center px-4 py-6 border-2 border-dashed border-surface-300 dark:border-surface-600 rounded-lg">
              <p class="text-sm text-surface-500 dark:text-surface-400">Fayl yuklang</p>
            </div>
          </div>
        </div>

        <div v-if="questions.length === 0" class="text-center py-8 text-surface-500 dark:text-surface-400">
          Savollar hali qo'shilmagan
        </div>
      </div>
    </AppModal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { toast } from 'vue-sonner';
import draggable from 'vuedraggable';
import {
  ArrowLeftIcon,
  PlusIcon,
  EyeIcon,
  PencilIcon,
  TrashIcon,
  ClipboardDocumentListIcon,
  InformationCircleIcon,
  DocumentDuplicateIcon,
} from '@heroicons/vue/24/outline';
import { getTypeColor as getTypeColorFn, getTypeIndicator as getTypeIndicatorFn } from '../../composables/useQuestionTypes';
import AppCard from '../../components/ui/AppCard.vue';
import AppButton from '../../components/ui/AppButton.vue';
import AppBadge from '../../components/ui/AppBadge.vue';
import AppModal from '../../components/ui/AppModal.vue';
import AppInput from '../../components/ui/AppInput.vue';
import AppConfirmDialog from '../../components/ui/AppConfirmDialog.vue';
import AppLoadingSpinner from '../../components/ui/AppLoadingSpinner.vue';
import QuestionCardItem from '../../components/questionnaire/QuestionCardItem.vue';
import QuestionFormModal from '../../components/questionnaire/QuestionFormModal.vue';

const route = useRoute();
const router = useRouter();

const loading = ref(true);
const vacancy = ref(null);
const questionnaire = ref(null);
const questions = ref([]);

// Create form
const creating = ref(false);
const createForm = ref({
  title: '',
  time_limit_minutes: 30,
  passing_score: 60,
  auto_reject_below: 30,
});

// Settings form
const savingSettings = ref(false);
const settingsForm = ref({
  title: '',
  time_limit_minutes: 30,
  passing_score: 60,
  auto_reject_below: 30,
});

// Question form
const showQuestionForm = ref(false);
const editingQuestion = ref(null);
const preselectedType = ref(null);

// Delete
const showDeleteDialog = ref(false);
const deletingQuestion = ref(null);
const deleting = ref(false);

// Preview
const showPreview = ref(false);

// Save as Template
const showSaveTemplateModal = ref(false);
const savingTemplate = ref(false);
const templateForm = ref({ name: '', category: '' });

// Templates (for creating from template)
const templates = ref([]);
const loadingTemplates = ref(false);
const applyingTemplate = ref(false);

const questionTypes = [
  { value: 'single_choice', label: 'Bir tanlov', description: 'Faqat bitta javobni tanlash mumkin' },
  { value: 'multi_select', label: 'Ko\'p tanlov', description: 'Bir nechta javob tanlash mumkin' },
  { value: 'number_range', label: 'Raqam', description: 'Min va max raqam orasida' },
  { value: 'open_text', label: 'Ochiq javob', description: 'Erkin matn kiritish' },
  { value: 'knockout', label: 'Knockout', description: 'Rad etish uchun savol' },
  { value: 'file_upload', label: 'Fayl yuklash', description: 'Hujjat yoki rasm yuklash' },
];

const totalWeight = computed(() =>
  questions.value.reduce((sum, q) => sum + (q.weight || 0), 0),
);

const knockoutCount = computed(() =>
  questions.value.filter(q => q.is_knockout).length,
);

onMounted(() => {
  loadData();
});

async function loadData() {
  loading.value = true;
  const vacancyId = route.params.vacancyId;

  try {
    // Load vacancy detail
    const { data: vacancyData } = await axios.get(`/api/recruiter/vacancies/${vacancyId}`);
    vacancy.value = vacancyData.vacancy || vacancyData;

    // Load questionnaire
    try {
      const { data: qData } = await axios.get(`/api/recruiter/vacancies/${vacancyId}/questionnaire`);
      if (qData.questionnaire) {
        questionnaire.value = qData.questionnaire;
        questions.value = qData.questionnaire.questions || [];
        // Populate settings form
        settingsForm.value = {
          title: questionnaire.value.title || '',
          time_limit_minutes: questionnaire.value.time_limit_minutes || 30,
          passing_score: questionnaire.value.passing_score || 60,
          auto_reject_below: questionnaire.value.auto_reject_below || 30,
        };
      }
    } catch {
      // No questionnaire yet
      questionnaire.value = null;
    }

    // Pre-fill create form title
    createForm.value.title = `${vacancy.value.title_uz || vacancy.value.title_ru} — Savolnoma`;

    // Load templates if no questionnaire exists
    if (!questionnaire.value) {
      fetchTemplates();
    }
  } catch {
    vacancy.value = null;
  } finally {
    loading.value = false;
  }
}

async function createQuestionnaire() {
  creating.value = true;
  try {
    const { data } = await axios.post(
      `/api/recruiter/vacancies/${route.params.vacancyId}/questionnaire`,
      {
        title: createForm.value.title || `${vacancy.value.title_uz || vacancy.value.title_ru} — Savolnoma`,
        time_limit_minutes: parseInt(createForm.value.time_limit_minutes) || 30,
        passing_score: parseInt(createForm.value.passing_score) || 60,
        auto_reject_below: parseInt(createForm.value.auto_reject_below) || 30,
        is_required: true,
      },
    );
    questionnaire.value = data.questionnaire;
    questions.value = data.questionnaire.questions || [];
    settingsForm.value = {
      title: questionnaire.value.title,
      time_limit_minutes: questionnaire.value.time_limit_minutes,
      passing_score: questionnaire.value.passing_score,
      auto_reject_below: questionnaire.value.auto_reject_below,
    };
    toast.success('Savolnoma yaratildi');
  } catch (error) {
    const msg = error.response?.data?.message || 'Xatolik yuz berdi';
    toast.error(msg);
  } finally {
    creating.value = false;
  }
}

async function saveSettings() {
  savingSettings.value = true;
  try {
    const { data } = await axios.put(
      `/api/recruiter/questionnaires/${questionnaire.value.id}`,
      {
        title: settingsForm.value.title,
        time_limit_minutes: parseInt(settingsForm.value.time_limit_minutes) || null,
        passing_score: parseInt(settingsForm.value.passing_score) || null,
        auto_reject_below: parseInt(settingsForm.value.auto_reject_below) || null,
      },
    );
    questionnaire.value = { ...questionnaire.value, ...data.questionnaire };
    toast.success('Sozlamalar saqlandi');
  } catch (error) {
    toast.error(error.response?.data?.message || 'Xatolik yuz berdi');
  } finally {
    savingSettings.value = false;
  }
}

function openAddQuestion(type) {
  editingQuestion.value = null;
  preselectedType.value = type;
  showQuestionForm.value = true;
}

function openEditQuestion(question) {
  editingQuestion.value = question;
  preselectedType.value = null;
  showQuestionForm.value = true;
}

function closeQuestionForm() {
  showQuestionForm.value = false;
  editingQuestion.value = null;
  preselectedType.value = null;
}

async function handleQuestionSave(payload) {
  try {
    if (editingQuestion.value) {
      // Update existing question
      const { data } = await axios.put(
        `/api/recruiter/questions/${editingQuestion.value.id}`,
        payload,
      );
      const index = questions.value.findIndex(q => q.id === editingQuestion.value.id);
      if (index !== -1) {
        questions.value[index] = data.question;
      }
      toast.success('Savol yangilandi');
    } else {
      // Create new question
      const { data } = await axios.post('/api/recruiter/questions', {
        ...payload,
        questionnaire_id: questionnaire.value.id,
      });
      questions.value.push(data.question);
      toast.success('Savol qo\'shildi');
    }
    closeQuestionForm();
  } catch (error) {
    const msg = error.response?.data?.message || 'Xatolik yuz berdi';
    toast.error(msg);
  }
}

async function handleReorder() {
  const reorderData = questions.value.map((q, i) => ({
    id: q.id,
    sort_order: i + 1,
  }));

  try {
    await axios.put('/api/recruiter/questions/reorder', {
      questions: reorderData,
    });
  } catch {
    toast.error('Tartibni saqlashda xatolik');
  }
}

function confirmDeleteQuestion(question) {
  deletingQuestion.value = question;
  showDeleteDialog.value = true;
}

async function confirmDelete() {
  if (!deletingQuestion.value) return;
  deleting.value = true;
  try {
    await axios.delete(`/api/recruiter/questions/${deletingQuestion.value.id}`);
    questions.value = questions.value.filter(q => q.id !== deletingQuestion.value.id);
    toast.success('Savol o\'chirildi');
    showDeleteDialog.value = false;
    deletingQuestion.value = null;
  } catch {
    toast.error('O\'chirishda xatolik');
  } finally {
    deleting.value = false;
  }
}

async function duplicateQuestion(question) {
  try {
    const payload = {
      questionnaire_id: questionnaire.value.id,
      type: question.type,
      text_uz: question.text_uz,
      weight: question.weight,
      is_required: question.is_required,
      is_knockout: question.is_knockout,
      correct_answer: question.correct_answer || {},
      scoring_config: question.scoring_config || {},
    };

    if (question.options?.length) {
      payload.options = question.options.map((o, i) => ({
        value: o.value || `option_${i + 1}`,
        label_uz: o.label_uz,
        is_correct: o.is_correct,
        score_value: o.score_value,
        sort_order: i + 1,
      }));
    }

    const { data } = await axios.post('/api/recruiter/questions', payload);
    questions.value.push(data.question);
    toast.success('Nusxa yaratildi');
  } catch {
    toast.error('Nusxa yaratishda xatolik');
  }
}

function hasTypeOptions(type) {
  return ['single_choice', 'multi_select', 'knockout'].includes(type);
}

// --- Template functions ---

async function saveAsTemplate() {
  savingTemplate.value = true;
  try {
    const questionsData = questions.value.map(q => ({
      type: q.type,
      text_uz: q.text_uz,
      text_ru: q.text_ru || null,
      weight: q.weight || 0,
      is_required: q.is_required ?? true,
      is_knockout: q.is_knockout ?? false,
      correct_answer: q.correct_answer || null,
      scoring_config: q.scoring_config || null,
      options: (q.options || []).map(o => ({
        value: o.value,
        label_uz: o.label_uz,
        label_ru: o.label_ru || null,
        is_correct: o.is_correct ?? false,
        score_value: o.score_value || null,
      })),
    }));

    await axios.post('/api/recruiter/templates', {
      name: templateForm.value.name,
      category: templateForm.value.category || null,
      questions_data: questionsData,
    });

    toast.success('Shablon muvaffaqiyatli saqlandi');
    showSaveTemplateModal.value = false;
    templateForm.value = { name: '', category: '' };
  } catch (error) {
    toast.error(error.response?.data?.message || 'Shablonni saqlashda xatolik');
  } finally {
    savingTemplate.value = false;
  }
}

async function fetchTemplates() {
  loadingTemplates.value = true;
  try {
    const { data } = await axios.get('/api/recruiter/templates', {
      params: { per_page: 50 },
    });
    templates.value = data.data || [];
  } catch {
    templates.value = [];
  } finally {
    loadingTemplates.value = false;
  }
}

async function applyTemplate(template) {
  applyingTemplate.value = true;
  try {
    const { data } = await axios.post(`/api/recruiter/templates/${template.id}/apply`, {
      vacancy_id: route.params.vacancyId,
    });
    questionnaire.value = data.questionnaire;
    questions.value = data.questionnaire.questions || [];
    settingsForm.value = {
      title: questionnaire.value.title || '',
      time_limit_minutes: questionnaire.value.time_limit_minutes || 30,
      passing_score: questionnaire.value.passing_score || 60,
      auto_reject_below: questionnaire.value.auto_reject_below || 30,
    };
    toast.success(`"${template.name}" shabloni qo'llanildi`);
  } catch (error) {
    const msg = error.response?.data?.message || 'Shablonni qo\'llashda xatolik';
    toast.error(msg);
  } finally {
    applyingTemplate.value = false;
  }
}
</script>
