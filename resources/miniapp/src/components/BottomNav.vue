<template>
  <nav class="fixed bottom-0 left-0 right-0 bg-tg-bg border-t border-gray-200 z-50">
    <div class="flex justify-around items-center h-16">
      <RouterLink
        v-for="item in navItems"
        :key="item.name"
        :to="item.to"
        class="flex flex-col items-center justify-center flex-1 h-full transition-colors"
        :class="{ 'text-tg-button': isActive(item.to), 'text-tg-hint': !isActive(item.to) }"
        @click="telegram.hapticFeedback('soft')"
      >
        <component :is="item.icon" class="w-6 h-6" />
        <span class="text-xs mt-1">{{ item.label }}</span>
      </RouterLink>
    </div>
  </nav>
</template>

<script setup>
import { computed } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { useTelegram } from '@/composables/useTelegram'

const route = useRoute()
const telegram = useTelegram()

const navItems = [
  {
    name: 'home',
    to: '/',
    label: 'Asosiy',
    icon: 'IconHome',
  },
  {
    name: 'search',
    to: '/search',
    label: 'Qidirish',
    icon: 'IconSearch',
  },
  {
    name: 'applications',
    to: '/applications',
    label: 'Arizalar',
    icon: 'IconClipboard',
  },
  {
    name: 'profile',
    to: '/profile',
    label: 'Profil',
    icon: 'IconUser',
  },
]

const isActive = (path) => {
  if (path === '/') {
    return route.path === '/'
  }
  return route.path.startsWith(path)
}
</script>

<script>
// Simple icon components
export const IconHome = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
    </svg>
  `
}

export const IconSearch = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
    </svg>
  `
}

export const IconClipboard = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
    </svg>
  `
}

export const IconUser = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
    </svg>
  `
}
</script>
