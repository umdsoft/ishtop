<template>
  <component
    :is="tag"
    :type="nativeType"
    :disabled="disabled || loading"
    :class="buttonClasses"
    @click="handleClick"
  >
    <span v-if="loading" class="absolute inset-0 flex items-center justify-center">
      <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
    </span>
    <span :class="{ 'opacity-0': loading }" class="flex items-center justify-center gap-2">
      <slot name="icon-left" />
      <slot />
      <slot name="icon-right" />
    </span>
  </component>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'secondary', 'ghost', 'danger', 'success', 'outline'].includes(value),
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl'].includes(value),
  },
  tag: {
    type: String,
    default: 'button',
  },
  nativeType: {
    type: String,
    default: 'button',
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  fullWidth: {
    type: Boolean,
    default: false,
  },
  rounded: {
    type: String,
    default: 'lg',
    validator: (value) => ['sm', 'md', 'lg', 'xl', 'full'].includes(value),
  },
});

const emit = defineEmits(['click']);

const buttonClasses = computed(() => {
  const classes = [
    'relative inline-flex items-center justify-center font-medium transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed',
  ];

  // Size
  const sizeClasses = {
    xs: 'px-2.5 py-1.5 text-xs',
    sm: 'px-3 py-2 text-sm',
    md: 'px-4 py-2.5 text-sm',
    lg: 'px-5 py-3 text-base',
    xl: 'px-6 py-3.5 text-base',
  };
  classes.push(sizeClasses[props.size]);

  // Variant
  const variantClasses = {
    primary: 'bg-brand-500 hover:bg-brand-600 text-white shadow-sm hover:shadow-md focus:ring-brand-500 dark:bg-brand-600 dark:hover:bg-brand-700',
    secondary: 'bg-surface-100 hover:bg-surface-200 text-surface-900 shadow-sm hover:shadow-md focus:ring-surface-500 dark:bg-surface-800 dark:hover:bg-surface-700 dark:text-surface-100',
    ghost: 'bg-transparent hover:bg-surface-100 text-surface-700 focus:ring-surface-500 dark:hover:bg-surface-800 dark:text-surface-300',
    danger: 'bg-danger-500 hover:bg-danger-600 text-white shadow-sm hover:shadow-md focus:ring-danger-500 dark:bg-danger-600 dark:hover:bg-danger-700',
    success: 'bg-success-500 hover:bg-success-600 text-white shadow-sm hover:shadow-md focus:ring-success-500 dark:bg-success-600 dark:hover:bg-success-700',
    outline: 'border-2 border-brand-500 bg-transparent hover:bg-brand-50 text-brand-500 focus:ring-brand-500 dark:hover:bg-brand-950 dark:text-brand-400',
  };
  classes.push(variantClasses[props.variant]);

  // Rounded
  const roundedClasses = {
    sm: 'rounded',
    md: 'rounded-md',
    lg: 'rounded-lg',
    xl: 'rounded-xl',
    full: 'rounded-full',
  };
  classes.push(roundedClasses[props.rounded]);

  // Full width
  if (props.fullWidth) {
    classes.push('w-full');
  }

  return classes.join(' ');
});

function handleClick(event) {
  if (!props.disabled && !props.loading) {
    emit('click', event);
  }
}
</script>
