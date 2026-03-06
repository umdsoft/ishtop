<template>
  <AppModal
    :show="show"
    size="2xl"
    :title="isEditing ? 'Savolni tahrirlash' : 'Yangi savol'"
    @close="$emit('close')"
  >
    <div class="space-y-5">
      <!-- Question Type -->
      <div v-if="!isEditing">
        <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-2">
          Savol turi <span class="text-danger-500">*</span>
        </label>
        <div class="grid grid-cols-3 gap-2">
          <button
            v-for="type in questionTypes"
            :key="type.value"
            :class="[
              'flex items-center gap-2.5 p-3 rounded-xl border-2 text-left transition-all text-sm',
              form.type === type.value
                ? 'border-brand-500 bg-brand-50 dark:bg-brand-900/20 dark:border-brand-400 shadow-sm'
                : 'border-surface-200 dark:border-surface-700 hover:border-surface-300 dark:hover:border-surface-600',
            ]"
            @click="form.type = type.value"
          >
            <div
              class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
              :class="getTypeColorClass(type.value)"
            >
              <span v-html="getTypeIndicatorSvg(type.value)" class="[&>svg]:w-4 [&>svg]:h-4" />
            </div>
            <span class="font-medium text-surface-900 dark:text-surface-100">{{ type.label }}</span>
          </button>
        </div>
      </div>

      <!-- Question Text UZ -->
      <div>
        <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1">
          Savol matni (o'zbekcha) <span class="text-danger-500">*</span>
        </label>
        <textarea
          v-model="form.text_uz"
          rows="3"
          class="w-full px-3 py-2 rounded-lg border border-surface-300 dark:border-surface-600 bg-surface-0 dark:bg-surface-800 text-surface-900 dark:text-surface-100 focus:ring-2 focus:ring-brand-500 focus:border-transparent resize-none"
          placeholder="Savolingizni kiriting..."
        />
      </div>

      <!-- Question Text RU -->
      <div>
        <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1">
          Savol matni (ruscha, ixtiyoriy)
        </label>
        <textarea
          v-model="form.text_ru"
          rows="2"
          class="w-full px-3 py-2 rounded-lg border border-surface-300 dark:border-surface-600 bg-surface-0 dark:bg-surface-800 text-surface-900 dark:text-surface-100 focus:ring-2 focus:ring-brand-500 focus:border-transparent resize-none"
          placeholder="Вопрос на русском..."
        />
      </div>

      <!-- Weight & Settings (scoring mode) -->
      <div v-if="showScoring" class="grid grid-cols-2 gap-4">
        <AppInput
          v-model="form.weight"
          type="number"
          label="Og'irlik (ball)"
          placeholder="10"
          hint="0 dan 100 gacha"
        />
        <div class="space-y-3 pt-7">
          <label class="flex items-center gap-3 cursor-pointer">
            <input
              v-model="form.is_required"
              type="checkbox"
              class="w-4 h-4 rounded border-surface-300 text-brand-600 focus:ring-brand-500 dark:border-surface-600 dark:bg-surface-800"
            />
            <span class="text-sm text-surface-700 dark:text-surface-300">Majburiy savol</span>
          </label>
          <label class="flex items-center gap-3 cursor-pointer">
            <input
              v-model="form.is_knockout"
              type="checkbox"
              class="w-4 h-4 rounded border-surface-300 text-danger-600 focus:ring-danger-500 dark:border-surface-600 dark:bg-surface-800"
            />
            <span class="text-sm text-surface-700 dark:text-surface-300">Knockout savol</span>
          </label>
        </div>
      </div>

      <!-- Settings (non-scoring mode) -->
      <div v-else class="flex items-center gap-6">
        <label class="flex items-center gap-2 cursor-pointer">
          <input
            v-model="form.is_required"
            type="checkbox"
            class="rounded border-surface-300 dark:border-surface-600 text-brand-600 focus:ring-brand-500"
          />
          <span class="text-sm text-surface-700 dark:text-surface-300">Majburiy savol</span>
        </label>
      </div>

      <!-- Options (for choice types) -->
      <div v-if="hasOptions" class="space-y-3">
        <div class="flex items-center justify-between">
          <label class="block text-sm font-medium text-surface-700 dark:text-surface-300">
            Javob variantlari <span class="text-danger-500">*</span>
          </label>
          <button
            type="button"
            class="flex items-center gap-1 text-sm text-brand-600 dark:text-brand-400 hover:text-brand-700 dark:hover:text-brand-300 font-medium"
            @click="addOption"
          >
            <PlusIcon class="w-3.5 h-3.5" />
            Variant qo'shish
          </button>
        </div>

        <div v-if="form.options.length === 0" class="text-center py-4 border border-dashed border-surface-300 dark:border-surface-600 rounded-lg">
          <p class="text-sm text-surface-500 dark:text-surface-400">Kamida 2 ta variant qo'shing</p>
        </div>

        <div v-else class="space-y-2">
          <div
            v-for="(option, index) in form.options"
            :key="index"
            class="flex items-center gap-2.5 p-3 bg-surface-50 dark:bg-surface-800 rounded-lg group/opt"
          >
            <!-- Is correct (scoring mode) -->
            <label v-if="showScoring" class="flex-shrink-0 cursor-pointer" :title="form.type === 'knockout' ? 'O\'tish' : 'To\'g\'ri javob'">
              <input
                v-model="option.is_correct"
                :type="isRadio ? 'radio' : 'checkbox'"
                :name="'correct-' + formKey"
                :value="true"
                :checked="option.is_correct"
                class="w-4 h-4 border-surface-300 text-success-600 focus:ring-success-500 dark:border-surface-600 dark:bg-surface-800"
                @change="handleCorrectChange(index)"
              />
            </label>
            <!-- Type indicator (non-scoring mode) -->
            <div v-else class="flex-shrink-0 mt-0.5">
              <div v-if="isRadio" class="w-4 h-4 rounded-full border-2 border-surface-300 dark:border-surface-500" />
              <div v-else class="w-4 h-4 rounded border-2 border-surface-300 dark:border-surface-500" />
            </div>

            <!-- Option labels -->
            <div class="flex items-center gap-2 flex-1">
              <input
                v-model="option.label_uz"
                type="text"
                class="flex-1 px-3 py-1.5 text-sm rounded-md border border-surface-300 dark:border-surface-600 bg-white dark:bg-surface-900 text-surface-900 dark:text-surface-100 focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                :placeholder="`Variant ${index + 1} (o'zbekcha)`"
              />
              <input
                v-model="option.label_ru"
                type="text"
                class="flex-1 px-3 py-1.5 text-sm rounded-md border border-surface-300 dark:border-surface-600 bg-white dark:bg-surface-900 text-surface-900 dark:text-surface-100 focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                :placeholder="`Variant ${index + 1} (ruscha)`"
              />
            </div>

            <!-- Score value (scoring mode) -->
            <input
              v-if="showScoring"
              v-model.number="option.score_value"
              type="number"
              placeholder="Ball"
              class="w-20 px-3 py-1.5 text-sm rounded-md border border-surface-300 dark:border-surface-600 bg-white dark:bg-surface-900 text-surface-900 dark:text-surface-100 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-center"
            />

            <!-- Remove -->
            <button
              v-if="form.options.length > 2"
              type="button"
              class="flex-shrink-0 p-1.5 text-surface-400 hover:text-danger-500 hover:bg-danger-50 dark:hover:bg-danger-950/30 rounded-lg transition-colors opacity-0 group-hover/opt:opacity-100"
              @click="removeOption(index)"
            >
              <TrashIcon class="w-4 h-4" />
            </button>
          </div>
        </div>
        <p v-if="showScoring" class="text-xs text-surface-500 dark:text-surface-400">
          {{ form.type === 'knockout' ? 'Belgilangan variant — o\'tish sharti' : 'Belgilangan variantlar — to\'g\'ri javoblar' }}
        </p>
      </div>

      <!-- Number range config -->
      <div v-if="form.type === 'number_range'" class="space-y-4">
        <label class="block text-sm font-medium text-surface-700 dark:text-surface-300">
          To'g'ri javob oralig'i
        </label>
        <div class="grid grid-cols-2 gap-4">
          <AppInput
            v-model="form.correct_answer_min"
            type="number"
            label="Minimal qiymat"
            placeholder="0"
          />
          <AppInput
            v-model="form.correct_answer_max"
            type="number"
            label="Maksimal qiymat"
            placeholder="100"
          />
        </div>
      </div>

      <!-- Open text config -->
      <div v-if="form.type === 'open_text'">
        <AppInput
          v-model="form.max_length"
          type="number"
          label="Maksimal belgilar soni"
          placeholder="500"
          hint="Bo'sh qoldiring cheklovsiz bo'ladi"
        />
      </div>

      <!-- File upload config -->
      <div v-if="form.type === 'file_upload'" class="space-y-4">
        <AppInput
          v-model="form.allowed_types"
          label="Ruxsat etilgan fayl turlari"
          placeholder="pdf,doc,docx,jpg,png"
          hint="Vergul bilan ajrating"
        />
        <AppInput
          v-model="form.max_size_mb"
          type="number"
          label="Maksimal hajm (MB)"
          placeholder="5"
        />
      </div>
    </div>

    <template #footer>
      <div class="flex gap-3 justify-end">
        <AppButton variant="outline" @click="$emit('close')">
          Bekor qilish
        </AppButton>
        <AppButton
          variant="primary"
          :disabled="!canSave"
          @click="handleSave"
        >
          {{ isEditing ? 'Saqlash' : 'Qo\'shish' }}
        </AppButton>
      </div>
    </template>
  </AppModal>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { PlusIcon, TrashIcon } from '@heroicons/vue/24/outline';
import { getTypeColor, getTypeIndicator, isRadioType, hasChoiceOptions } from '../../composables/useQuestionTypes';
import AppModal from '../ui/AppModal.vue';
import AppButton from '../ui/AppButton.vue';
import AppInput from '../ui/AppInput.vue';

const props = defineProps({
  show: { type: Boolean, required: true },
  question: { type: Object, default: null },
  preselectedType: { type: String, default: null },
  questionTypes: { type: Array, required: true },
  showScoring: { type: Boolean, default: false },
});

const emit = defineEmits(['close', 'save']);

const formKey = ref(0);

const isEditing = computed(() => !!props.question);

function getTypeColorClass(type) {
  return getTypeColor(type).number;
}

function getTypeIndicatorSvg(type) {
  return getTypeIndicator(type);
}

const defaultForm = () => ({
  type: props.questionTypes[0]?.value || 'text',
  text_uz: '',
  text_ru: '',
  is_required: true,
  is_knockout: false,
  weight: 10,
  options: [
    { label_uz: '', label_ru: '', is_correct: false, score_value: 0 },
    { label_uz: '', label_ru: '', is_correct: false, score_value: 0 },
  ],
  correct_answer_min: '',
  correct_answer_max: '',
  max_length: '',
  allowed_types: '',
  max_size_mb: '',
});

const form = ref(defaultForm());

const hasOptions = computed(() => hasChoiceOptions(form.value.type));
const isRadio = computed(() => isRadioType(form.value.type));

const canSave = computed(() => {
  if (!form.value.text_uz?.trim()) return false;
  if (hasOptions.value && form.value.options.filter(o => o.label_uz?.trim()).length < 2) return false;
  return true;
});

watch(() => props.show, (newVal) => {
  if (newVal) {
    formKey.value++;
    if (props.question) {
      // Editing existing question
      form.value = {
        type: props.question.type || props.questionTypes[0]?.value || 'text',
        text_uz: props.question.text_uz || '',
        text_ru: props.question.text_ru || '',
        is_required: props.question.is_required ?? true,
        is_knockout: props.question.is_knockout ?? false,
        weight: props.question.weight ?? 10,
        options: props.question.options?.length > 0
          ? props.question.options.map(o => ({
              label_uz: o.label_uz || '',
              label_ru: o.label_ru || '',
              is_correct: o.is_correct || false,
              score_value: o.score_value ?? 0,
            }))
          : [
              { label_uz: '', label_ru: '', is_correct: false, score_value: 0 },
              { label_uz: '', label_ru: '', is_correct: false, score_value: 0 },
            ],
        correct_answer_min: props.question.correct_answer?.min ?? '',
        correct_answer_max: props.question.correct_answer?.max ?? '',
        max_length: props.question.scoring_config?.max_length ?? '',
        allowed_types: props.question.scoring_config?.allowed_types ?? '',
        max_size_mb: props.question.scoring_config?.max_size_mb ?? '',
      };
    } else {
      form.value = defaultForm();
      if (props.preselectedType) {
        form.value.type = props.preselectedType;
      }
    }
  }
});

function addOption() {
  form.value.options.push({
    label_uz: '',
    label_ru: '',
    is_correct: false,
    score_value: 0,
  });
}

function removeOption(index) {
  form.value.options.splice(index, 1);
}

function handleCorrectChange(index) {
  if (isRadio.value) {
    form.value.options.forEach((opt, i) => {
      opt.is_correct = i === index;
    });
  }
}

function handleSave() {
  if (!canSave.value) return;

  const payload = {
    type: form.value.type,
    text_uz: form.value.text_uz,
    text_ru: form.value.text_ru || '',
    is_required: form.value.is_required,
    weight: props.showScoring ? (parseInt(form.value.weight) || 10) : 0,
    is_knockout: props.showScoring ? form.value.is_knockout : false,
    correct_answer: {},
    scoring_config: {},
  };

  if (hasOptions.value) {
    payload.options = form.value.options
      .filter(o => o.label_uz?.trim())
      .map((o, i) => ({
        value: `option_${i + 1}`,
        label_uz: o.label_uz,
        label_ru: o.label_ru || '',
        is_correct: o.is_correct,
        score_value: parseInt(o.score_value) || 0,
        sort_order: i + 1,
      }));

    if (form.value.type === 'knockout') {
      const correctOption = payload.options.find(o => o.is_correct);
      if (correctOption) {
        payload.correct_answer = { value: correctOption.value };
      }
    }
  }

  if (form.value.type === 'number_range') {
    payload.correct_answer = {
      min: parseFloat(form.value.correct_answer_min) || 0,
      max: parseFloat(form.value.correct_answer_max) || 100,
    };
  }

  if (form.value.type === 'open_text' && form.value.max_length) {
    payload.scoring_config = { max_length: parseInt(form.value.max_length) };
  }

  if (form.value.type === 'file_upload') {
    payload.scoring_config = {};
    if (form.value.allowed_types) {
      payload.scoring_config.allowed_types = form.value.allowed_types;
    }
    if (form.value.max_size_mb) {
      payload.scoring_config.max_size_mb = parseInt(form.value.max_size_mb);
    }
  }

  emit('save', payload);
}
</script>
