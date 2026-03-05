<template>
  <div>
    <div v-if="label || $slots.label" class="flex justify-between items-center mb-2">
      <slot name="label">
        <span class="text-sm font-medium text-surface-700 dark:text-surface-300">{{ label }}</span>
      </slot>
      <span v-if="showPercentage" class="text-sm font-medium text-surface-600 dark:text-surface-400">
        {{ percentage }}%
      </span>
    </div>

    <div
      :class="[
        'w-full bg-surface-200 dark:bg-surface-800 overflow-hidden',
        roundedClasses,
        heightClasses,
      ]"
    >
      <div
        :class="[
          'h-full transition-all duration-500 ease-out',
          colorClasses,
          striped ? 'bg-stripes' : '',
          animated && striped ? 'animate-stripes' : '',
        ]"
        :style="{ width: `${percentage}%` }"
      />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  value: {
    type: Number,
    required: true,
  },
  max: {
    type: Number,
    default: 100,
  },
  label: {
    type: String,
    default: '',
  },
  showPercentage: {
    type: Boolean,
    default: true,
  },
  color: {
    type: String,
    default: 'brand',
    validator: (value) => ['brand', 'success', 'warning', 'danger', 'info', 'surface'].includes(value),
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl'].includes(value),
  },
  rounded: {
    type: String,
    default: 'full',
    validator: (value) => ['none', 'sm', 'md', 'lg', 'full'].includes(value),
  },
  striped: {
    type: Boolean,
    default: false,
  },
  animated: {
    type: Boolean,
    default: false,
  },
});

const percentage = computed(() => {
  const value = Math.min(Math.max(props.value, 0), props.max);
  return Math.round((value / props.max) * 100);
});

const heightClasses = computed(() => {
  const heights = {
    xs: 'h-1',
    sm: 'h-2',
    md: 'h-3',
    lg: 'h-4',
    xl: 'h-6',
  };
  return heights[props.size];
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
    brand: 'bg-brand-600',
    success: 'bg-success-600',
    warning: 'bg-warning-600',
    danger: 'bg-danger-600',
    info: 'bg-info-600',
    surface: 'bg-surface-600',
  };
  return colors[props.color];
});
</script>

<style scoped>
.bg-stripes {
  background-image: linear-gradient(
    45deg,
    rgba(255, 255, 255, 0.15) 25%,
    transparent 25%,
    transparent 50%,
    rgba(255, 255, 255, 0.15) 50%,
    rgba(255, 255, 255, 0.15) 75%,
    transparent 75%,
    transparent
  );
  background-size: 1rem 1rem;
}

@keyframes stripes {
  0% {
    background-position: 0 0;
  }
  100% {
    background-position: 1rem 0;
  }
}

.animate-stripes {
  animation: stripes 1s linear infinite;
}
</style>
