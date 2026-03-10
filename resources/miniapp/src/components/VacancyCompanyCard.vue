<template>
  <div class="px-5 py-4">
    <div class="section-header mb-3">
      <div class="section-icon" style="background-color: rgba(139, 92, 246, 0.1);">
        <svg class="w-4 h-4" style="color: #8b5cf6;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
        </svg>
      </div>
      <h2 class="section-title">{{ t('vacancy.about_company') }}</h2>
    </div>
    <div class="company-card">
      <div class="flex items-center gap-3">
        <img
          v-if="employer.logo_url"
          :src="employer.logo_url"
          class="w-12 h-12 rounded-xl object-cover flex-shrink-0"
        />
        <div
          v-else
          class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0"
          style="background-color: rgba(var(--tg-button-rgb, 13,148,136), 0.12);"
        >
          <span class="text-[15px] font-bold" style="color: var(--tg-theme-button-color);">
            {{ getInitial(employer.company_name) }}
          </span>
        </div>
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-1.5">
            <p class="font-semibold text-[14px] truncate" style="color: var(--tg-theme-text-color);">
              {{ employer.company_name }}
            </p>
            <svg v-if="employer.verification_level === 'verified'" class="w-4 h-4 flex-shrink-0" viewBox="0 0 20 20" fill="var(--tg-theme-button-color)">
              <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
          </div>
          <div v-if="employer.rating" class="flex items-center gap-1 mt-0.5">
            <svg class="w-3.5 h-3.5" fill="#f59e0b" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            <span class="text-[12px] font-medium" style="color: var(--tg-theme-text-color);">{{ employer.rating }}</span>
            <span v-if="employer.rating_count" class="text-[11px]" style="color: var(--tg-theme-hint-color);">({{ employer.rating_count }})</span>
          </div>
        </div>
      </div>
      <p v-if="employer.description" class="text-[12px] mt-3 leading-relaxed" style="color: var(--tg-theme-hint-color);">
        {{ employer.description }}
      </p>
    </div>
  </div>
</template>

<script setup>
import { useLocale } from '@/composables/useLocale'
import { getInitial } from '@/utils/formatters'

const { t } = useLocale()

defineProps({
  employer: { type: Object, required: true },
})
</script>

<style scoped>
.section-header {
  display: flex;
  align-items: center;
  gap: 10px;
}

.section-icon {
  width: 28px;
  height: 28px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.section-title {
  font-size: 15px;
  font-weight: 700;
  color: var(--tg-theme-text-color);
}

.company-card {
  border-radius: 14px;
  padding: 14px;
  background-color: var(--tg-theme-secondary-bg-color);
}
</style>
