import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/utils/api'

export const useQuestionnaireStore = defineStore('questionnaire', () => {
  const questionnaire = ref(null)
  const questions = ref([])
  const answers = ref({})
  const currentQuestionIndex = ref(0)
  const startTime = ref(null)
  const loading = ref(false)

  const currentQuestion = computed(() => questions.value[currentQuestionIndex.value] || null)
  const totalQuestions = computed(() => questions.value.length)
  const progress = computed(() => {
    if (totalQuestions.value === 0) return 0
    return Math.round(((currentQuestionIndex.value + 1) / totalQuestions.value) * 100)
  })
  const isLastQuestion = computed(() => currentQuestionIndex.value === totalQuestions.value - 1)
  const isFirstQuestion = computed(() => currentQuestionIndex.value === 0)

  async function fetchQuestionnaire(vacancyId) {
    loading.value = true
    try {
      const response = await api.get(`/vacancies/${vacancyId}/questionnaire`)
      questionnaire.value = response.data.questionnaire
      questions.value = response.data.questionnaire.questions || []
      answers.value = {}
      currentQuestionIndex.value = 0
      startTime.value = Date.now()
      return response.data
    } catch (error) {
      console.error('Fetch questionnaire failed:', error)
      throw error
    } finally {
      loading.value = false
    }
  }

  function setAnswer(questionId, value) {
    answers.value[questionId] = value
  }

  function getAnswer(questionId) {
    return answers.value[questionId] || null
  }

  function nextQuestion() {
    if (!isLastQuestion.value) {
      currentQuestionIndex.value++
    }
  }

  function previousQuestion() {
    if (!isFirstQuestion.value) {
      currentQuestionIndex.value--
    }
  }

  function goToQuestion(index) {
    if (index >= 0 && index < totalQuestions.value) {
      currentQuestionIndex.value = index
    }
  }

  async function submitQuestionnaire(applicationId) {
    loading.value = true
    try {
      const timeSpent = Math.floor((Date.now() - startTime.value) / 1000)

      const formattedAnswers = Object.entries(answers.value).map(([questionId, value]) => ({
        question_id: questionId,
        answer_value: value,
      }))

      const response = await api.post(`/questionnaires/${questionnaire.value.id}/respond`, {
        application_id: applicationId,
        answers: formattedAnswers,
        time_spent_seconds: timeSpent,
      })

      return response.data
    } catch (error) {
      console.error('Submit questionnaire failed:', error)
      throw error
    } finally {
      loading.value = false
    }
  }

  function reset() {
    questionnaire.value = null
    questions.value = []
    answers.value = {}
    currentQuestionIndex.value = 0
    startTime.value = null
  }

  return {
    questionnaire,
    questions,
    answers,
    currentQuestionIndex,
    currentQuestion,
    totalQuestions,
    progress,
    isLastQuestion,
    isFirstQuestion,
    loading,
    fetchQuestionnaire,
    setAnswer,
    getAnswer,
    nextQuestion,
    previousQuestion,
    goToQuestion,
    submitQuestionnaire,
    reset,
  }
})
