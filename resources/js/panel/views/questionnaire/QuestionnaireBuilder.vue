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
                {{ vacancy.title }}
              </p>
            </div>
          </div>
          <div v-if="questionnaire" class="flex items-center gap-3">
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
      <div v-if="!questionnaire">
        <AppCard>
          <template #header>
            <h2 class="text-lg font-semibold text-surface-900 dark:text-surface-100">
              Savolnoma sozlamalari
            </h2>
          </template>

          <div class="space-y-4 max-w-lg">
            <AppInput
              v-model="createForm.title"
              label="Sarlavha"
              :placeholder="`${vacancy.title} — Savolnoma`"
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

            <div v-if="questions.length > 0" class="space-y-3">
              <draggable
                v-model="questions"
                item-key="id"
                handle=".drag-handle"
                :animation="200"
                ghost-class="opacity-50"
                @end="handleReorder"
              >
                <template #item="{ element, index }">
                  <div class="p-4 bg-surface-50 dark:bg-surface-900 rounded-lg">
                    <div class="flex items-start gap-3">
                      <!-- Drag Handle -->
                      <button class="drag-handle p-1 mt-1 cursor-move text-surface-400 hover:text-surface-600 dark:hover:text-surface-300">
                        <Bars3Icon class="h-5 w-5" />
                      </button>

                      <!-- Content -->
                      <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between mb-2">
                          <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1 flex-wrap">
                              <span class="text-sm font-medium text-surface-500 dark:text-surface-400">
                                Savol {{ index + 1 }}
                              </span>
                              <AppBadge size="sm" variant="primary">
                                {{ getQuestionTypeLabel(element.type) }}
                              </AppBadge>
                              <AppBadge v-if="element.is_knockout" size="sm" variant="danger">
                                Knockout
                              </AppBadge>
                            </div>
                            <p class="font-medium text-surface-900 dark:text-surface-100">
                              {{ element.text_uz }}
                            </p>
                          </div>
                          <div class="flex items-center gap-2 ml-4 flex-shrink-0">
                            <div class="text-right">
                              <p class="text-xs text-surface-600 dark:text-surface-400">Og'irlik</p>
                              <p class="font-semibold text-surface-900 dark:text-surface-100">
                                {{ element.weight }}
                              </p>
                            </div>
                            <AppDropdown
                              :items="getQuestionActions(element)"
                            >
                              <template #trigger>
                                <button class="p-2 rounded hover:bg-surface-100 dark:hover:bg-surface-800">
                                  <EllipsisVerticalIcon class="h-5 w-5 text-surface-600" />
                                </button>
                              </template>
                            </AppDropdown>
                          </div>
                        </div>

                        <!-- Options preview -->
                        <div v-if="hasTypeOptions(element.type) && element.options?.length" class="mt-2 pl-4 space-y-1">
                          <div
                            v-for="option in element.options.slice(0, 4)"
                            :key="option.id"
                            class="flex items-center gap-2 text-sm"
                          >
                            <div :class="[
                              'w-1.5 h-1.5 rounded-full flex-shrink-0',
                              option.is_correct ? 'bg-success-500' : 'bg-surface-400',
                            ]" />
                            <span :class="[
                              option.is_correct
                                ? 'text-success-700 dark:text-success-400 font-medium'
                                : 'text-surface-700 dark:text-surface-300',
                            ]">
                              {{ option.label_uz }}
                              <span v-if="option.score_value" class="text-xs text-surface-500">({{ option.score_value }} ball)</span>
                            </span>
                          </div>
                          <p v-if="element.options.length > 4" class="text-xs text-surface-500 dark:text-surface-400 pl-3.5">
                            +{{ element.options.length - 4 }} ta ko'proq
                          </p>
                        </div>

                        <!-- Number range preview -->
                        <div v-if="element.type === 'number_range' && element.correct_answer" class="mt-2 text-sm text-surface-600 dark:text-surface-400">
                          Oraliq: {{ element.correct_answer.min }} — {{ element.correct_answer.max }}
                        </div>
                      </div>
                    </div>
                  </div>
                </template>
              </draggable>
            </div>

            <div v-else class="text-center py-12">
              <ClipboardDocumentListIcon class="h-16 w-16 mx-auto text-surface-400 dark:text-surface-500 mb-4" />
              <h3 class="text-lg font-semibold text-surface-900 dark:text-surface-100 mb-2">
                Savollar yo'q
              </h3>
              <p class="text-surface-600 dark:text-surface-400 mb-4">
                Savolnomangizga birinchi savolni qo'shing
              </p>
              <AppButton variant="primary" @click="openAddQuestion(null)">
                <template #icon-left>
                  <PlusIcon class="h-5 w-5" />
                </template>
                Savol qo'shish
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

          <!-- Question Types -->
          <AppCard>
            <template #header>
              <h3 class="text-sm font-semibold text-surface-900 dark:text-surface-100">Savol turlari</h3>
            </template>

            <div class="space-y-2">
              <button
                v-for="type in questionTypes"
                :key="type.value"
                class="w-full flex items-center gap-3 p-3 rounded-lg hover:bg-surface-50 dark:hover:bg-surface-800 transition-colors text-left"
                @click="openAddQuestion(type.value)"
              >
                <div class="flex-shrink-0 text-2xl">{{ type.icon }}</div>
                <div class="flex-1">
                  <p class="text-sm font-medium text-surface-900 dark:text-surface-100">
                    {{ type.label }}
                  </p>
                  <p class="text-xs text-surface-500 dark:text-surface-400">
                    {{ type.description }}
                  </p>
                </div>
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
    <QuestionForm
      :show="showQuestionForm"
      :question="editingQuestion"
      :preselected-type="preselectedType"
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
                • {{ questionnaire.time_limit_minutes }} daqiqa
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
  Bars3Icon,
  EllipsisVerticalIcon,
  ClipboardDocumentListIcon,
  InformationCircleIcon,
} from '@heroicons/vue/24/outline';
import AppCard from '../../components/ui/AppCard.vue';
import AppButton from '../../components/ui/AppButton.vue';
import AppBadge from '../../components/ui/AppBadge.vue';
import AppDropdown from '../../components/ui/AppDropdown.vue';
import AppModal from '../../components/ui/AppModal.vue';
import AppInput from '../../components/ui/AppInput.vue';
import AppConfirmDialog from '../../components/ui/AppConfirmDialog.vue';
import AppLoadingSpinner from '../../components/ui/AppLoadingSpinner.vue';
import QuestionForm from './QuestionForm.vue';

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

const questionTypes = [
  { value: 'single_choice', label: 'Bir tanlov', description: 'Faqat bitta javobni tanlash mumkin', icon: '⭕' },
  { value: 'multi_select', label: 'Ko\'p tanlov', description: 'Bir nechta javob tanlash mumkin', icon: '☑️' },
  { value: 'number_range', label: 'Raqam', description: 'Min va max raqam orasida', icon: '🔢' },
  { value: 'open_text', label: 'Ochiq javob', description: 'Erkin matn kiritish', icon: '📝' },
  { value: 'knockout', label: 'Knockout', description: 'Rad etish uchun savol', icon: '⛔' },
  { value: 'file_upload', label: 'Fayl yuklash', description: 'Hujjat yoki rasm yuklash', icon: '📎' },
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
    createForm.value.title = `${vacancy.value.title} — Savolnoma`;
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
        title: createForm.value.title || `${vacancy.value.title} — Savolnoma`,
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

function getQuestionTypeLabel(type) {
  const typeObj = questionTypes.find(t => t.value === type);
  return typeObj ? typeObj.label : type;
}

function hasTypeOptions(type) {
  return ['single_choice', 'multi_select', 'knockout'].includes(type);
}

function getQuestionActions(question) {
  return [
    {
      label: 'Tahrirlash',
      onClick: () => openEditQuestion(question),
    },
    {
      label: 'Nusxa olish',
      onClick: () => duplicateQuestion(question),
    },
    {
      label: 'O\'chirish',
      danger: true,
      onClick: () => confirmDeleteQuestion(question),
    },
  ];
}
</script>
