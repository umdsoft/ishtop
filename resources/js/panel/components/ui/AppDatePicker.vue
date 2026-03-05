<template>
  <div>
    <label v-if="label" :for="inputId" class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">
      {{ label }}
      <span v-if="required" class="text-danger-500">*</span>
    </label>

    <div class="relative">
      <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
        <CalendarIcon class="h-5 w-5 text-surface-400 dark:text-surface-500" />
      </div>

      <input
        :id="inputId"
        :value="formattedDate"
        type="date"
        :min="min"
        :max="max"
        :disabled="disabled"
        :required="required"
        :class="inputClasses"
        @change="handleChange"
      />
    </div>

    <p v-if="error" class="mt-1.5 text-sm text-danger-600 dark:text-danger-400">{{ error }}</p>
    <p v-else-if="hint" class="mt-1.5 text-sm text-surface-500 dark:text-surface-400">{{ hint }}</p>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { CalendarIcon } from '@heroicons/vue/20/solid';

const props = defineProps({
  modelValue: {
    type: [String, Date],
    default: null,
  },
  label: {
    type: String,
    default: '',
  },
  min: {
    type: String,
    default: null,
  },
  max: {
    type: String,
    default: null,
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
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value),
  },
});

const emit = defineEmits(['update:modelValue']);

const inputId = `date-${Math.random().toString(36).substr(2, 9)}`;

const formattedDate = computed(() => {
  if (!props.modelValue) return '';

  if (props.modelValue instanceof Date) {
    return props.modelValue.toISOString().split('T')[0];
  }

  return props.modelValue;
});

const inputClasses = computed(() => {
  const classes = [
    'block w-full pl-10 border rounded-lg transition-colors duration-200',
    'focus:outline-none focus:ring-2 focus:ring-brand-500',
    'dark:bg-surface-900 dark:text-surface-100',
  ];

  // Size
  const sizeClasses = {
    sm: 'pr-3 py-1.5 text-sm',
    md: 'pr-3 py-2 text-sm',
    lg: 'pr-4 py-2.5 text-base',
  };
  classes.push(sizeClasses[props.size]);

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

function handleChange(event) {
  const value = event.target.value;
  emit('update:modelValue', value);
}
</script>
