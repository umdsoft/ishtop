<template>
  <div class="group relative rounded-xl border border-surface-200 dark:border-surface-700 overflow-hidden hover:shadow-md hover:border-surface-300 dark:hover:border-surface-600 transition-all duration-200">
    <div class="flex">
      <!-- Type color stripe -->
      <div class="w-1.5 flex-shrink-0" :class="typeColor.stripe" />

      <div class="flex-1 p-4">
        <div class="flex items-start gap-3">
          <!-- Drag Handle + Number -->
          <div class="flex flex-col items-center gap-1.5 pt-0.5">
            <button class="drag-handle cursor-move text-surface-300 hover:text-surface-500 dark:text-surface-600 dark:hover:text-surface-400 opacity-0 group-hover:opacity-100 transition-opacity">
              <Bars3Icon class="h-4 w-4" />
            </button>
            <div
              class="w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold"
              :class="typeColor.number"
            >
              {{ index + 1 }}
            </div>
          </div>

          <!-- Content -->
          <div class="flex-1 min-w-0">
            <!-- Type + badges row -->
            <div class="flex items-center gap-2 mb-2 flex-wrap">
              <span
                class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg text-xs font-semibold"
                :class="typeColor.badge"
              >
                <span class="flex-shrink-0" v-html="typeIndicator" />
                {{ typeLabel }}
              </span>
              <span
                v-if="question.is_knockout"
                class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium bg-red-50 dark:bg-red-950/30 text-red-600 dark:text-red-400"
              >
                <span class="w-1 h-1 rounded-full bg-red-500" />
                Knockout
              </span>
              <span
                v-if="question.is_required !== false"
                class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium bg-danger-50 dark:bg-danger-950/30 text-danger-600 dark:text-danger-400"
              >
                <span class="w-1 h-1 rounded-full bg-danger-500" />
                Majburiy
              </span>
              <span
                v-else
                class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium bg-surface-100 dark:bg-surface-800 text-surface-500 dark:text-surface-400"
              >
                Ixtiyoriy
              </span>
              <span
                v-if="showWeight && question.weight"
                class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium bg-surface-100 dark:bg-surface-800 text-surface-600 dark:text-surface-400"
              >
                {{ question.weight }} ball
              </span>
            </div>

            <!-- Question text -->
            <p class="font-medium text-surface-900 dark:text-surface-100 leading-relaxed">
              {{ question.text_uz }}
            </p>
            <p v-if="question.text_ru" class="text-sm text-surface-500 dark:text-surface-400 mt-1 flex items-center gap-1.5">
              <span class="inline-block w-4 h-3 rounded-sm bg-surface-200 dark:bg-surface-700 text-[8px] font-bold text-surface-500 dark:text-surface-400 leading-none text-center">RU</span>
              {{ question.text_ru }}
            </p>

            <!-- Type-specific answer preview -->
            <div class="mt-3 pt-3 border-t border-surface-100 dark:border-surface-800">
              <!-- Single choice / knockout options -->
              <div v-if="isRadio && question.options?.length" class="space-y-1.5">
                <div
                  v-for="(option, oi) in question.options.slice(0, 4)"
                  :key="oi"
                  class="flex items-center gap-2.5 text-sm"
                >
                  <div class="w-4 h-4 rounded-full border-2 border-surface-300 dark:border-surface-600 flex-shrink-0" />
                  <span :class="[
                    'text-surface-700 dark:text-surface-300',
                    option.is_correct ? 'font-medium text-success-700 dark:text-success-400' : '',
                  ]">
                    {{ option.label_uz }}
                    <span v-if="option.score_value" class="text-xs text-surface-400 ml-1">({{ option.score_value }}b)</span>
                  </span>
                </div>
                <p v-if="question.options.length > 4" class="text-xs text-surface-400 dark:text-surface-500 ml-7">
                  +{{ question.options.length - 4 }} ta variant
                </p>
              </div>

              <!-- Multiple choice / multi_select options -->
              <div v-else-if="isCheckbox && question.options?.length" class="space-y-1.5">
                <div
                  v-for="(option, oi) in question.options.slice(0, 4)"
                  :key="oi"
                  class="flex items-center gap-2.5 text-sm"
                >
                  <div class="w-4 h-4 rounded border-2 border-surface-300 dark:border-surface-600 flex-shrink-0" />
                  <span :class="[
                    'text-surface-700 dark:text-surface-300',
                    option.is_correct ? 'font-medium text-success-700 dark:text-success-400' : '',
                  ]">
                    {{ option.label_uz }}
                    <span v-if="option.score_value" class="text-xs text-surface-400 ml-1">({{ option.score_value }}b)</span>
                  </span>
                </div>
                <p v-if="question.options.length > 4" class="text-xs text-surface-400 dark:text-surface-500 ml-7">
                  +{{ question.options.length - 4 }} ta variant
                </p>
              </div>

              <!-- Rating preview -->
              <div v-else-if="question.type === 'rating'" class="flex items-center gap-1">
                <div
                  v-for="star in 5"
                  :key="star"
                  class="w-7 h-7 rounded-md border border-surface-200 dark:border-surface-700 flex items-center justify-center"
                  v-html="starSvg"
                />
                <span class="text-xs text-surface-400 dark:text-surface-500 ml-1">1-5</span>
              </div>

              <!-- Yes/No preview -->
              <div v-else-if="question.type === 'yes_no'" class="flex items-center gap-2">
                <div class="px-4 py-1.5 rounded-lg border border-success-200 dark:border-success-800 bg-success-50 dark:bg-success-950/20 text-sm font-medium text-success-700 dark:text-success-400">
                  Ha
                </div>
                <div class="px-4 py-1.5 rounded-lg border border-danger-200 dark:border-danger-800 bg-danger-50 dark:bg-danger-950/20 text-sm font-medium text-danger-700 dark:text-danger-400">
                  Yo'q
                </div>
              </div>

              <!-- Text / open_text preview -->
              <div v-else-if="question.type === 'text' || question.type === 'open_text'" class="flex items-center gap-2">
                <div class="flex-1 px-3 py-2 rounded-lg border border-dashed border-surface-300 dark:border-surface-600 bg-surface-50 dark:bg-surface-800/50">
                  <span class="text-xs text-surface-400 dark:text-surface-500">Matnli javob kiritish maydoni</span>
                </div>
              </div>

              <!-- Number range preview -->
              <div v-else-if="question.type === 'number_range'" class="flex items-center gap-2">
                <div class="flex-1 px-3 py-2 rounded-lg border border-dashed border-surface-300 dark:border-surface-600 bg-surface-50 dark:bg-surface-800/50">
                  <span class="text-xs text-surface-400 dark:text-surface-500">
                    Raqamli javob
                    <template v-if="question.correct_answer">
                      ({{ question.correct_answer.min }} — {{ question.correct_answer.max }})
                    </template>
                  </span>
                </div>
              </div>

              <!-- File upload preview -->
              <div v-else-if="question.type === 'file_upload'" class="flex items-center gap-2">
                <div class="flex-1 px-3 py-2 rounded-lg border border-dashed border-surface-300 dark:border-surface-600 bg-surface-50 dark:bg-surface-800/50 flex items-center gap-2">
                  <svg class="w-4 h-4 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                  </svg>
                  <span class="text-xs text-surface-400 dark:text-surface-500">Fayl yuklash</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Actions slot -->
          <div class="flex-shrink-0 ml-2">
            <slot name="actions" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Bars3Icon } from '@heroicons/vue/24/outline';
import { getTypeColor, getTypeIndicator, getTypeLabel, STAR_SVG, isRadioType, hasChoiceOptions } from '../../composables/useQuestionTypes';

const props = defineProps({
  question: { type: Object, required: true },
  index: { type: Number, required: true },
  questionTypes: { type: Array, required: true },
  showWeight: { type: Boolean, default: false },
});

const typeColor = computed(() => getTypeColor(props.question.type));
const typeIndicator = computed(() => getTypeIndicator(props.question.type));
const typeLabel = computed(() => getTypeLabel(props.question.type, props.questionTypes));
const starSvg = STAR_SVG;

const isRadio = computed(() => isRadioType(props.question.type));
const isCheckbox = computed(() => {
  return hasChoiceOptions(props.question.type) && !isRadioType(props.question.type);
});
</script>
