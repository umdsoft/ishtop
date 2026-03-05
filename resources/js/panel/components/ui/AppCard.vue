<template>
  <div :class="cardClasses">
    <div v-if="$slots.header || title" class="px-6 py-4 border-b border-surface-200 dark:border-surface-700">
      <slot name="header">
        <h3 class="text-lg font-semibold text-surface-900 dark:text-surface-100">
          {{ title }}
        </h3>
      </slot>
    </div>

    <div :class="bodyClasses">
      <slot />
    </div>

    <div v-if="$slots.footer" class="px-6 py-4 border-t border-surface-200 dark:border-surface-700 bg-surface-50 dark:bg-surface-900/50">
      <slot name="footer" />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  title: {
    type: String,
    default: '',
  },
  noPadding: {
    type: Boolean,
    default: false,
  },
  hover: {
    type: Boolean,
    default: false,
  },
  rounded: {
    type: String,
    default: 'xl',
    validator: (value) => ['lg', 'xl', '2xl'].includes(value),
  },
});

const cardClasses = computed(() => {
  const classes = [
    'bg-surface-0 dark:bg-surface-900 border border-surface-200 dark:border-surface-800',
    'shadow-sm transition-all duration-200',
  ];

  // Rounded
  const roundedClasses = {
    lg: 'rounded-lg',
    xl: 'rounded-xl',
    '2xl': 'rounded-2xl',
  };
  classes.push(roundedClasses[props.rounded]);

  // Hover effect
  if (props.hover) {
    classes.push('hover:shadow-hover hover:border-brand-200 dark:hover:border-brand-800 cursor-pointer');
  }

  return classes.join(' ');
});

const bodyClasses = computed(() => {
  if (props.noPadding) {
    return '';
  }
  return 'px-6 py-4';
});
</script>
