<template>
  <span :class="badgeClasses">
    <slot />
  </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'primary', 'success', 'warning', 'danger', 'info'].includes(value),
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value),
  },
  rounded: {
    type: String,
    default: 'full',
    validator: (value) => ['md', 'lg', 'full'].includes(value),
  },
  dot: {
    type: Boolean,
    default: false,
  },
});

const badgeClasses = computed(() => {
  const classes = ['inline-flex items-center gap-1.5 font-medium'];

  // Size
  const sizeClasses = {
    sm: 'px-2 py-0.5 text-xs',
    md: 'px-2.5 py-1 text-xs',
    lg: 'px-3 py-1.5 text-sm',
  };
  classes.push(sizeClasses[props.size]);

  // Variant
  const variantClasses = {
    default: 'bg-surface-100 text-surface-700 dark:bg-surface-800 dark:text-surface-300',
    primary: 'bg-brand-100 text-brand-700 dark:bg-brand-900/50 dark:text-brand-300',
    success: 'bg-success-100 text-success-700 dark:bg-success-900/50 dark:text-success-300',
    warning: 'bg-warning-100 text-warning-700 dark:bg-warning-900/50 dark:text-warning-300',
    danger: 'bg-danger-100 text-danger-700 dark:bg-danger-900/50 dark:text-danger-300',
    info: 'bg-info-100 text-info-700 dark:bg-info-900/50 dark:text-info-300',
  };
  classes.push(variantClasses[props.variant]);

  // Rounded
  const roundedClasses = {
    md: 'rounded-md',
    lg: 'rounded-lg',
    full: 'rounded-full',
  };
  classes.push(roundedClasses[props.rounded]);

  return classes.join(' ');
});
</script>
