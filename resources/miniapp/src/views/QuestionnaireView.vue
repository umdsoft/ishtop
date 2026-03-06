<template>
  <!-- Loading -->
  <div v-if="loading" class="flex items-center justify-center min-h-screen">
    <LoadingSpinner />
  </div>

  <!-- Completion -->
  <div v-else-if="completed" class="flex flex-col items-center justify-center min-h-screen px-6">
    <div
      class="w-20 h-20 rounded-full flex items-center justify-center mb-5"
      style="background-color: rgba(16, 185, 129, 0.12);"
    >
      <svg class="w-10 h-10" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
      </svg>
    </div>
    <h2 class="text-xl font-bold mb-2" style="color: var(--tg-theme-text-color);">
      {{ t('questionnaire.congrats') }}
    </h2>
    <p class="text-[14px] mb-6 text-center" style="color: var(--tg-theme-hint-color);">
      {{ t('questionnaire.completed') }}
    </p>
    <button
      class="w-full py-3.5 rounded-xl font-semibold text-[15px] active:scale-[0.97] transition-transform"
      style="background-color: var(--tg-theme-button-color); color: var(--tg-theme-button-text-color);"
      @click="router.push('/applications')"
    >
      {{ t('questionnaire.go_to_apps') }}
    </button>
  </div>

  <!-- Questionnaire -->
  <div v-else class="min-h-screen" style="background-color: var(--tg-theme-bg-color);">
    <!-- Header -->
    <div
      class="sticky top-0 z-10 px-5 pt-4 pb-3"
      style="background-color: var(--tg-theme-bg-color); border-bottom: 1px solid var(--separator-color, rgba(128,128,128,0.12));"
    >
      <div class="flex items-center justify-between mb-3">
        <h1 class="text-[17px] font-bold" style="color: var(--tg-theme-text-color);">
          {{ t('questionnaire.title') }}
        </h1>
        <button
          class="w-8 h-8 rounded-full flex items-center justify-center active:scale-[0.9] transition-transform"
          style="background-color: var(--tg-theme-secondary-bg-color);"
          @click="handleBack"
        >
          <svg class="w-4 h-4" style="color: var(--tg-theme-hint-color);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Progress -->
      <div class="w-full h-1.5 rounded-full overflow-hidden" style="background-color: var(--tg-theme-secondary-bg-color);">
        <div
          class="h-full rounded-full transition-all duration-500 ease-out"
          style="background-color: var(--tg-theme-button-color);"
          :style="{ width: `${questionnaireStore.progress}%` }"
        ></div>
      </div>
      <p class="text-[12px] mt-1.5" style="color: var(--tg-theme-hint-color);">
        {{ t('questionnaire.question_of') }} {{ questionnaireStore.currentQuestionIndex + 1 }} / {{ questionnaireStore.totalQuestions }}
      </p>
    </div>

    <!-- Question Card -->
    <div v-if="questionnaireStore.currentQuestion" class="px-5 pt-4 pb-32">
      <div
        class="rounded-2xl p-5 mb-5"
        style="background-color: var(--tg-theme-secondary-bg-color);"
      >
        <!-- Knockout Badge -->
        <span
          v-if="questionnaireStore.currentQuestion.is_knockout"
          class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold mb-3"
          style="background-color: rgba(239, 68, 68, 0.12); color: #ef4444;"
        >
          <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
          </svg>
          {{ t('questionnaire.knockout') }}
        </span>

        <!-- Question Text -->
        <h2 class="text-[16px] font-semibold leading-snug mb-2" style="color: var(--tg-theme-text-color);">
          {{ questionnaireStore.currentQuestion.text_uz || questionnaireStore.currentQuestion.text_ru }}
        </h2>

        <!-- Score -->
        <p
          v-if="questionnaireStore.currentQuestion.weight > 0"
          class="text-[13px]"
          style="color: var(--tg-theme-hint-color);"
        >
          {{ t('questionnaire.score') }} {{ questionnaireStore.currentQuestion.weight }}
        </p>
      </div>

      <!-- Answer Options -->
      <div class="space-y-2.5">
        <!-- Single Choice -->
        <template v-if="questionnaireStore.currentQuestion.type === 'single_choice'">
          <button
            v-for="option in questionnaireStore.currentQuestion.options"
            :key="option.id"
            class="w-full p-4 rounded-xl text-left transition-all duration-200 active:scale-[0.98]"
            :style="currentAnswer === option.value
              ? {
                  backgroundColor: 'rgba(var(--tg-button-rgb, 59,130,246), 0.1)',
                  border: '2px solid var(--tg-theme-button-color)',
                }
              : {
                  backgroundColor: 'var(--tg-theme-secondary-bg-color)',
                  border: '2px solid transparent',
                }"
            @click="setAnswer(option.value)"
          >
            <div class="flex items-center gap-3">
              <div
                class="w-5 h-5 rounded-full flex-shrink-0 flex items-center justify-center"
                :style="currentAnswer === option.value
                  ? { border: '2px solid var(--tg-theme-button-color)' }
                  : { border: '2px solid var(--tg-theme-hint-color)', opacity: '0.4' }"
              >
                <div
                  v-if="currentAnswer === option.value"
                  class="w-2.5 h-2.5 rounded-full"
                  style="background-color: var(--tg-theme-button-color);"
                ></div>
              </div>
              <span class="text-[14px]" style="color: var(--tg-theme-text-color);">
                {{ option.label_uz || option.label_ru || option.label }}
              </span>
            </div>
          </button>
        </template>

        <!-- Multi Select -->
        <template v-else-if="questionnaireStore.currentQuestion.type === 'multi_select'">
          <button
            v-for="option in questionnaireStore.currentQuestion.options"
            :key="option.id"
            class="w-full p-4 rounded-xl text-left transition-all duration-200 active:scale-[0.98]"
            :style="isSelected(option.value)
              ? {
                  backgroundColor: 'rgba(var(--tg-button-rgb, 59,130,246), 0.1)',
                  border: '2px solid var(--tg-theme-button-color)',
                }
              : {
                  backgroundColor: 'var(--tg-theme-secondary-bg-color)',
                  border: '2px solid transparent',
                }"
            @click="toggleOption(option.value)"
          >
            <div class="flex items-center gap-3">
              <div
                class="w-5 h-5 rounded flex-shrink-0 flex items-center justify-center"
                :style="isSelected(option.value)
                  ? { backgroundColor: 'var(--tg-theme-button-color)', border: '2px solid var(--tg-theme-button-color)' }
                  : { border: '2px solid var(--tg-theme-hint-color)', opacity: '0.4' }"
              >
                <svg v-if="isSelected(option.value)" class="w-3.5 h-3.5" fill="white" viewBox="0 0 20 20">
                  <path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                </svg>
              </div>
              <span class="text-[14px]" style="color: var(--tg-theme-text-color);">
                {{ option.label_uz || option.label_ru || option.label }}
              </span>
            </div>
          </button>
        </template>

        <!-- Number Range -->
        <div v-else-if="questionnaireStore.currentQuestion.type === 'number_range'" class="px-1">
          <div class="text-center mb-4">
            <span
              class="text-4xl font-bold"
              style="color: var(--tg-theme-button-color);"
            >{{ currentAnswer || 0 }}</span>
          </div>
          <input
            type="range"
            :min="questionnaireStore.currentQuestion.scoring_config?.min || 0"
            :max="questionnaireStore.currentQuestion.scoring_config?.max || 100"
            :step="questionnaireStore.currentQuestion.scoring_config?.step || 1"
            v-model.number="currentAnswer"
            class="range-slider"
          />
          <div class="flex justify-between mt-2 text-[12px]" style="color: var(--tg-theme-hint-color);">
            <span>{{ questionnaireStore.currentQuestion.scoring_config?.min || 0 }}</span>
            <span>{{ questionnaireStore.currentQuestion.scoring_config?.max || 100 }}</span>
          </div>
        </div>

        <!-- Open Text -->
        <div v-else-if="questionnaireStore.currentQuestion.type === 'open_text'">
          <textarea
            v-model="currentAnswer"
            rows="5"
            class="w-full p-4 rounded-xl text-[14px] resize-none outline-none"
            style="background-color: var(--tg-theme-secondary-bg-color); color: var(--tg-theme-text-color); border: 2px solid transparent;"
            :placeholder="t('questionnaire.text_placeholder')"
            @focus="$event.target.style.borderColor = 'var(--tg-theme-button-color)'"
            @blur="$event.target.style.borderColor = 'transparent'"
          ></textarea>
        </div>

        <!-- Knockout (Yes/No) -->
        <div v-else-if="questionnaireStore.currentQuestion.type === 'knockout'" class="grid grid-cols-2 gap-3">
          <button
            class="p-5 rounded-xl font-semibold text-[15px] transition-all duration-200 active:scale-[0.97]"
            :style="currentAnswer === 'yes'
              ? { backgroundColor: 'rgba(16, 185, 129, 0.15)', color: '#10b981', border: '2px solid #10b981' }
              : { backgroundColor: 'var(--tg-theme-secondary-bg-color)', color: 'var(--tg-theme-text-color)', border: '2px solid transparent' }"
            @click="setAnswer('yes')"
          >
            <svg class="w-6 h-6 mx-auto mb-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
            </svg>
            {{ t('common.yes') }}
          </button>
          <button
            class="p-5 rounded-xl font-semibold text-[15px] transition-all duration-200 active:scale-[0.97]"
            :style="currentAnswer === 'no'
              ? { backgroundColor: 'rgba(239, 68, 68, 0.15)', color: '#ef4444', border: '2px solid #ef4444' }
              : { backgroundColor: 'var(--tg-theme-secondary-bg-color)', color: 'var(--tg-theme-text-color)', border: '2px solid transparent' }"
            @click="setAnswer('no')"
          >
            <svg class="w-6 h-6 mx-auto mb-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
            {{ t('common.no') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Fixed Bottom Navigation -->
    <div
      class="fixed bottom-0 left-0 right-0 z-30 px-5 py-3 safe-area-bottom"
      style="background-color: var(--tg-theme-bg-color); border-top: 1px solid var(--separator-color, rgba(128,128,128,0.12));"
    >
      <div class="flex gap-3">
        <button
          v-if="!questionnaireStore.isFirstQuestion"
          class="flex-1 py-3.5 rounded-xl font-semibold text-[15px] flex items-center justify-center gap-2 active:scale-[0.97] transition-transform"
          style="background-color: var(--tg-theme-secondary-bg-color); color: var(--tg-theme-text-color);"
          @click="previousQuestion"
        >
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
          </svg>
          {{ t('questionnaire.back') }}
        </button>
        <button
          v-if="!questionnaireStore.isLastQuestion"
          class="flex-1 py-3.5 rounded-xl font-semibold text-[15px] flex items-center justify-center gap-2 active:scale-[0.97] transition-transform"
          :style="isAnswered
            ? { backgroundColor: 'var(--tg-theme-button-color)', color: 'var(--tg-theme-button-text-color)' }
            : { backgroundColor: 'var(--tg-theme-secondary-bg-color)', color: 'var(--tg-theme-hint-color)', opacity: '0.6' }"
          @click="nextQuestion"
          :disabled="!isAnswered"
        >
          {{ t('questionnaire.next') }}
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
          </svg>
        </button>
        <button
          v-else
          class="flex-1 py-3.5 rounded-xl font-semibold text-[15px] flex items-center justify-center gap-2 active:scale-[0.97] transition-transform"
          :style="isAnswered && !submitting
            ? { backgroundColor: '#10b981', color: '#ffffff' }
            : { backgroundColor: 'var(--tg-theme-secondary-bg-color)', color: 'var(--tg-theme-hint-color)', opacity: '0.6' }"
          @click="submitQuestionnaire"
          :disabled="!isAnswered || submitting"
        >
          <svg v-if="!submitting" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
          </svg>
          <div v-else class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
          {{ submitting ? t('questionnaire.submitting') : t('questionnaire.finish') }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useQuestionnaireStore } from '@/stores/questionnaire'
import { useTelegram } from '@/composables/useTelegram'
import { useLocale } from '@/composables/useLocale'
import LoadingSpinner from '@/components/LoadingSpinner.vue'

const route = useRoute()
const router = useRouter()
const questionnaireStore = useQuestionnaireStore()
const telegram = useTelegram()
const { t } = useLocale()

const loading = ref(false)
const submitting = ref(false)
const completed = ref(false)
const currentAnswer = ref(null)

const isAnswered = computed(() => {
  if (questionnaireStore.currentQuestion?.type === 'multi_select') {
    return Array.isArray(currentAnswer.value) && currentAnswer.value.length > 0
  }
  return currentAnswer.value !== null && currentAnswer.value !== ''
})

onMounted(async () => {
  telegram.hideMainButton()
  telegram.enableClosingConfirmation()

  loading.value = true
  try {
    await questionnaireStore.fetchQuestionnaire(route.params.vacancyId)
    loadCurrentAnswer()
  } catch (error) {
    telegram.showAlert(t('questionnaire.load_error'))
    router.back()
  } finally {
    loading.value = false
  }
})

watch(
  () => questionnaireStore.currentQuestionIndex,
  () => {
    loadCurrentAnswer()
  }
)

function loadCurrentAnswer() {
  if (questionnaireStore.currentQuestion) {
    const saved = questionnaireStore.getAnswer(questionnaireStore.currentQuestion.id)
    currentAnswer.value = saved || getDefaultAnswer()
  }
}

function getDefaultAnswer() {
  const type = questionnaireStore.currentQuestion?.type
  if (type === 'multi_select') {
    return []
  } else if (type === 'number_range') {
    return questionnaireStore.currentQuestion.scoring_config?.min || 0
  }
  return null
}

function setAnswer(value) {
  telegram.hapticFeedback('soft')
  currentAnswer.value = value
  questionnaireStore.setAnswer(questionnaireStore.currentQuestion.id, value)
}

function isSelected(value) {
  return Array.isArray(currentAnswer.value) && currentAnswer.value.includes(value)
}

function toggleOption(value) {
  telegram.hapticFeedback('soft')

  if (!Array.isArray(currentAnswer.value)) {
    currentAnswer.value = []
  }

  const index = currentAnswer.value.indexOf(value)
  if (index > -1) {
    currentAnswer.value.splice(index, 1)
  } else {
    currentAnswer.value.push(value)
  }

  questionnaireStore.setAnswer(questionnaireStore.currentQuestion.id, currentAnswer.value)
}

function nextQuestion() {
  if (isAnswered.value) {
    telegram.hapticFeedback('soft')
    questionnaireStore.nextQuestion()
  }
}

function previousQuestion() {
  telegram.hapticFeedback('soft')
  questionnaireStore.previousQuestion()
}

async function submitQuestionnaire() {
  if (!isAnswered.value) return

  const confirm = await telegram.showConfirm(t('questionnaire.confirm_submit'))

  if (!confirm) return

  submitting.value = true
  try {
    await questionnaireStore.submitQuestionnaire(route.query.applicationId)
    telegram.disableClosingConfirmation()
    completed.value = true
    telegram.hapticFeedback('heavy')
  } catch (error) {
    telegram.showAlert(error.response?.data?.message || t('questionnaire.submit_error'))
  } finally {
    submitting.value = false
  }
}

async function handleBack() {
  const confirm = await telegram.showConfirm(t('questionnaire.exit_confirm'))

  if (confirm) {
    telegram.disableClosingConfirmation()
    questionnaireStore.reset()
    router.back()
  }
}
</script>

<style scoped>
.safe-area-bottom {
  padding-bottom: max(12px, env(safe-area-inset-bottom, 12px));
}

.range-slider {
  -webkit-appearance: none;
  appearance: none;
  width: 100%;
  height: 6px;
  border-radius: 3px;
  background: var(--tg-theme-secondary-bg-color);
  outline: none;
}

.range-slider::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: var(--tg-theme-button-color);
  cursor: pointer;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
}

.range-slider::-moz-range-thumb {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: var(--tg-theme-button-color);
  cursor: pointer;
  border: none;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
}
</style>
