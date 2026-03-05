import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/utils/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('auth_token') || null)
  const loading = ref(false)

  const isAuthenticated = computed(() => !!token.value && !!user.value)
  const isWorker = computed(() => !!user.value?.worker_profile)
  const isEmployer = computed(() => !!user.value?.employer_profile)

  async function loginWithTelegram(initData) {
    loading.value = true
    try {
      const response = await api.post('/auth/telegram', { init_data: initData })
      token.value = response.data.token
      user.value = response.data.user
      localStorage.setItem('auth_token', token.value)
      return response.data
    } catch (error) {
      console.error('Login failed:', error)
      throw error
    } finally {
      loading.value = false
    }
  }

  async function fetchUser() {
    if (!token.value) return null

    loading.value = true
    try {
      const response = await api.get('/me')
      user.value = response.data
      return response.data
    } catch (error) {
      console.error('Fetch user failed:', error)
      if (error.response?.status === 401) {
        logout()
      }
      throw error
    } finally {
      loading.value = false
    }
  }

  async function updateProfile(data) {
    loading.value = true
    try {
      const response = await api.put('/me', data)
      user.value = response.data
      return response.data
    } catch (error) {
      console.error('Update profile failed:', error)
      throw error
    } finally {
      loading.value = false
    }
  }

  function logout() {
    user.value = null
    token.value = null
    localStorage.removeItem('auth_token')
  }

  return {
    user,
    token,
    loading,
    isAuthenticated,
    isWorker,
    isEmployer,
    loginWithTelegram,
    fetchUser,
    updateProfile,
    logout,
  }
})
