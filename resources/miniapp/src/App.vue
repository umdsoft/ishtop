<template>
  <div id="app" class="min-h-screen">
    <RouterView />
    <BottomNav v-if="showNav" />
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { RouterView, useRoute } from 'vue-router'
import { useTelegram } from '@/composables/useTelegram'
import BottomNav from '@/components/BottomNav.vue'

const route = useRoute()
const telegram = useTelegram()

const showNav = computed(() => {
  const noNavRoutes = ['login', 'questionnaire', 'map', 'vacancy-detail']
  return !noNavRoutes.includes(route.name)
})

onMounted(() => {
  telegram.ready()
  telegram.expand()
})
</script>
