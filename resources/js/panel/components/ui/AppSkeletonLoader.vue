<template>
  <div
    :class="[
      'animate-pulse bg-surface-200 dark:bg-surface-800',
      roundedClasses,
      customClass || sizeClasses,
    ]"
    :style="customStyle"
  />
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  type: {
    type: String,
    default: 'text',
    validator: (value) => ['text', 'title', 'avatar', 'thumbnail', 'card', 'button', 'custom'].includes(value),
  },
  rounded: {
    type: String,
    default: 'md',
    validator: (value) => ['none', 'sm', 'md', 'lg', 'full'].includes(value),
  },
  customClass: {
    type: String,
    default: '',
  },
  customStyle: {
    type: Object,
    default: () => ({}),
  },
});

const roundedClasses = computed(() => {
  const rounded = {
    none: 'rounded-none',
    sm: 'rounded-sm',
    md: 'rounded-md',
    lg: 'rounded-lg',
    full: 'rounded-full',
  };
  return rounded[props.rounded];
});

const sizeClasses = computed(() => {
  const types = {
    text: 'h-4 w-full',
    title: 'h-8 w-3/4',
    avatar: 'h-10 w-10 rounded-full',
    thumbnail: 'h-24 w-24',
    card: 'h-48 w-full',
    button: 'h-10 w-24',
    custom: '',
  };
  return types[props.type];
});
</script>
