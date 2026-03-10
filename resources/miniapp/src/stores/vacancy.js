import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/utils/api'

export const useVacancyStore = defineStore('vacancy', () => {
  const vacancies = ref([])
  const currentVacancy = ref(null)
  const filters = ref({
    q: '',
    category: null,
    city: null,
    work_type: null,
    salary_min: null,
    salary_max: null,
    experience_required: null,
  })
  const loading = ref(false)
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 20,
    total: 0,
  })

  async function fetchVacancies(params = {}, { append = false } = {}) {
    loading.value = true
    try {
      const response = await api.get('/vacancies', {
        params: { ...filters.value, ...params },
      })
      if (append) {
        vacancies.value = [...vacancies.value, ...response.data.data]
      } else {
        vacancies.value = response.data.data
      }
      pagination.value = {
        current_page: response.data.current_page,
        last_page: response.data.last_page,
        per_page: response.data.per_page,
        total: response.data.total,
      }
      return response.data
    } catch (error) {
      console.error('Fetch vacancies failed:', error)
      throw error
    } finally {
      loading.value = false
    }
  }

  async function fetchVacancy(id) {
    loading.value = true
    try {
      const response = await api.get(`/vacancies/${id}`)
      currentVacancy.value = response.data
      return response.data
    } catch (error) {
      console.error('Fetch vacancy failed:', error)
      throw error
    } finally {
      loading.value = false
    }
  }

  async function searchVacancies(params) {
    return await fetchVacancies(params)
  }

  async function nearbyVacancies(lat, lng, radius = 10) {
    loading.value = true
    try {
      const response = await api.get('/vacancies/nearby', {
        params: { lat, lng, radius },
      })
      vacancies.value = response.data.data
      pagination.value = {
        current_page: response.data.current_page,
        last_page: response.data.last_page,
        per_page: response.data.per_page,
        total: response.data.total,
      }
      return response.data
    } catch (error) {
      console.error('Nearby vacancies failed:', error)
      throw error
    } finally {
      loading.value = false
    }
  }

  async function recommendedVacancies() {
    try {
      const response = await api.get('/vacancies/recommended')
      return response.data.vacancies
    } catch (error) {
      console.error('Recommended vacancies failed:', error)
      throw error
    }
  }

  async function createVacancy(data) {
    loading.value = true
    try {
      const response = await api.post('/vacancies', data)
      return response.data
    } catch (error) {
      console.error('Create vacancy failed:', error)
      throw error
    } finally {
      loading.value = false
    }
  }

  async function applyToVacancy(vacancyId, data = {}) {
    loading.value = true
    try {
      const response = await api.post('/applications', {
        vacancy_id: vacancyId,
        ...data,
      })
      return response.data
    } catch (error) {
      console.error('Apply to vacancy failed:', error)
      throw error
    } finally {
      loading.value = false
    }
  }

  function setFilters(newFilters) {
    filters.value = { ...filters.value, ...newFilters }
  }

  function resetFilters() {
    filters.value = {
      q: '',
      category: null,
      city: null,
      work_type: null,
      salary_min: null,
      salary_max: null,
      experience_required: null,
    }
  }

  // Hydrate from bootstrap data (avoids separate /vacancies call on first load)
  function hydrateVacancies(data) {
    if (data && data.length && vacancies.value.length === 0) {
      vacancies.value = data
      return true
    }
    return false
  }

  return {
    vacancies,
    currentVacancy,
    filters,
    loading,
    pagination,
    fetchVacancies,
    fetchVacancy,
    searchVacancies,
    nearbyVacancies,
    recommendedVacancies,
    createVacancy,
    applyToVacancy,
    setFilters,
    resetFilters,
    hydrateVacancies,
  }
})
