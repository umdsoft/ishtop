<template>
  <div>
    <label v-if="label" :for="inputId" class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">
      {{ label }}
      <span v-if="required" class="text-danger-500">*</span>
    </label>

    <div class="relative">
      <textarea
        :id="inputId"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :required="required"
        :rows="rows"
        :maxlength="maxlength"
        :class="textareaClasses"
        @input="$emit('update:modelValue', $event.target.value)"
      />
    </div>

    <div v-if="maxlength || error || hint" class="flex items-start justify-between mt-1.5 gap-2">
      <div class="flex-1">
        <p v-if="error" class="text-sm text-danger-600 dark:text-danger-400">{{ error }}</p>
        <p v-else-if="hint" class="text-sm text-surface-500 dark:text-surface-400">{{ hint }}</p>
      </div>
      <div v-if="maxlength" class="text-xs text-surface-500 dark:text-surface-400 whitespace-nowrap">
        {{ modelValue?.length || 0 }}/{{ maxlength }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  modelValue: {
    type: String,
    default: '',
  },
  label: {
    type: String,
    default: '',
  },
  placeholder: {
    type: String,
    default: '',
  },
  error: {
    type: String,
    default: '',
  },
  hint: {
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
  rows: {
    type: Number,
    default: 4,
  },
  maxlength: {
    type: Number,
    default: null,
  },
  resize: {
    type: String,
    default: 'vertical',
    validator: (value) => ['none', 'vertical', 'horizontal', 'both'].includes(value),
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value),
  },
});

defineEmits(['update:modelValue']);

const inputId = `textarea-${Math.random().toString(36).substr(2, 9)}`;

const textareaClasses = computed(() => {
  const classes = [
    'block w-full rounded-lg border transition-colors duration-200',
    'focus:outline-none focus:ring-2 focus:ring-brand-500',
    'placeholder:text-surface-400 dark:placeholder:text-surface-500',
    'dark:bg-surface-900 dark:text-surface-100',
  ];

  // Size
  const sizeClasses = {
    sm: 'px-3 py-1.5 text-sm',
    md: 'px-3 py-2 text-sm',
    lg: 'px-4 py-2.5 text-base',
  };
  classes.push(sizeClasses[props.size]);

  // Resize
  const resizeClasses = {
    none: 'resize-none',
    vertical: 'resize-y',
    horizontal: 'resize-x',
    both: 'resize',
  };
  classes.push(resizeClasses[props.resize]);

  // State
  if (props.error) {
    classes.push('border-danger-500 dark:border-danger-500 focus:ring-danger-500');
  } else {
    classes.push('border-surface-300 dark:border-surface-600 hover:border-surface-400 dark:hover:border-surface-500');
  }

  if (props.disabled) {
    classes.push('opacity-50 cursor-not-allowed bg-surface-50 dark:bg-surface-800');
  }

  return classes.join(' ');
});
</script>
