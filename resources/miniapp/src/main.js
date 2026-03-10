import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import './style.css'

// KadrGo branding: force teal theme (override Telegram's default blue)
;(function() {
  const root = document.documentElement
  root.style.setProperty('--tg-theme-button-color', '#0D9488')
  root.style.setProperty('--tg-theme-link-color', '#0D9488')
  root.style.setProperty('--tg-button-rgb', '13,148,136')
})()

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)

// Global error handler — kutilmagan xatolarni ushlash
app.config.errorHandler = (err, instance, info) => {
  console.error(`[App Error] ${info}:`, err)
  const tg = window.Telegram?.WebApp
  if (tg?.showAlert && import.meta.env.DEV) {
    tg.showAlert(`Xatolik: ${err.message}`)
  }
}

app.mount('#app')

// Auth ni fonda bajarish — UI darhol ko'rinadi
async function initAuth() {
  const { useAuthStore } = await import('@/stores/auth')
  const authStore = useAuthStore()

  // 1) Telegram initData — combined init (auth + categories + vacancies)
  const tg = window.Telegram?.WebApp
  if (tg?.initData && tg?.initDataUnsafe?.user) {
    try {
      await authStore.loginWithTelegram(tg.initData)
      // Hydrate reference store from bootstrap data (avoids separate /categories call)
      if (authStore.bootstrapData?.categories) {
        const { useReferenceStore } = await import('@/stores/reference')
        const refStore = useReferenceStore()
        refStore.categories = authStore.bootstrapData.categories
        refStore.categoriesLoaded = true
      }
      return
    } catch (e) {
      console.error('Auto-login failed:', e)
    }
  }

  // 2) URL dagi auth_token (bot keyboard dan)
  const urlParams = new URLSearchParams(window.location.search)
  const authToken = urlParams.get('auth_token')
  if (authToken) {
    sessionStorage.setItem('url_auth_token', authToken)
    try {
      await authStore.loginWithToken(authToken)
      const cleanUrl = window.location.pathname + window.location.hash
      window.history.replaceState({}, '', cleanUrl)
      return
    } catch (e) {
      console.error('Token auth failed:', e)
    }
  }

  // 3) localStorage dagi token orqali session tiklash
  if (authStore.token) {
    try {
      await authStore.fetchUser()
    } catch (e) {
      console.error('Session restore failed:', e)
    }
  }

  authStore.markAuthReady()
}

initAuth()
