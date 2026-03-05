<template>
  <div :class="['relative inline-block', sizeClasses]">
    <img
      v-if="src && !imageError"
      :src="src"
      :alt="alt"
      :class="['object-cover', roundedClasses, sizeClasses]"
      @error="handleImageError"
    />
    <div
      v-else
      :class="[
        'flex items-center justify-center font-semibold',
        roundedClasses,
        sizeClasses,
        colorClasses,
      ]"
    >
      <span :class="textSizeClasses">{{ initials }}</span>
    </div>

    <span
      v-if="status"
      :class="[
        'absolute rounded-full ring-2 ring-white dark:ring-surface-900',
        statusClasses,
        statusPositionClasses,
      ]"
    />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  src: {
    type: String,
    default: '',
  },
  alt: {
    type: String,
    default: 'Avatar',
  },
  name: {
    type: String,
    default: '',
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl', '2xl'].includes(value),
  },
  rounded: {
    type: String,
    default: 'full',
    validator: (value) => ['none', 'sm', 'md', 'lg', 'full'].includes(value),
  },
  status: {
    type: String,
    default: null,
    validator: (value) => !value || ['online', 'offline', 'away', 'busy'].includes(value),
  },
  color: {
    type: String,
    default: 'brand',
    validator: (value) => ['brand', 'surface', 'success', 'warning', 'danger', 'info'].includes(value),
  },
});

const imageError = ref(false);

const initials = computed(() => {
  if (!props.name) return '?';

  const parts = props.name.trim().split(' ');
  if (parts.length >= 2) {
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
  }
  return props.name.substring(0, 2).toUpperCase();
});

const sizeClasses = computed(() => {
  const sizes = {
    xs: 'h-6 w-6',
    sm: 'h-8 w-8',
    md: 'h-10 w-10',
    lg: 'h-12 w-12',
    xl: 'h-16 w-16',
    '2xl': 'h-20 w-20',
  };
  return sizes[props.size];
});

const textSizeClasses = computed(() => {
  const sizes = {
    xs: 'text-xs',
    sm: 'text-xs',
    md: 'text-sm',
    lg: 'text-base',
    xl: 'text-xl',
    '2xl': 'text-2xl',
  };
  return sizes[props.size];
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

const colorClasses = computed(() => {
  const colors = {
    brand: 'bg-brand-100 text-brand-700 dark:bg-brand-900/50 dark:text-brand-300',
    surface: 'bg-surface-100 text-surface-700 dark:bg-surface-800 dark:text-surface-300',
    success: 'bg-success-100 text-success-700 dark:bg-success-900/50 dark:text-success-300',
    warning: 'bg-warning-100 text-warning-700 dark:bg-warning-900/50 dark:text-warning-300',
    danger: 'bg-danger-100 text-danger-700 dark:bg-danger-900/50 dark:text-danger-300',
    info: 'bg-info-100 text-info-700 dark:bg-info-900/50 dark:text-info-300',
  };
  return colors[props.color];
});

const statusClasses = computed(() => {
  if (!props.status) return '';

  const statuses = {
    online: 'bg-success-500',
    offline: 'bg-surface-400',
    away: 'bg-warning-500',
    busy: 'bg-danger-500',
  };
  return statuses[props.status];
});

const statusPositionClasses = computed(() => {
  const positions = {
    xs: 'h-1.5 w-1.5 bottom-0 right-0',
    sm: 'h-2 w-2 bottom-0 right-0',
    md: 'h-2.5 w-2.5 bottom-0 right-0',
    lg: 'h-3 w-3 bottom-0 right-0',
    xl: 'h-4 w-4 bottom-0 right-0',
    '2xl': 'h-5 w-5 bottom-0 right-0',
  };
  return positions[props.size];
});

function handleImageError() {
  imageError.value = true;
}
</script>
