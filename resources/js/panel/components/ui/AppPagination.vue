<template>
  <nav class="flex items-center justify-between" aria-label="Pagination">
    <div class="flex-1 flex justify-between sm:hidden">
      <AppButton
        variant="secondary"
        size="sm"
        :disabled="currentPage === 1"
        @click="goToPage(currentPage - 1)"
      >
        Oldingi
      </AppButton>
      <AppButton
        variant="secondary"
        size="sm"
        :disabled="currentPage === totalPages"
        @click="goToPage(currentPage + 1)"
      >
        Keyingi
      </AppButton>
    </div>

    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
      <div>
        <p class="text-sm text-surface-700 dark:text-surface-300">
          Ko'rsatilmoqda
          <span class="font-medium">{{ fromItem }}</span>
          dan
          <span class="font-medium">{{ toItem }}</span>
          gacha, jami
          <span class="font-medium">{{ total }}</span>
          ta natija
        </p>
      </div>

      <div>
        <nav class="relative z-0 inline-flex rounded-lg shadow-sm -space-x-px" aria-label="Pagination">
          <button
            type="button"
            :disabled="currentPage === 1"
            :class="[
              'relative inline-flex items-center px-2 py-2 rounded-l-lg border border-surface-300 dark:border-surface-600 text-sm font-medium transition-colors',
              currentPage === 1
                ? 'bg-surface-50 dark:bg-surface-900 text-surface-400 dark:text-surface-500 cursor-not-allowed'
                : 'bg-white dark:bg-surface-800 text-surface-700 dark:text-surface-300 hover:bg-surface-50 dark:hover:bg-surface-700',
            ]"
            @click="goToPage(currentPage - 1)"
          >
            <span class="sr-only">Previous</span>
            <ChevronLeftIcon class="h-5 w-5" />
          </button>

          <button
            v-for="page in displayedPages"
            :key="page"
            type="button"
            :class="[
              'relative inline-flex items-center px-4 py-2 border text-sm font-medium transition-colors',
              page === currentPage
                ? 'z-10 bg-brand-50 dark:bg-brand-900/50 border-brand-500 text-brand-600 dark:text-brand-400'
                : 'bg-white dark:bg-surface-800 border-surface-300 dark:border-surface-600 text-surface-700 dark:text-surface-300 hover:bg-surface-50 dark:hover:bg-surface-700',
              page === '...' ? 'cursor-default' : '',
            ]"
            :disabled="page === '...'"
            @click="typeof page === 'number' ? goToPage(page) : null"
          >
            {{ page }}
          </button>

          <button
            type="button"
            :disabled="currentPage === totalPages"
            :class="[
              'relative inline-flex items-center px-2 py-2 rounded-r-lg border border-surface-300 dark:border-surface-600 text-sm font-medium transition-colors',
              currentPage === totalPages
                ? 'bg-surface-50 dark:bg-surface-900 text-surface-400 dark:text-surface-500 cursor-not-allowed'
                : 'bg-white dark:bg-surface-800 text-surface-700 dark:text-surface-300 hover:bg-surface-50 dark:hover:bg-surface-700',
            ]"
            @click="goToPage(currentPage + 1)"
          >
            <span class="sr-only">Next</span>
            <ChevronRightIcon class="h-5 w-5" />
          </button>
        </nav>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { computed } from 'vue';
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/20/solid';
import AppButton from './AppButton.vue';

const props = defineProps({
  currentPage: {
    type: Number,
    required: true,
  },
  total: {
    type: Number,
    required: true,
  },
  perPage: {
    type: Number,
    default: 10,
  },
  maxVisible: {
    type: Number,
    default: 7,
  },
});

const emit = defineEmits(['update:currentPage', 'change']);

const totalPages = computed(() => Math.ceil(props.total / props.perPage));

const fromItem = computed(() => {
  if (props.total === 0) return 0;
  return (props.currentPage - 1) * props.perPage + 1;
});

const toItem = computed(() => {
  const to = props.currentPage * props.perPage;
  return to > props.total ? props.total : to;
});

const displayedPages = computed(() => {
  const pages = [];
  const total = totalPages.value;
  const current = props.currentPage;
  const max = props.maxVisible;

  if (total <= max) {
    for (let i = 1; i <= total; i++) {
      pages.push(i);
    }
  } else {
    const half = Math.floor(max / 2);
    let start = current - half;
    let end = current + half;

    if (start < 1) {
      start = 1;
      end = max;
    }

    if (end > total) {
      end = total;
      start = total - max + 1;
    }

    if (start > 1) {
      pages.push(1);
      if (start > 2) {
        pages.push('...');
      }
    }

    for (let i = start; i <= end; i++) {
      pages.push(i);
    }

    if (end < total) {
      if (end < total - 1) {
        pages.push('...');
      }
      pages.push(total);
    }
  }

  return pages;
});

function goToPage(page) {
  if (page >= 1 && page <= totalPages.value && page !== props.currentPage) {
    emit('update:currentPage', page);
    emit('change', page);
  }
}
</script>
