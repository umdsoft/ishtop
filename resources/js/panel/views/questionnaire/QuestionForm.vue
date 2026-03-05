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
              'flex items-center gap-2 p-3 rounded-lg border-2 text-left transition-all text-sm',
              form.type === type.value
                ? 'border-brand-500 bg-brand-50 dark:bg-brand-900/20 dark:border-brand-400'
                : 'border-surface-200 dark:border-surface-700 hover:border-surface-300 dark:hover:border-surface-600',
            ]"
            @click="form.type = type.value"
          >
            <span class="text-lg flex-shrink-0">{{ type.icon }}</span>
            <span class="font-medium text-surface-900 dark:text-surface-100">{{ type.label }}</span>
          </button>
        </div>
      </div>

      <!-- Question Text -->
      <AppTextarea
        v-model="form.text_uz"
        label="Savol matni"
        placeholder="Savolingizni kiriting..."
        :rows="3"
        required
      />

      <!-- Weight & Settings Row -->
      <div class="grid grid-cols-2 gap-4">
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

      <!-- Options for single_choice / multi_select / knockout -->
      <div v-if="hasOptions" class="space-y-3">
        <div class="flex items-center justify-between">
          <label class="block text-sm font-medium text-surface-700 dark:text-surface-300">
            Javob variantlari <span class="text-danger-500">*</span>
          </label>
          <button
            class="text-sm text-brand-600 dark:text-brand-400 hover:text-brand-700 dark:hover:text-brand-300 font-medium"
            @click="addOption"
          >
            + Variant qo'shish
          </button>
        </div>

        <div class="space-y-2">
          <div
            v-for="(option, index) in form.options"
            :key="index"
            class="flex items-center gap-3 p-3 bg-surface-50 dark:bg-surface-800 rounded-lg"
          >
            <!-- Is correct checkbox -->
            <label class="flex-shrink-0 cursor-pointer" :title="form.type === 'knockout' ? 'O\'tish' : 'To\'g\'ri javob'">
              <input
                v-model="option.is_correct"
                :type="form.type === 'single_choice' || form.type === 'knockout' ? 'radio' : 'checkbox'"
                :name="'correct-' + formKey"
                :value="true"
                :checked="option.is_correct"
                class="w-4 h-4 border-surface-300 text-success-600 focus:ring-success-500 dark:border-surface-600 dark:bg-surface-800"
                @change="handleCorrectChange(index)"
              />
            </label>

            <!-- Label -->
            <input
              v-model="option.label_uz"
              type="text"
              :placeholder="`Variant ${index + 1}`"
              class="flex-1 px-3 py-1.5 text-sm rounded-md border border-surface-300 dark:border-surface-600 bg-white dark:bg-surface-900 text-surface-900 dark:text-surface-100 focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
            />

            <!-- Score value -->
            <input
              v-model.number="option.score_value"
              type="number"
              placeholder="Ball"
              class="w-20 px-3 py-1.5 text-sm rounded-md border border-surface-300 dark:border-surface-600 bg-white dark:bg-surface-900 text-surface-900 dark:text-surface-100 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-center"
            />

            <!-- Remove -->
            <button
              v-if="form.options.length > 2"
              class="flex-shrink-0 p-1 text-surface-400 hover:text-danger-500 transition-colors"
              @click="removeOption(index)"
            >
              <XMarkIcon class="w-4 h-4" />
            </button>
          </div>
        </div>
        <p class="text-xs text-surface-500 dark:text-surface-400">
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
        <AppButton variant="secondary" @click="$emit('close')">
          Bekor qilish
        </AppButton>
        <AppButton variant="primary" :loading="saving" @click="handleSave">
          {{ isEditing ? 'Saqlash' : 'Qo\'shish' }}
        </AppButton>
      </div>
    </template>
  </AppModal>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { XMarkIcon } from '@heroicons/vue/24/outline';
import AppModal from '../../components/ui/AppModal.vue';
import AppButton from '../../components/ui/AppButton.vue';
import AppInput from '../../components/ui/AppInput.vue';
import AppTextarea from '../../components/ui/AppTextarea.vue';

const props = defineProps({
  show: { type: Boolean, required: true },
  question: { type: Object, default: null },
  preselectedType: { type: String, default: null },
});

const emit = defineEmits(['close', 'save']);

const formKey = ref(0);
const saving = ref(false);

const isEditing = computed(() => !!props.question);

const questionTypes = [
  { value: 'single_choice', label: 'Bir tanlov', icon: '⭕' },
  { value: 'multi_select', label: 'Ko\'p tanlov', icon: '☑️' },
  { value: 'number_range', label: 'Raqam', icon: '🔢' },
  { value: 'open_text', label: 'Ochiq javob', icon: '📝' },
  { value: 'knockout', label: 'Knockout', icon: '⛔' },
  { value: 'file_upload', label: 'Fayl yuklash', icon: '📎' },
];

const defaultForm = () => ({
  type: 'single_choice',
  text_uz: '',
  weight: 10,
  is_required: true,
  is_knockout: false,
  options: [
    { label_uz: '', is_correct: false, score_value: 0 },
    { label_uz: '', is_correct: false, score_value: 0 },
  ],
  correct_answer_min: '',
  correct_answer_max: '',
  max_length: '',
  allowed_types: '',
  max_size_mb: '',
});

const form = ref(defaultForm());

const hasOptions = computed(() =>
  ['single_choice', 'multi_select', 'knockout'].includes(form.value.type),
);

watch(() => props.show, (newVal) => {
  if (newVal) {
    formKey.value++;
    if (props.question) {
      // Editing existing question
      form.value = {
        type: props.question.type,
        text_uz: props.question.text_uz || '',
        weight: props.question.weight ?? 10,
        is_required: props.question.is_required ?? true,
        is_knockout: props.question.is_knockout ?? false,
        options: props.question.options?.length > 0
          ? props.question.options.map(o => ({
              label_uz: o.label_uz || '',
              is_correct: o.is_correct || false,
              score_value: o.score_value ?? 0,
            }))
          : [
              { label_uz: '', is_correct: false, score_value: 0 },
              { label_uz: '', is_correct: false, score_value: 0 },
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
  form.value.options.push({ label_uz: '', is_correct: false, score_value: 0 });
}

function removeOption(index) {
  form.value.options.splice(index, 1);
}

function handleCorrectChange(index) {
  if (form.value.type === 'single_choice' || form.value.type === 'knockout') {
    // Only one can be correct
    form.value.options.forEach((opt, i) => {
      opt.is_correct = i === index;
    });
  }
}

function handleSave() {
  // Validate
  if (!form.value.text_uz.trim()) return;
  if (hasOptions.value) {
    const validOptions = form.value.options.filter(o => o.label_uz.trim());
    if (validOptions.length < 2) return;
  }

  saving.value = true;

  // Build payload
  const payload = {
    type: form.value.type,
    text_uz: form.value.text_uz,
    weight: parseInt(form.value.weight) || 10,
    is_required: form.value.is_required,
    is_knockout: form.value.is_knockout,
    correct_answer: {},
    scoring_config: {},
  };

  if (hasOptions.value) {
    payload.options = form.value.options
      .filter(o => o.label_uz.trim())
      .map((o, i) => ({
        value: `option_${i + 1}`,
        label_uz: o.label_uz,
        is_correct: o.is_correct,
        score_value: parseInt(o.score_value) || 0,
        sort_order: i + 1,
      }));
    // Set correct_answer for knockout
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
  saving.value = false;
}
</script>
