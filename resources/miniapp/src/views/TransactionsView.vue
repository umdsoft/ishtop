<template>
  <div class="pb-20" style="background-color: var(--tg-theme-bg-color);">
    <!-- Header -->
    <div class="px-5 pt-6 pb-4">
      <h1 class="text-[22px] font-bold tracking-tight" style="color: var(--tg-theme-text-color);">
        {{ t('transactions.title') }}
      </h1>
    </div>

    <!-- Loading -->
    <LoadingSpinner v-if="loading && transactions.length === 0" />

    <!-- Empty state -->
    <div v-else-if="transactions.length === 0" class="px-5 py-16 text-center">
      <svg class="w-12 h-12 mx-auto mb-3 opacity-30" style="color: var(--tg-theme-hint-color);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
      </svg>
      <p class="text-[14px]" style="color: var(--tg-theme-hint-color);">
        {{ t('transactions.empty') }}
      </p>
    </div>

    <!-- Transactions list -->
    <div v-else class="px-5 space-y-2">
      <div
        v-for="tx in transactions"
        :key="tx.id"
        class="flex items-center gap-3 p-3.5 rounded-2xl"
        style="background-color: var(--tg-theme-secondary-bg-color);"
      >
        <!-- Icon -->
        <div
          class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
          :style="{ backgroundColor: tx.type === 'balance_topup' ? 'rgba(52,199,89,0.12)' : 'rgba(255,59,48,0.12)' }"
        >
          <svg v-if="tx.type === 'balance_topup'" class="w-5 h-5" style="color: #34c759;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m0 0l6.75-6.75M12 19.5l-6.75-6.75" />
          </svg>
          <svg v-else class="w-5 h-5" style="color: #ff3b30;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 19.5v-15m0 0l-6.75 6.75M12 4.5l6.75 6.75" />
          </svg>
        </div>

        <!-- Info -->
        <div class="flex-1 min-w-0">
          <div class="text-[14px] font-semibold truncate" style="color: var(--tg-theme-text-color);">
            {{ tx.type_label }}
          </div>
          <div v-if="tx.description" class="text-[12px] truncate mt-0.5" style="color: var(--tg-theme-hint-color);">
            {{ tx.description }}
          </div>
          <div class="text-[11px] mt-0.5" style="color: var(--tg-theme-hint-color);">
            {{ formatDate(tx.created_at) }}
          </div>
        </div>

        <!-- Amount -->
        <div class="text-right flex-shrink-0">
          <span
            class="text-[15px] font-bold"
            :style="{ color: tx.type === 'balance_topup' ? '#34c759' : '#ff3b30' }"
          >
            {{ tx.type === 'balance_topup' ? '+' : '-' }}{{ formatNumber(Math.round(tx.amount)) }}
          </span>
          <div class="text-[11px]" style="color: var(--tg-theme-hint-color);">{{ t('common.som') }}</div>
        </div>
      </div>

      <!-- Load more -->
      <div v-if="hasMore" class="py-3 text-center">
        <button
          class="text-[13px] font-semibold px-5 py-2 rounded-xl"
          style="color: var(--tg-theme-button-color);"
          :disabled="loading"
          @click="loadMore"
        >
          {{ loading ? '...' : t('transactions.load_more') }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useLocale } from '@/composables/useLocale'
import { formatNumber } from '@/utils/formatters'
import LoadingSpinner from '@/components/LoadingSpinner.vue'
import api from '@/utils/api'

const { t } = useLocale()

const transactions = ref([])
const loading = ref(false)
const page = ref(1)
const hasMore = ref(false)

onMounted(() => loadTransactions())

async function loadTransactions() {
  loading.value = true
  try {
    const res = await api.get('/payments/history', { params: { page: page.value, per_page: 20 } })
    const data = res.data
    transactions.value.push(...data.data)
    hasMore.value = data.current_page < data.last_page
  } catch {
    // silent
  } finally {
    loading.value = false
  }
}

function loadMore() {
  page.value++
  loadTransactions()
}

function formatDate(dateStr) {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  const day = String(d.getDate()).padStart(2, '0')
  const month = String(d.getMonth() + 1).padStart(2, '0')
  const year = d.getFullYear()
  const hours = String(d.getHours()).padStart(2, '0')
  const mins = String(d.getMinutes()).padStart(2, '0')
  return `${day}.${month}.${year}, ${hours}:${mins}`
}
</script>
