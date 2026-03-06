import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import './style.css'

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)

// Initialize auth BEFORE router — prevents deadlock
async function initAuth() {
  const { useAuthStore } = await import('@/stores/auth')
  const authStore = useAuthStore()

  const tg = window.Telegram?.WebApp
  const initData = tg?.initData
  const tgUser = tg?.initDataUnsafe?.user

  if (tgUser && initData) {
    try {
      await authStore.loginWithTelegram(initData)
    } catch (e) {
      console.error('Auto-login failed:', e)
      authStore.markAuthReady()
    }
  } else if (authStore.token) {
    try {
      await authStore.fetchUser()
    } catch (e) {
      console.error('Session restore failed:', e)
    }
    authStore.markAuthReady()
  } else {
    authStore.markAuthReady()
  }
}

initAuth().finally(() => {
  app.use(router)
  app.mount('#app')
})
