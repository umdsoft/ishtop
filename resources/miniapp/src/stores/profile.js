import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/utils/api'

export const useProfileStore = defineStore('profile', () => {
  const workerProfile = ref(null)
  const employerProfile = ref(null)
  const loading = ref(false)

  async function fetchWorkerProfile() {
    loading.value = true
    try {
      const response = await api.get('/profile/worker')
      workerProfile.value = response.data.profile || response.data
      return workerProfile.value
    } catch (error) {
      console.error('Fetch worker profile failed:', error)
      throw error
    } finally {
      loading.value = false
    }
  }

  async function updateWorkerProfile(data) {
    loading.value = true
    try {
      const response = await api.put('/profile/worker', data)
      workerProfile.value = response.data.profile || response.data
      return workerProfile.value
    } catch (error) {
      console.error('Update worker profile failed:', error)
      throw error
    } finally {
      loading.value = false
    }
  }

  async function fetchEmployerProfile() {
    loading.value = true
    try {
      const response = await api.get('/profile/employer')
      employerProfile.value = response.data.profile || response.data
      return employerProfile.value
    } catch (error) {
      console.error('Fetch employer profile failed:', error)
      throw error
    } finally {
      loading.value = false
    }
  }

  async function updateEmployerProfile(data) {
    loading.value = true
    try {
      const response = await api.put('/profile/employer', data)
      employerProfile.value = response.data.profile || response.data
      return employerProfile.value
    } catch (error) {
      console.error('Update employer profile failed:', error)
      throw error
    } finally {
      loading.value = false
    }
  }

  async function updateSearchStatus(status) {
    loading.value = true
    try {
      const response = await api.put('/profile/search-status', { status })
      if (workerProfile.value) {
        workerProfile.value.search_status = status
      }
      return response.data
    } catch (error) {
      console.error('Update search status failed:', error)
      throw error
    } finally {
      loading.value = false
    }
  }

  return {
    workerProfile,
    employerProfile,
    loading,
    fetchWorkerProfile,
    updateWorkerProfile,
    fetchEmployerProfile,
    updateEmployerProfile,
    updateSearchStatus,
  }
})
