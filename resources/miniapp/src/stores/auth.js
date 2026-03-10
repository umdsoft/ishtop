import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/utils/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('auth_token') || null)
  const loading = ref(false)
  const authAttempted = ref(false)

  // Resolves when initial auth attempt is done
  let _authReadyResolve
  const authReady = new Promise((resolve) => {
    _authReadyResolve = resolve
  })

  const isAuthenticated = computed(() => !!token.value && !!user.value)
  const isWorker = computed(() => !!user.value?.worker_profile || !!user.value?.has_worker_profile)
  const isEmployer = computed(() => !!user.value?.employer_profile || !!user.value?.has_employer_profile)

  // Bootstrap data from combined init endpoint
  const bootstrapData = ref(null)

  async function loginWithTelegram(initData) {
    loading.value = true
    try {
      // Use combined init endpoint — auth + categories + vacancies in 1 request
      const response = await api.post('/auth/telegram-init', { init_data: initData })
      token.value = response.data.token
      user.value = response.data.user
      localStorage.setItem('auth_token', token.value)
      // Store bootstrap data for HomeView to consume
      if (response.data.categories || response.data.vacancies) {
        bootstrapData.value = {
          categories: response.data.categories,
          vacancies: response.data.vacancies,
        }
      }
      return response.data
    } catch (error) {
      console.error('Login failed:', error)
      // Try to restore session from stored token
      if (token.value) {
        try {
          await fetchUser()
          return
        } catch (e) {
          // Token is invalid
        }
      }
      throw error
    } finally {
      loading.value = false
      authAttempted.value = true
      _authReadyResolve()
    }
  }

  async function loginWithToken(authToken) {
    loading.value = true
    try {
      const baseURL = import.meta.env.VITE_API_URL || '/api'
      const res = await fetch(baseURL + '/auth/telegram-token', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ token: authToken }),
      })
      if (!res.ok) throw new Error('Token auth failed')
      const data = await res.json()
      token.value = data.token
      user.value = data.user
      localStorage.setItem('auth_token', data.token)
      return data
    } catch (error) {
      console.error('Token login failed:', error)
      throw error
    } finally {
      loading.value = false
      authAttempted.value = true
      _authReadyResolve()
    }
  }

  async function fetchUser() {
    if (!token.value) return null

    loading.value = true
    try {
      const response = await api.get('/me')
      user.value = response.data.user || response.data
      return user.value
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
      user.value = response.data.user || response.data
      return user.value
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

  function markAuthReady() {
    authAttempted.value = true
    _authReadyResolve()
  }

  return {
    user,
    token,
    loading,
    authAttempted,
    authReady,
    bootstrapData,
    isAuthenticated,
    isWorker,
    isEmployer,
    loginWithTelegram,
    loginWithToken,
    fetchUser,
    updateProfile,
    logout,
    markAuthReady,
  }
})
