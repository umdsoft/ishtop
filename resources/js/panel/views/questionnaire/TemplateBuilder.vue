<template>
  <div>
    <!-- Loading -->
    <div v-if="loading" class="flex items-center justify-center py-20">
      <AppLoadingSpinner size="lg" text="Yuklanmoqda..." />
    </div>

    <!-- Main Content -->
    <div v-else>
      <!-- Header -->
      <div class="mb-6">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center gap-3">
            <button
              class="p-2 rounded-lg hover:bg-surface-100 dark:hover:bg-surface-800 transition-colors"
              @click="$router.push({ name: 'questionnaire-templates' })"
            >
              <ArrowLeftIcon class="h-5 w-5 text-surface-600 dark:text-surface-400" />
            </button>
            <div>
              <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">
                {{ isEditing ? 'Shablonni tahrirlash' : 'Yangi shablon yaratish' }}
              </h1>
              <p v-if="isEditing && templateData.name" class="text-surface-600 dark:text-surface-400 mt-0.5">
                {{ templateData.name }}
              </p>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <AppButton
              v-if="questions.length > 0"
              variant="outline"
              @click="showPreview = true"
            >
              <template #icon-left>
                <EyeIcon class="h-5 w-5" />
              </template>
              Ko'rish
            </AppButton>
            <AppButton
              variant="primary"
              :loading="saving"
              :disabled="!templateData.name?.trim() || questions.length === 0"
              @click="saveTemplate"
            >
              {{ isEditing ? 'Saqlash' : 'Yaratish' }}
            </AppButton>
          </div>
        </div>

        <!-- Stats -->
        <div v-if="questions.length > 0" class="grid grid-cols-3 gap-4">
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
              <p class="text-sm text-surface-600 dark:text-surface-400">Majburiy</p>
              <p class="text-2xl font-bold text-surface-900 dark:text-surface-100 mt-1">
                {{ questions.filter(q => q.is_required).length }}
              </p>
            </div>
          </AppCard>
          <AppCard>
            <div class="text-center">
              <p class="text-sm text-surface-600 dark:text-surface-400">Savol turlari</p>
              <p class="text-2xl font-bold text-surface-900 dark:text-surface-100 mt-1">
                {{ uniqueTypes }}
              </p>
            </div>
          </AppCard>
        </div>
      </div>

      <!-- Two-column layout -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
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
                item-key="__index"
                handle=".drag-handle"
                :animation="200"
                ghost-class="opacity-50"
              >
                <template #item="{ element, index }">
                  <QuestionCardItem
                    :question="element"
                    :index="index"
                    :question-types="questionTypes"
                  >
                    <template #actions>
                      <div class="flex flex-col gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button
                          class="p-1.5 rounded-lg hover:bg-brand-50 dark:hover:bg-brand-950/30 transition-colors"
                          title="Tahrirlash"
                          @click="editQuestion(index)"
                        >
                          <PencilIcon class="h-4 w-4 text-surface-400 hover:text-brand-600 dark:hover:text-brand-400" />
                        </button>
                        <button
                          class="p-1.5 rounded-lg hover:bg-danger-50 dark:hover:bg-danger-950/30 transition-colors"
                          title="O'chirish"
                          @click="removeQuestion(index)"
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
          <!-- Template Info -->
          <AppCard>
            <template #header>
              <h3 class="text-sm font-semibold text-surface-900 dark:text-surface-100">Shablon ma'lumotlari</h3>
            </template>
            <div class="space-y-4">
              <AppInput
                v-model="templateData.name"
                label="Shablon nomi"
                placeholder="Masalan: IT mutaxassis uchun savolnoma"
                required
                size="sm"
              />
              <AppInput
                v-model="templateData.category"
                label="Kategoriya"
                placeholder="Masalan: IT, Savdo, Umumiy"
                size="sm"
              />
            </div>
          </AppCard>

          <!-- Question Types (quick add) -->
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
                  Savollar tartibini drag-and-drop bilan o'zgartiring. Shablon yaratilgandan so'ng uni istalgan vakansiyaga qo'llashingiz mumkin.
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
      @close="showQuestionForm = false"
      @save="handleQuestionSave"
    />

    <!-- Preview Modal -->
    <AppModal
      :show="showPreview"
      size="2xl"
      title="Shablon ko'rinishi"
      @close="showPreview = false"
    >
      <div class="space-y-4">
        <div class="flex items-center justify-between mb-4 pb-4 border-b border-surface-200 dark:border-surface-800">
          <div>
            <h3 class="text-lg font-semibold text-surface-900 dark:text-surface-100">
              {{ templateData.name || 'Nomi kiritilmagan' }}
            </h3>
            <p class="text-sm text-surface-500 dark:text-surface-400 mt-1">
              {{ questions.length }} ta savol
              <span v-if="templateData.category"> &bull; {{ templateData.category }}</span>
            </p>
          </div>
        </div>

        <div
          v-for="(question, index) in questions"
          :key="index"
          class="p-4 bg-surface-50 dark:bg-surface-900 rounded-lg"
        >
          <p class="text-sm text-surface-500 dark:text-surface-400 mb-1">
            Savol {{ index + 1 }}
            <span v-if="question.is_required" class="text-danger-500">*</span>
          </p>
          <p class="font-medium text-surface-900 dark:text-surface-100 mb-1">
            {{ question.text_uz }}
          </p>
          <p v-if="question.text_ru" class="text-sm text-surface-500 dark:text-surface-400 mb-3">
            {{ question.text_ru }}
          </p>

          <!-- Choice options preview -->
          <div v-if="question.options?.length" class="space-y-2 mt-3">
            <label
              v-for="(option, oi) in question.options"
              :key="oi"
              class="flex items-center gap-3 p-2 rounded-lg hover:bg-surface-100 dark:hover:bg-surface-800"
            >
              <input
                :type="question.type === 'multiple_choice' ? 'checkbox' : 'radio'"
                :name="`preview-q-${index}`"
                disabled
                class="w-4 h-4 border-surface-300 text-brand-600"
              />
              <span class="text-sm text-surface-700 dark:text-surface-300">{{ option.label_uz }}</span>
            </label>
          </div>

          <!-- Text preview -->
          <div v-if="question.type === 'text'" class="mt-3">
            <textarea
              disabled
              placeholder="Javobingizni yozing..."
              rows="2"
              class="w-full px-3 py-2 text-sm rounded-lg border border-surface-300 dark:border-surface-600 bg-surface-100 dark:bg-surface-800 text-surface-500 resize-none"
            />
          </div>

          <!-- Rating preview -->
          <div v-if="question.type === 'rating'" class="mt-3 flex items-center gap-2">
            <div v-for="star in 5" :key="star" class="w-8 h-8 rounded-lg border border-surface-300 dark:border-surface-600 flex items-center justify-center text-surface-400 text-sm">
              {{ star }}
            </div>
          </div>

          <!-- Yes/No preview -->
          <div v-if="question.type === 'yes_no'" class="mt-3 flex items-center gap-3">
            <div class="px-4 py-2 rounded-lg border border-surface-300 dark:border-surface-600 text-sm text-surface-500">Ha</div>
            <div class="px-4 py-2 rounded-lg border border-surface-300 dark:border-surface-600 text-sm text-surface-500">Yo'q</div>
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
} from '@heroicons/vue/24/outline';
import { getTypeColor as getTypeColorFn, getTypeIndicator as getTypeIndicatorFn } from '../../composables/useQuestionTypes';
import AppCard from '../../components/ui/AppCard.vue';
import AppButton from '../../components/ui/AppButton.vue';
import AppModal from '../../components/ui/AppModal.vue';
import AppInput from '../../components/ui/AppInput.vue';
import AppLoadingSpinner from '../../components/ui/AppLoadingSpinner.vue';
import QuestionCardItem from '../../components/questionnaire/QuestionCardItem.vue';
import QuestionFormModal from '../../components/questionnaire/QuestionFormModal.vue';

const route = useRoute();
const router = useRouter();

const loading = ref(false);
const saving = ref(false);
const isEditing = ref(false);
const templateId = ref(null);

const templateData = ref({
  name: '',
  category: '',
});

const questions = ref([]);

// Question form
const showQuestionForm = ref(false);
const editingQuestion = ref(null);
const editingQuestionIndex = ref(null);
const preselectedType = ref(null);

// Preview
const showPreview = ref(false);

const questionTypes = [
  { value: 'text', label: 'Matn', description: 'Ochiq javob' },
  { value: 'single_choice', label: 'Bitta tanlov', description: 'Bir variantni tanlash' },
  { value: 'multiple_choice', label: 'Ko\'p tanlov', description: 'Bir nechta variant' },
  { value: 'rating', label: 'Baho', description: '1 dan 5 gacha baho' },
  { value: 'yes_no', label: 'Ha/Yo\'q', description: 'Ikki variantli javob' },
];

const uniqueTypes = computed(() => {
  return new Set(questions.value.map(q => q.type)).size;
});

onMounted(async () => {
  const id = route.params.templateId;
  if (id) {
    isEditing.value = true;
    templateId.value = id;
    await loadTemplate(id);
  }
});

async function loadTemplate(id) {
  loading.value = true;
  try {
    const { data } = await axios.get('/api/recruiter/templates', {
      params: { per_page: 50 },
    });
    const all = data.data || [];
    const tpl = all.find(t => t.id == id);
    if (tpl) {
      templateData.value = {
        name: tpl.name,
        category: tpl.category || '',
      };
      questions.value = (tpl.questions_data || []).map((q, i) => ({
        __index: i,
        type: q.type || 'text',
        text_uz: q.text_uz || '',
        text_ru: q.text_ru || '',
        is_required: q.is_required ?? true,
        weight: q.weight || 0,
        options: q.options || [],
      }));
    } else {
      toast.error('Shablon topilmadi');
      router.push({ name: 'questionnaire-templates' });
    }
  } catch {
    toast.error('Shablonni yuklashda xatolik');
    router.push({ name: 'questionnaire-templates' });
  } finally {
    loading.value = false;
  }
}

function openAddQuestion(type) {
  editingQuestion.value = null;
  editingQuestionIndex.value = null;
  preselectedType.value = type;
  showQuestionForm.value = true;
}

function editQuestion(index) {
  editingQuestion.value = { ...questions.value[index] };
  editingQuestionIndex.value = index;
  preselectedType.value = null;
  showQuestionForm.value = true;
}

function removeQuestion(index) {
  questions.value.splice(index, 1);
}

function handleQuestionSave(questionData) {
  if (editingQuestionIndex.value !== null) {
    questions.value[editingQuestionIndex.value] = {
      ...questionData,
      __index: editingQuestionIndex.value,
    };
  } else {
    questions.value.push({
      ...questionData,
      __index: Date.now(),
    });
  }
  showQuestionForm.value = false;
  editingQuestion.value = null;
  editingQuestionIndex.value = null;
}

async function saveTemplate() {
  if (!templateData.value.name?.trim() || questions.value.length === 0) return;

  const emptyQ = questions.value.find(q => !q.text_uz?.trim());
  if (emptyQ) {
    toast.error('Barcha savollar matni kiritilishi kerak');
    return;
  }

  const questionsPayload = questions.value.map((q, i) => ({
    type: q.type,
    text_uz: q.text_uz,
    text_ru: q.text_ru || null,
    is_required: q.is_required ?? true,
    weight: q.weight || 0,
    sort_order: i + 1,
    options: q.options || [],
  }));

  saving.value = true;
  try {
    if (isEditing.value) {
      await axios.put(`/api/recruiter/templates/${templateId.value}`, {
        name: templateData.value.name,
        category: templateData.value.category || null,
        questions_data: questionsPayload,
      });
      toast.success('Shablon yangilandi');
    } else {
      await axios.post('/api/recruiter/templates', {
        name: templateData.value.name,
        category: templateData.value.category || null,
        questions_data: questionsPayload,
      });
      toast.success('Shablon yaratildi');
    }
    router.push({ name: 'questionnaire-templates' });
  } catch (error) {
    const msg = error.response?.data?.message || 'Xatolik yuz berdi';
    toast.error(msg);
  } finally {
    saving.value = false;
  }
}
</script>
