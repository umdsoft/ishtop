import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || '/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
})

// Request interceptor — har bir so'rovga token va initData qo'shish
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }

    const initData = window.Telegram?.WebApp?.initData
    if (initData) {
      config.headers['X-Telegram-Init-Data'] = initData
    }

    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor — 401 da avtomatik re-auth + retry
let isRefreshing = false
let failedQueue = []

function processQueue(error, token = null) {
  failedQueue.forEach(({ resolve, reject, config }) => {
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
      resolve(api(config))
    } else {
      reject(error)
    }
  })
  failedQueue = []
}

api.interceptors.response.use(
  (response) => response,
  async (error) => {
    const originalRequest = error.config

    // 401 bo'lsa va auth endpoint emas bo'lsa — re-auth qilamiz
    if (
      error.response?.status === 401 &&
      !originalRequest._retry &&
      !originalRequest.url?.includes('/auth/')
    ) {
      // Agar allaqachon refresh jarayonda bo'lsa — navbatga qo'shamiz
      if (isRefreshing) {
        return new Promise((resolve, reject) => {
          failedQueue.push({ resolve, reject, config: originalRequest })
        })
      }

      originalRequest._retry = true
      isRefreshing = true

      const tg = window.Telegram?.WebApp
      if (tg?.initData) {
        try {
          // fetch() ishlatamiz — axios interceptor loop'dan qochish uchun
          const baseURL = import.meta.env.VITE_API_URL || '/api'
          const res = await fetch(baseURL + '/auth/telegram', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
            },
            body: JSON.stringify({ init_data: tg.initData }),
          })

          if (res.ok) {
            const data = await res.json()
            const newToken = data.token
            localStorage.setItem('auth_token', newToken)

            // Auth store'ni yangilash (agar mavjud bo'lsa)
            try {
              const { useAuthStore } = await import('@/stores/auth')
              const authStore = useAuthStore()
              authStore.token = newToken
              authStore.user = data.user
            } catch (e) {
              // Store hali yuklanmagan bo'lishi mumkin
            }

            // Asl so'rovni yangi token bilan qayta yuborish
            originalRequest.headers.Authorization = `Bearer ${newToken}`
            processQueue(null, newToken)
            isRefreshing = false
            return api(originalRequest)
          }
        } catch (e) {
          // Re-auth muvaffaqiyatsiz
        }
      }

      // Re-auth iloji bo'lmadi
      processQueue(error, null)
      isRefreshing = false
      localStorage.removeItem('auth_token')
    }

    return Promise.reject(error)
  }
)

export default api
