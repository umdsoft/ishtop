<template>
  <footer class="bg-surface-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Main footer -->
      <div class="py-10 grid grid-cols-2 md:grid-cols-4 gap-8">
        <!-- Brand -->
        <div class="col-span-2 md:col-span-1">
          <router-link to="/" class="flex items-center gap-2.5 mb-3">
            <div class="w-9 h-9 bg-brand-500 rounded-xl flex items-center justify-center flex-shrink-0">
              <svg width="20" height="20" viewBox="0 0 48 48" fill="none">
                <path d="M15 14L15 34" stroke="white" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M15 24L27 14" stroke="white" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M15 24L27 34" stroke="white" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M30 17L35 17L35 31L30 31" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" opacity="0.7"/>
              </svg>
            </div>
            <div class="flex flex-col leading-none">
              <span class="text-[12px] font-extrabold tracking-[0.5px] text-white">KADR</span>
              <span class="text-[12px] font-black tracking-[1px] text-accent-400 flex items-center">GO<span class="w-1 h-1 rounded-full bg-accent-400 ml-1"></span></span>
            </div>
          </router-link>
          <p class="text-surface-400 text-sm leading-relaxed mb-4">{{ $t('footer_description') }}</p>
          <div class="flex items-center gap-2">
            <a href="https://t.me/kadrgobot" target="_blank" class="w-9 h-9 bg-surface-800 hover:bg-brand-500 rounded-lg flex items-center justify-center transition-colors group">
              <svg class="w-4 h-4 text-surface-400 group-hover:text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
            </a>
            <a href="https://instagram.com/kadrgo_uz" target="_blank" class="w-9 h-9 bg-surface-800 hover:bg-brand-500 rounded-lg flex items-center justify-center transition-colors group">
              <svg class="w-4 h-4 text-surface-400 group-hover:text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
            </a>
          </div>
        </div>

        <!-- Platform -->
        <div>
          <h4 class="text-white font-semibold text-sm mb-3">{{ $t('platform') }}</h4>
          <ul class="space-y-2">
            <li><router-link to="/vacancies" class="text-surface-400 hover:text-white text-sm transition-colors">{{ $t('vacancies') }}</router-link></li>
            <li><router-link to="/#categories" class="text-surface-400 hover:text-white text-sm transition-colors">{{ $t('categories') }}</router-link></li>
            <li><a href="/panel" class="text-surface-400 hover:text-white text-sm transition-colors">{{ $t('for_employers') }}</a></li>
            <li><a href="https://t.me/kadrgobot" target="_blank" class="text-surface-400 hover:text-white text-sm transition-colors">{{ $t('telegram_bot') }}</a></li>
          </ul>
        </div>

        <!-- Company -->
        <div>
          <h4 class="text-white font-semibold text-sm mb-3">{{ $t('company') }}</h4>
          <ul class="space-y-2">
            <li><router-link to="/faq" class="text-surface-400 hover:text-white text-sm transition-colors">{{ $t('help') }}</router-link></li>
            <li><a href="https://t.me/kadrgo_support" target="_blank" class="text-surface-400 hover:text-white text-sm transition-colors">{{ $t('contacts') }}</a></li>
          </ul>
        </div>

        <!-- Legal -->
        <div>
          <h4 class="text-white font-semibold text-sm mb-3">{{ $t('legal') }}</h4>
          <ul class="space-y-2">
            <li><router-link to="/terms" class="text-surface-400 hover:text-white text-sm transition-colors">{{ $t('terms') }}</router-link></li>
            <li><router-link to="/privacy" class="text-surface-400 hover:text-white text-sm transition-colors">{{ $t('privacy') }}</router-link></li>
          </ul>
        </div>
      </div>

      <!-- Bottom bar -->
      <div class="border-t border-surface-800 py-5 text-center">
        <span class="text-surface-500 text-xs">&copy; {{ year }} KadrGo. {{ $t('all_rights') }}.</span>
      </div>
    </div>
  </footer>
</template>

<script setup>
const year = new Date().getFullYear();
</script>
