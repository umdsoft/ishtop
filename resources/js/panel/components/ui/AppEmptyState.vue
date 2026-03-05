<template>
  <div class="text-center py-12 px-4">
    <component
      v-if="icon"
      :is="icon"
      class="mx-auto h-12 w-12 text-surface-400 dark:text-surface-500 mb-4"
    />
    <svg
      v-else
      class="mx-auto h-12 w-12 text-surface-400 dark:text-surface-500 mb-4"
      fill="none"
      viewBox="0 0 24 24"
      stroke="currentColor"
    >
      <path
        stroke-linecap="round"
        stroke-linejoin="round"
        stroke-width="2"
        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
      />
    </svg>

    <h3 class="text-lg font-medium text-surface-900 dark:text-surface-100 mb-2">
      {{ title }}
    </h3>

    <p class="text-sm text-surface-500 dark:text-surface-400 mb-6 max-w-md mx-auto">
      {{ description }}
    </p>

    <div v-if="$slots.action || action" class="flex justify-center gap-3">
      <slot name="action">
        <AppButton
          v-if="action"
          :variant="actionVariant"
          :size="actionSize"
          @click="$emit('action')"
        >
          {{ action }}
        </AppButton>
      </slot>
    </div>
  </div>
</template>

<script setup>
import AppButton from './AppButton.vue';

defineProps({
  icon: {
    type: Object,
    default: null,
  },
  title: {
    type: String,
    default: 'Hech narsa topilmadi',
  },
  description: {
    type: String,
    default: 'Ushbu sahifada hozircha ma\'lumot yo\'q',
  },
  action: {
    type: String,
    default: '',
  },
  actionVariant: {
    type: String,
    default: 'primary',
  },
  actionSize: {
    type: String,
    default: 'md',
  },
});

defineEmits(['action']);
</script>
