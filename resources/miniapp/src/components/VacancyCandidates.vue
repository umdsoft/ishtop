<template>
  <div>
    <div class="px-5 py-4">
      <div class="section-header mb-3">
        <div class="section-icon" style="background-color: rgba(16, 185, 129, 0.1);">
          <svg class="w-4 h-4" style="color: #10b981;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
          </svg>
        </div>
        <h2 class="section-title">{{ t('vacancy.own_candidates_title') }}</h2>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="py-6 text-center">
        <LoadingSpinner />
      </div>

      <!-- Empty -->
      <div v-else-if="totalCount === 0" class="py-4 text-center">
        <div class="w-14 h-14 mx-auto mb-3 rounded-2xl flex items-center justify-center" style="background-color: var(--tg-theme-secondary-bg-color);">
          <svg class="w-7 h-7" style="color: var(--tg-theme-hint-color);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
          </svg>
        </div>
        <p class="text-[13px]" style="color: var(--tg-theme-hint-color);">{{ t('vacancy.own_candidates_empty') }}</p>
      </div>

      <!-- Locked — Paywall -->
      <div v-else-if="locked" class="paywall-card">
        <div class="paywall-icon-wrap">
          <svg class="w-8 h-8" style="color: var(--tg-theme-button-color);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
          </svg>
        </div>
        <p class="text-[16px] font-bold mt-3" style="color: var(--tg-theme-text-color);">
          {{ t('vacancy.own_candidates_found').replace('{count}', totalCount) }}
        </p>
        <p class="text-[12px] mt-1 mb-4" style="color: var(--tg-theme-hint-color);">
          {{ t('vacancy.own_candidates_locked_hint') }}
        </p>
        <button
          class="paywall-btn"
          @click="$emit('unlock')"
          :disabled="unlocking"
        >
          <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5V6.75a4.5 4.5 0 119 0v3.75M3.75 21.75h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H3.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
          </svg>
          <span v-if="unlocking">...</span>
          <span v-else>{{ t('vacancy.own_candidates_unlock') }}</span>
        </button>
        <p class="text-[12px] mt-2 font-semibold" style="color: var(--tg-theme-button-color);">
          {{ t('vacancy.own_candidates_unlock_price').replace('{price}', formatNumber(unlockPrice)) }}
        </p>
      </div>

      <!-- Unlocked — Candidates list -->
      <div v-else class="space-y-2">
        <div v-for="candidate in candidates" :key="candidate.id"
             class="candidate-card cursor-pointer active:opacity-70 transition-opacity"
             @click="viewCandidate(candidate)">
          <div class="flex items-center gap-3">
            <div
              class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 overflow-hidden"
              style="background-color: rgba(var(--tg-button-rgb, 13,148,136), 0.12);"
            >
              <img v-if="candidate.photo_url" :src="candidate.photo_url" class="w-10 h-10 rounded-full object-cover" />
              <span v-else class="text-[13px] font-bold" style="color: var(--tg-theme-button-color);">
                {{ getInitial(candidate.full_name) }}
              </span>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-[13px] font-semibold truncate" style="color: var(--tg-theme-text-color);">{{ candidate.full_name }}</p>
              <p class="text-[11px] truncate" style="color: var(--tg-theme-hint-color);">
                {{ candidate.specialty || '' }}
                <span v-if="candidate.city"> · {{ candidate.district || candidate.city }}</span>
                <span v-if="candidate.experience_years"> · {{ candidate.experience_years }} {{ t('vacancy.own_candidates_exp') }}</span>
              </p>
            </div>
            <div v-if="candidate.match_score" class="candidate-score">
              {{ candidate.match_score }}%
            </div>
            <svg class="w-4 h-4 flex-shrink-0" style="color: var(--tg-theme-hint-color);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
            </svg>
          </div>
        </div>
      </div>
    </div>
    <div class="h-2" style="background-color: var(--tg-theme-secondary-bg-color);"></div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { useLocale } from '@/composables/useLocale'
import { formatNumber, getInitial } from '@/utils/formatters'
import LoadingSpinner from '@/components/LoadingSpinner.vue'

const router = useRouter()
const { t } = useLocale()

defineProps({
  candidates: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
  locked: { type: Boolean, default: true },
  totalCount: { type: Number, default: 0 },
  unlockPrice: { type: Number, default: 0 },
  unlocking: { type: Boolean, default: false },
})

defineEmits(['unlock'])

function viewCandidate(candidate) {
  router.push({ path: `/candidates/${candidate.id}`, query: { score: candidate.match_score } })
}
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

.candidate-card {
  padding: 10px 12px;
  border-radius: 12px;
  background-color: var(--tg-theme-secondary-bg-color);
}

.candidate-score {
  padding: 4px 8px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 700;
  background-color: rgba(16, 185, 129, 0.12);
  color: #10b981;
  flex-shrink: 0;
}

.paywall-card {
  text-align: center;
  padding: 28px 20px;
  border-radius: 16px;
  background: linear-gradient(135deg, rgba(var(--tg-button-rgb, 13,148,136), 0.06) 0%, rgba(16, 185, 129, 0.06) 100%);
  border: 1px solid rgba(var(--tg-button-rgb, 13,148,136), 0.12);
}

.paywall-icon-wrap {
  width: 56px;
  height: 56px;
  margin: 0 auto;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: rgba(var(--tg-button-rgb, 13,148,136), 0.1);
}

.paywall-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 28px;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 700;
  background-color: var(--tg-theme-button-color);
  color: var(--tg-theme-button-text-color);
  transition: transform 0.15s;
}
.paywall-btn:active {
  transform: scale(0.97);
}
.paywall-btn:disabled {
  opacity: 0.6;
}
</style>
