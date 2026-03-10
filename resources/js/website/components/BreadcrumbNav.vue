<template>
  <nav class="flex items-center gap-1.5 text-sm text-surface-400">
    <template v-for="(item, index) in items" :key="index">
      <router-link
        v-if="item.to"
        :to="item.to"
        class="hover:text-brand-500 transition-colors"
      >
        {{ item.label }}
      </router-link>
      <span v-else class="text-surface-700 font-medium truncate max-w-[200px]">{{ item.label }}</span>

      <svg
        v-if="index < items.length - 1"
        class="w-3.5 h-3.5 flex-shrink-0"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        viewBox="0 0 24 24"
      >
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
      </svg>
    </template>
  </nav>
</template>

<script setup>
defineProps({
  items: { type: Array, required: true },
});
</script>
