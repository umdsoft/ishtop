<template>
  <div class="questionnaire-view min-h-screen bg-tg-bg">
    <!-- Header -->
    <div class="sticky top-0 bg-tg-bg z-10 p-4 border-b border-gray-200">
      <div class="flex items-center justify-between mb-2">
        <h1 class="text-lg font-bold">Savolnoma</h1>
        <button class="text-tg-hint" @click="handleBack">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Progress Bar -->
      <div class="w-full bg-gray-200 rounded-full h-2">
        <div
          class="bg-tg-button h-2 rounded-full transition-all duration-300"
          :style="{ width: `${questionnaireStore.progress}%` }"
        ></div>
      </div>
      <p class="text-sm text-tg-hint mt-1">
        Savol {{ questionnaireStore.currentQuestionIndex + 1 }} / {{ questionnaireStore.totalQuestions }}
      </p>
    </div>

    <!-- Loading -->
    <LoadingSpinner v-if="loading" />

    <!-- Question -->
    <div v-else-if="questionnaireStore.currentQuestion" class="p-4">
      <div class="card mb-4">
        <!-- Question Text -->
        <div class="mb-4">
          <span v-if="questionnaireStore.currentQuestion.is_knockout" class="inline-block px-2 py-1 bg-red-100 text-red-800 text-xs rounded mb-2">
            Majburiy savol
          </span>
          <h2 class="text-lg font-semibold mb-2">
            {{ questionnaireStore.currentQuestion.text_uz }}
          </h2>
          <p v-if="questionnaireStore.currentQuestion.weight > 0" class="text-sm text-tg-hint">
            Ball: {{ questionnaireStore.currentQuestion.weight }}
          </p>
        </div>

        <!-- Answer Input -->
        <div class="space-y-3">
          <!-- Single Choice -->
          <div
            v-if="questionnaireStore.currentQuestion.type === 'single_choice'"
            v-for="option in questionnaireStore.currentQuestion.options"
            :key="option.id"
            class="p-3 border-2 rounded-lg cursor-pointer transition-all"
            :class="currentAnswer === option.value ? 'border-tg-button bg-blue-50' : 'border-gray-200'"
            @click="setAnswer(option.value)"
          >
            <div class="flex items-center gap-3">
              <div
                class="w-5 h-5 rounded-full border-2 flex items-center justify-center"
                :class="currentAnswer === option.value ? 'border-tg-button' : 'border-gray-300'"
              >
                <div v-if="currentAnswer === option.value" class="w-3 h-3 rounded-full bg-tg-button"></div>
              </div>
              <span>{{ option.label_uz }}</span>
            </div>
          </div>

          <!-- Multi Select -->
          <div
            v-else-if="questionnaireStore.currentQuestion.type === 'multi_select'"
            v-for="option in questionnaireStore.currentQuestion.options"
            :key="option.id"
            class="p-3 border-2 rounded-lg cursor-pointer transition-all"
            :class="isSelected(option.value) ? 'border-tg-button bg-blue-50' : 'border-gray-200'"
            @click="toggleOption(option.value)"
          >
            <div class="flex items-center gap-3">
              <div
                class="w-5 h-5 rounded border-2 flex items-center justify-center"
                :class="isSelected(option.value) ? 'border-tg-button bg-tg-button' : 'border-gray-300'"
              >
                <svg v-if="isSelected(option.value)" class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                </svg>
              </div>
              <span>{{ option.label_uz }}</span>
            </div>
          </div>

          <!-- Number Range -->
          <div v-else-if="questionnaireStore.currentQuestion.type === 'number_range'" class="space-y-2">
            <input
              type="range"
              :min="questionnaireStore.currentQuestion.scoring_config?.min || 0"
              :max="questionnaireStore.currentQuestion.scoring_config?.max || 100"
              :step="questionnaireStore.currentQuestion.scoring_config?.step || 1"
              v-model.number="currentAnswer"
              class="w-full"
            />
            <div class="text-center text-2xl font-bold text-tg-button">
              {{ currentAnswer || 0 }}
            </div>
          </div>

          <!-- Open Text -->
          <textarea
            v-else-if="questionnaireStore.currentQuestion.type === 'open_text'"
            v-model="currentAnswer"
            rows="5"
            class="input w-full"
            placeholder="Javobingizni kiriting..."
          ></textarea>

          <!-- Knockout (Yes/No) -->
          <div v-else-if="questionnaireStore.currentQuestion.type === 'knockout'" class="grid grid-cols-2 gap-3">
            <button
              class="p-4 rounded-lg font-medium border-2 transition-all"
              :class="currentAnswer === 'yes' ? 'border-tg-button bg-blue-50' : 'border-gray-200'"
              @click="setAnswer('yes')"
            >
              ✓ Ha
            </button>
            <button
              class="p-4 rounded-lg font-medium border-2 transition-all"
              :class="currentAnswer === 'no' ? 'border-tg-button bg-blue-50' : 'border-gray-200'"
              @click="setAnswer('no')"
            >
              ✗ Yo'q
            </button>
          </div>
        </div>
      </div>

      <!-- Navigation -->
      <div class="flex gap-3">
        <button
          v-if="!questionnaireStore.isFirstQuestion"
          class="btn-secondary flex-1"
          @click="previousQuestion"
        >
          ← Orqaga
        </button>
        <button
          v-if="!questionnaireStore.isLastQuestion"
          class="btn-primary flex-1"
          @click="nextQuestion"
          :disabled="!isAnswered"
        >
          Keyingi →
        </button>
        <button
          v-else
          class="btn-primary flex-1"
          @click="submitQuestionnaire"
          :disabled="!isAnswered || submitting"
        >
          {{ submitting ? 'Yuborilmoqda...' : 'Tugatish' }}
        </button>
      </div>
    </div>

    <!-- Completion -->
    <div v-else-if="completed" class="p-4 text-center py-12">
      <p class="text-6xl mb-4">✅</p>
      <h2 class="text-2xl font-bold mb-2">Tabriklaymiz!</h2>
      <p class="text-tg-hint mb-6">Savolnoma muvaffaqiyatli to'ldirildi</p>
      <button class="btn-primary" @click="router.push('/applications')">
        Arizalarimga o'tish
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useQuestionnaireStore } from '@/stores/questionnaire'
import { useTelegram } from '@/composables/useTelegram'
import LoadingSpinner from '@/components/LoadingSpinner.vue'

const route = useRoute()
const router = useRouter()
const questionnaireStore = useQuestionnaireStore()
const telegram = useTelegram()

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
    telegram.showAlert('Savolnomani yuklashda xatolik')
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

  const confirm = await telegram.showConfirm(
    'Savolnomani tugatmoqchimisiz? Javoblarni keyinchalik o\'zgartira olmaysiz.'
  )

  if (!confirm) return

  submitting.value = true
  try {
    await questionnaireStore.submitQuestionnaire(route.query.applicationId)
    telegram.disableClosingConfirmation()
    completed.value = true
    telegram.hapticFeedback('heavy')
  } catch (error) {
    telegram.showAlert(error.response?.data?.message || 'Yuborishda xatolik')
  } finally {
    submitting.value = false
  }
}

async function handleBack() {
  const confirm = await telegram.showConfirm(
    'Savolnomani tugatmadingiz. Chiqmoqchimisiz?'
  )

  if (confirm) {
    telegram.disableClosingConfirmation()
    questionnaireStore.reset()
    router.back()
  }
}
</script>
