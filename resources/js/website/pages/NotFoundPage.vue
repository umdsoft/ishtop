<template>
  <div class="flex flex-col items-center justify-center min-h-[60vh] px-4">
    <div class="text-8xl font-black text-surface-200">404</div>
    <p class="text-surface-600 text-lg mt-4">Sahifa topilmadi</p>
    <router-link
      to="/"
      class="mt-6 inline-flex items-center gap-2 bg-brand-500 hover:bg-brand-600 text-white px-6 py-2.5 rounded-xl font-medium transition-colors"
    >
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
      </svg>
      {{ $t('home') }}
    </router-link>
  </div>
</template>
