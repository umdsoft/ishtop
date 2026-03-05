<template>
  <Switch
    v-model="enabled"
    :disabled="disabled"
    :class="[
      enabled ? 'bg-brand-600' : 'bg-surface-200 dark:bg-surface-700',
      disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer',
      'relative inline-flex items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 dark:focus:ring-offset-surface-900',
      sizeClasses,
    ]"
  >
    <span
      :class="[
        enabled ? togglePositionOn : 'translate-x-1',
        'inline-block transform rounded-full bg-white transition-transform duration-200',
        toggleSizeClasses,
      ]"
    />
  </Switch>
</template>

<script setup>
import { computed } from 'vue';
import { Switch } from '@headlessui/vue';

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value),
  },
});

const emit = defineEmits(['update:modelValue']);

const enabled = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value),
});

const sizeClasses = computed(() => {
  const sizes = {
    sm: 'h-5 w-9',
    md: 'h-6 w-11',
    lg: 'h-7 w-14',
  };
  return sizes[props.size];
});

const toggleSizeClasses = computed(() => {
  const sizes = {
    sm: 'h-3 w-3',
    md: 'h-4 w-4',
    lg: 'h-5 w-5',
  };
  return sizes[props.size];
});

const togglePositionOn = computed(() => {
  const positions = {
    sm: 'translate-x-5',
    md: 'translate-x-6',
    lg: 'translate-x-8',
  };
  return positions[props.size];
});
</script>
