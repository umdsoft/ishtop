<template>
  <nav class="bg-white border-b border-surface-100 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">
        <!-- Logo -->
        <router-link to="/" class="flex items-center gap-2.5">
          <div class="w-10 h-10 bg-brand-500 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg width="22" height="22" viewBox="0 0 48 48" fill="none">
              <path d="M15 14L15 34" stroke="white" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M15 24L27 14" stroke="white" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M15 24L27 34" stroke="white" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M30 17L35 17L35 31L30 31" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" opacity="0.7"/>
            </svg>
          </div>
          <div class="flex flex-col leading-none">
            <span class="text-[13px] font-extrabold tracking-[0.5px] text-brand-500">KADR</span>
            <span class="text-[13px] font-black tracking-[1px] text-accent-500 flex items-center">GO<span class="w-[5px] h-[5px] rounded-full bg-accent-500 ml-1"></span></span>
          </div>
        </router-link>

        <!-- Desktop nav -->
        <div class="hidden md:flex items-center gap-6">
          <router-link to="/vacancies" class="text-surface-600 hover:text-brand-500 font-medium transition-colors">
            {{ $t('vacancies') }}
          </router-link>

          <LangSwitcher />

          <a href="/panel"
             class="inline-flex items-center gap-1.5 text-surface-600 hover:text-brand-500 font-medium transition-colors text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            {{ $t('for_employers') }}
          </a>

          <a href="https://t.me/kadrgobot" target="_blank"
             class="inline-flex items-center gap-2 bg-brand-500 hover:bg-brand-600 text-white px-4 py-2 rounded-xl font-medium transition-colors text-sm">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
            {{ $t('telegram_bot') }}
          </a>
        </div>

        <!-- Mobile menu button -->
        <button @click="mobileOpen = !mobileOpen" class="md:hidden p-2 text-surface-600">
          <svg v-if="!mobileOpen" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
          <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- Mobile menu -->
    <transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0 -translate-y-2"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 -translate-y-2"
    >
      <div v-if="mobileOpen" class="md:hidden border-t border-surface-100 bg-white">
        <div class="px-4 py-4 space-y-3">
          <router-link
            to="/vacancies"
            class="block text-surface-700 hover:text-brand-500 font-medium py-2"
            @click="mobileOpen = false"
          >
            {{ $t('vacancies') }}
          </router-link>

          <a
            href="/panel"
            class="block text-surface-700 hover:text-brand-500 font-medium py-2"
            @click="mobileOpen = false"
          >
            {{ $t('for_employers') }}
          </a>

          <div class="flex items-center gap-2 py-2">
            <LangSwitcher />
          </div>

          <a href="https://t.me/kadrgobot" target="_blank"
             class="flex items-center justify-center gap-2 bg-brand-500 hover:bg-brand-600 text-white px-4 py-3 rounded-xl font-medium transition-colors text-sm">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
            {{ $t('telegram_bot') }}
          </a>
        </div>
      </div>
    </transition>
  </nav>
</template>

<script setup>
import { ref } from 'vue';
import LangSwitcher from '@website/components/LangSwitcher.vue';

const mobileOpen = ref(false);
</script>
