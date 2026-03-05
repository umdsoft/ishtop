<template>
  <div class="flex items-start gap-3">
    <div class="flex items-center h-5">
      <input
        :id="inputId"
        type="checkbox"
        :checked="modelValue"
        :disabled="disabled"
        :required="required"
        :class="checkboxClasses"
        @change="$emit('update:modelValue', $event.target.checked)"
      />
    </div>
    <div v-if="label || description" class="flex-1">
      <label :for="inputId" class="block text-sm font-medium text-surface-700 dark:text-surface-300 cursor-pointer">
        {{ label }}
        <span v-if="required" class="text-danger-500">*</span>
      </label>
      <p v-if="description" class="text-sm text-surface-500 dark:text-surface-400 mt-0.5">
        {{ description }}
      </p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
  label: {
    type: String,
    default: '',
  },
  description: {
    type: String,
    default: '',
  },
  required: {
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

defineEmits(['update:modelValue']);

const inputId = `checkbox-${Math.random().toString(36).substr(2, 9)}`;

const checkboxClasses = computed(() => {
  const classes = [
    'rounded border-surface-300 dark:border-surface-600',
    'text-brand-600 focus:ring-brand-500 dark:focus:ring-brand-500',
    'transition-colors duration-200 cursor-pointer',
    'dark:bg-surface-900 dark:checked:bg-brand-600',
  ];

  // Size
  const sizeClasses = {
    sm: 'h-4 w-4',
    md: 'h-5 w-5',
    lg: 'h-6 w-6',
  };
  classes.push(sizeClasses[props.size]);

  if (props.disabled) {
    classes.push('opacity-50 cursor-not-allowed');
  }

  return classes.join(' ');
});
</script>
