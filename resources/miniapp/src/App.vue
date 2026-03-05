<template>
  <div id="app" class="min-h-screen">
    <RouterView />
    <BottomNav v-if="isAuthenticated && showNav" />
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { RouterView, useRoute } from 'vue-router'
import { useTelegram } from '@/composables/useTelegram'
import { useAuthStore } from '@/stores/auth'
import BottomNav from '@/components/BottomNav.vue'

const route = useRoute()
const telegram = useTelegram()
const authStore = useAuthStore()

const isAuthenticated = computed(() => authStore.isAuthenticated)

const showNav = computed(() => {
  const noNavRoutes = ['login', 'questionnaire']
  return !noNavRoutes.includes(route.name)
})

onMounted(async () => {
  telegram.ready()
  telegram.expand()

  // Auto-authenticate if telegram data is available
  if (telegram.initDataUnsafe && !authStore.isAuthenticated) {
    await authStore.loginWithTelegram(telegram.initData)
  }
})
</script>
