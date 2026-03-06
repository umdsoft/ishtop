<template>
  <nav class="fixed bottom-0 left-0 right-0 z-50 safe-area-bottom" style="background-color: var(--tg-theme-bg-color); border-top: 1px solid var(--separator-color);">
    <div class="flex justify-around items-center h-14">
      <RouterLink
        v-for="item in navItems"
        :key="item.name"
        :to="item.to"
        class="flex flex-col items-center justify-center flex-1 h-full transition-all duration-200"
        @click="telegram.hapticFeedback('soft')"
      >
        <!-- Active state: filled icon -->
        <svg
          v-if="isActive(item.to)"
          class="w-[22px] h-[22px]"
          style="color: var(--tg-theme-button-color);"
          viewBox="0 0 24 24"
          fill="currentColor"
          v-html="item.filledPath"
        />
        <!-- Inactive state: outlined icon -->
        <svg
          v-else
          class="w-[22px] h-[22px]"
          style="color: var(--tg-theme-hint-color);"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="1.8"
          v-html="item.outlinedPath"
        />
        <span
          class="text-[10px] mt-0.5 font-medium"
          :style="{ color: isActive(item.to) ? 'var(--tg-theme-button-color)' : 'var(--tg-theme-hint-color)' }"
        >
          {{ item.label }}
        </span>
      </RouterLink>
    </div>
  </nav>
</template>

<script setup>
import { computed } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { useTelegram } from '@/composables/useTelegram'
import { useLocale } from '@/composables/useLocale'

const route = useRoute()
const telegram = useTelegram()
const { t } = useLocale()

const navItems = computed(() => [
  {
    name: 'home',
    to: '/',
    label: t('nav.home'),
    filledPath: '<path d="M11.47 3.84a.75.75 0 011.06 0l8.69 8.69a.75.75 0 11-1.06 1.06l-.22-.22V19.5a1.5 1.5 0 01-1.5 1.5h-3.75a.75.75 0 01-.75-.75v-4.5a.75.75 0 00-.75-.75h-1.5a.75.75 0 00-.75.75v4.5a.75.75 0 01-.75.75H6.56a1.5 1.5 0 01-1.5-1.5v-6.13l-.22.22a.75.75 0 01-1.06-1.06l8.69-8.69z"/>',
    outlinedPath: '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>',
  },
  {
    name: 'search',
    to: '/search',
    label: t('nav.search'),
    filledPath: '<path fill-rule="evenodd" d="M10.5 3.75a6.75 6.75 0 100 13.5 6.75 6.75 0 000-13.5zM2.25 10.5a8.25 8.25 0 1114.59 5.28l4.69 4.69a.75.75 0 11-1.06 1.06l-4.69-4.69A8.25 8.25 0 012.25 10.5z" clip-rule="evenodd"/>',
    outlinedPath: '<path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>',
  },
  {
    name: 'top',
    to: '/search?is_top=true',
    label: t('nav.top'),
    filledPath: '<path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd"/>',
    outlinedPath: '<path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/>',
  },
  {
    name: 'applications',
    to: '/applications',
    label: t('nav.applications'),
    filledPath: '<path fill-rule="evenodd" d="M7.502 6h7.128A3.375 3.375 0 0118 9.375v9.375a3 3 0 003-3V6.108c0-1.505-1.125-2.811-2.664-2.94a48.972 48.972 0 00-8.583-.058A2.86 2.86 0 007.502 6zM13.5 3A2.25 2.25 0 0011.25.75h-1.5A2.25 2.25 0 007.5 3h6z" clip-rule="evenodd"/><path fill-rule="evenodd" d="M3 9.375C3 8.339 3.84 7.5 4.875 7.5h9.75c1.036 0 1.875.84 1.875 1.875v11.25c0 1.035-.84 1.875-1.875 1.875h-9.75A1.875 1.875 0 013 20.625V9.375zm9.586 4.594a.75.75 0 00-1.172-.938l-2.476 3.096-.908-.907a.75.75 0 00-1.06 1.06l1.5 1.5a.75.75 0 001.116-.062l3-3.75z" clip-rule="evenodd"/>',
    outlinedPath: '<path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15a2.25 2.25 0 011.65 1.586m-5.8 0c-.376.023-.75.05-1.124.08C8.318 4.046 7.5 5.236 7.5 6.578v9.672A2.25 2.25 0 009.75 18.5h6a2.25 2.25 0 002.25-2.25V6.578c0-1.342-.818-2.532-2.226-2.662a41.461 41.461 0 00-1.124-.08m-5.8 0a41.926 41.926 0 00-1.35.091C6.318 4.046 5.5 5.236 5.5 6.578V18.5a2.25 2.25 0 002.25 2.25h.75"/>',
  },
  {
    name: 'profile',
    to: '/profile',
    label: t('nav.profile'),
    filledPath: '<path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM3.751 20.105a8.25 8.25 0 0116.498 0 .75.75 0 01-.437.695A18.683 18.683 0 0112 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 01-.437-.695z" clip-rule="evenodd"/>',
    outlinedPath: '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>',
  },
])

const isActive = (to) => {
  if (to === '/') {
    return route.path === '/' && !route.query.is_top
  }
  if (to === '/search?is_top=true') {
    return route.path === '/search' && route.query.is_top === 'true'
  }
  if (to === '/search') {
    return route.path.startsWith('/search') && route.query.is_top !== 'true'
  }
  return route.path.startsWith(to)
}
</script>

<style scoped>
.safe-area-bottom {
  padding-bottom: env(safe-area-inset-bottom, 0px);
}
</style>
