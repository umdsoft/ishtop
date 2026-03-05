<template>
  <div :class="['flex items-center justify-center', wrapperClass]">
    <div
      :class="[
        'animate-spin rounded-full border-2 border-surface-200 dark:border-surface-700',
        sizeClasses,
        colorClasses,
      ]"
    />
    <span v-if="text" :class="['ml-3 text-sm text-surface-600 dark:text-surface-400']">
      {{ text }}
    </span>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl'].includes(value),
  },
  color: {
    type: String,
    default: 'brand',
    validator: (value) => ['brand', 'white', 'surface'].includes(value),
  },
  text: {
    type: String,
    default: '',
  },
  wrapperClass: {
    type: String,
    default: '',
  },
});

const sizeClasses = computed(() => {
  const sizes = {
    xs: 'h-4 w-4',
    sm: 'h-5 w-5',
    md: 'h-8 w-8',
    lg: 'h-12 w-12',
    xl: 'h-16 w-16',
  };
  return sizes[props.size];
});

const colorClasses = computed(() => {
  const colors = {
    brand: 'border-t-brand-600',
    white: 'border-t-white',
    surface: 'border-t-surface-600',
  };
  return colors[props.color];
});
</script>
