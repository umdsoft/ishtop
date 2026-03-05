<template>
  <div>
    <!-- Header -->
    <div class="mb-6">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">Savolnoma yaratish</h1>
          <p class="text-surface-600 dark:text-surface-400 mt-1">
            Vakansiya uchun savollar qo'shing va ballash tizimini sozlang
          </p>
        </div>
        <div class="flex items-center gap-3">
          <AppButton variant="outline" @click="showPreview = true">
            <template #icon-left>
              <EyeIcon class="h-5 w-5" />
            </template>
            Ko'rish
          </AppButton>
          <AppButton variant="primary" @click="saveQuestionnaire">
            <template #icon-left>
              <CheckIcon class="h-5 w-5" />
            </template>
            Saqlash
          </AppButton>
        </div>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-3 gap-4">
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
            <p class="text-sm text-surface-600 dark:text-surface-400">Jami ball</p>
            <p class="text-2xl font-bold text-surface-900 dark:text-surface-100 mt-1">
              {{ totalScore }}
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

    <!-- Questions List -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2">
        <AppCard>
          <template #header>
            <div class="flex items-center justify-between">
              <h2 class="text-lg font-semibold text-surface-900 dark:text-surface-100">
                Savollar
              </h2>
              <AppButton variant="outline" size="sm" @click="showAddQuestion = true">
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
            >
              <template #item="{ element, index }">
                <div class="p-4 bg-surface-50 dark:bg-surface-900 rounded-lg">
                  <div class="flex items-start gap-3">
                    <!-- Drag Handle -->
                    <button class="drag-handle p-1 mt-1 cursor-move text-surface-400 hover:text-surface-600 dark:hover:text-surface-300">
                      <Bars3Icon class="h-5 w-5" />
                    </button>

                    <!-- Content -->
                    <div class="flex-1">
                      <div class="flex items-start justify-between mb-2">
                        <div class="flex-1">
                          <div class="flex items-center gap-2 mb-1">
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
                            {{ element.question }}
                          </p>
                        </div>
                        <div class="flex items-center gap-2 ml-4">
                          <div class="text-right">
                            <p class="text-xs text-surface-600 dark:text-surface-400">Og'irlik</p>
                            <p class="font-semibold text-surface-900 dark:text-surface-100">
                              {{ element.weight }}
                            </p>
                          </div>
                          <AppDropdown
                            :items="getQuestionActions(element)"
                            label=""
                            variant="ghost"
                            size="sm"
                            button-class="!p-2"
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
                      <div v-if="['single_choice', 'multi_select'].includes(element.type)" class="mt-2 pl-4 space-y-1">
                        <div
                          v-for="option in element.options.slice(0, 3)"
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
                          </span>
                        </div>
                        <p v-if="element.options.length > 3" class="text-xs text-surface-500 dark:text-surface-400 pl-3.5">
                          +{{ element.options.length - 3 }} ko'proq
                        </p>
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
            <AppButton variant="primary" @click="showAddQuestion = true">
              <template #icon-left>
                <PlusIcon class="h-5 w-5" />
              </template>
              Savol qo'shish
            </AppButton>
          </div>
        </AppCard>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
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
              @click="addQuestionType(type.value)"
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

    <!-- Add Question Modal -->
    <AppModal
      :open="showAddQuestion"
      size="lg"
      title="Yangi savol"
      @close="showAddQuestion = false"
    >
      <p class="text-sm text-surface-600 dark:text-surface-400">
        Savol yaratish funksiyasi tez orada qo'shiladi
      </p>

      <template #footer>
        <div class="flex gap-3 justify-end">
          <AppButton variant="outline" @click="showAddQuestion = false">
            Bekor qilish
          </AppButton>
          <AppButton variant="primary" @click="showAddQuestion = false">
            Qo'shish
          </AppButton>
        </div>
      </template>
    </AppModal>

    <!-- Preview Modal -->
    <AppModal
      :open="showPreview"
      size="xl"
      title="Savolnoma ko'rinishi"
      @close="showPreview = false"
    >
      <p class="text-sm text-surface-600 dark:text-surface-400">
        Preview funksiyasi tez orada qo'shiladi
      </p>
    </AppModal>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { toast } from 'vue-sonner';
import draggable from 'vuedraggable';
import {
  PlusIcon,
  CheckIcon,
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

const questions = ref([
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
]);

const showAddQuestion = ref(false);
const showPreview = ref(false);

const questionTypes = [
  {
    value: 'single_choice',
    label: 'Bir tanlov',
    description: 'Faqat bitta javobni tanlash mumkin',
    icon: '⭕',
  },
  {
    value: 'multi_select',
    label: 'Ko\'p tanlov',
    description: 'Bir nechta javob tanlash mumkin',
    icon: '☑️',
  },
  {
    value: 'number_range',
    label: 'Raqam',
    description: 'Min va max raqam orasida',
    icon: '🔢',
  },
  {
    value: 'open_text',
    label: 'Ochiq javob',
    description: 'Erkin matn kiritish',
    icon: '📝',
  },
  {
    value: 'knockout',
    label: 'Knockout',
    description: 'Rad etish uchun savol',
    icon: '⛔',
  },
  {
    value: 'file_upload',
    label: 'Fayl yuklash',
    description: 'Hujjat yoki rasm yuklash',
    icon: '📎',
  },
];

const totalScore = computed(() => {
  return questions.value.reduce((sum, q) => sum + q.weight, 0);
});

const knockoutCount = computed(() => {
  return questions.value.filter(q => q.is_knockout).length;
});

function getQuestionTypeLabel(type) {
  const typeObj = questionTypes.find(t => t.value === type);
  return typeObj ? typeObj.label : type;
}

function getQuestionActions(question) {
  return [
    {
      label: 'Tahrirlash',
      onClick: () => toast.info('Tahrirlash funksiyasi'),
    },
    {
      label: 'Nusxa olish',
      onClick: () => {
        const copy = { ...question, id: Date.now() };
        questions.value.push(copy);
        toast.success('Nusxa olindi');
      },
    },
    {
      label: 'O\'chirish',
      danger: true,
      onClick: () => {
        questions.value = questions.value.filter(q => q.id !== question.id);
        toast.success('Savol o\'chirildi');
      },
    },
  ];
}

function addQuestionType(type) {
  showAddQuestion.value = true;
  toast.info(`${getQuestionTypeLabel(type)} savoli qo'shilmoqda`);
}

function saveQuestionnaire() {
  toast.success('Savolnoma saqlandi');
}
</script>
