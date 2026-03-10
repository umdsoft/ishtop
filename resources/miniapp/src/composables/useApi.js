import { ref } from 'vue'
import api from '@/utils/api'

export function useApi(fn) {
  const loading = ref(false)
  const error = ref(null)
  const data = ref(null)

  const execute = async (...args) => {
    loading.value = true
    error.value = null

    try {
      const response = await fn(...args)
      data.value = response.data
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || err.message || 'Error'
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    loading,
    error,
    data,
    execute,
  }
}

export { api }
