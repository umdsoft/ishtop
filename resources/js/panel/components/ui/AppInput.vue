<template>
  <div class="w-full">
    <label
      v-if="label"
      :for="inputId"
      class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5"
    >
      {{ label }}
      <span v-if="required" class="text-danger-500">*</span>
    </label>

    <div class="relative">
      <div v-if="$slots['icon-left']" class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-surface-400">
        <slot name="icon-left" />
      </div>

      <input
        :id="inputId"
        :type="type"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :required="required"
        :autocomplete="autocomplete"
        :class="inputClasses"
        @input="handleInput"
        @blur="handleBlur"
        @focus="handleFocus"
      />

      <div v-if="$slots['icon-right']" class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-surface-400">
        <slot name="icon-right" />
      </div>
    </div>

    <p v-if="error" class="mt-1.5 text-sm text-danger-500">
      {{ error }}
    </p>

    <p v-else-if="hint" class="mt-1.5 text-sm text-surface-500 dark:text-surface-400">
      {{ hint }}
    </p>
  </div>
</template>

<script setup>
import { computed, useSlots } from 'vue';

const slots = useSlots();

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: '',
  },
  type: {
    type: String,
    default: 'text',
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
  disabled: {
    type: Boolean,
    default: false,
  },
  required: {
    type: Boolean,
    default: false,
  },
  autocomplete: {
    type: String,
    default: 'off',
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value),
  },
});

const emit = defineEmits(['update:modelValue', 'blur', 'focus']);

const inputId = computed(() => `input-${Math.random().toString(36).substr(2, 9)}`);

const inputClasses = computed(() => {
  const classes = [
    'block w-full rounded-lg border transition-all duration-200',
    'focus:outline-none focus:ring-2 focus:ring-offset-0',
    'disabled:bg-surface-100 disabled:cursor-not-allowed disabled:text-surface-500',
    'dark:bg-surface-900 dark:disabled:bg-surface-800',
  ];

  // Size
  const sizeClasses = {
    sm: 'px-3 py-1.5 text-sm',
    md: 'px-4 py-2.5 text-sm',
    lg: 'px-5 py-3 text-base',
  };
  classes.push(sizeClasses[props.size]);

  // Icon padding
  if (slots['icon-left']) {
    classes.push('pl-10');
  }
  if (slots['icon-right']) {
    classes.push('pr-10');
  }

  // Error state
  if (props.error) {
    classes.push(
      'border-danger-300 text-danger-900 placeholder-danger-300',
      'focus:border-danger-500 focus:ring-danger-500',
      'dark:border-danger-700 dark:text-danger-100'
    );
  } else {
    classes.push(
      'border-surface-300 text-surface-900 placeholder-surface-400',
      'focus:border-brand-500 focus:ring-brand-500',
      'dark:border-surface-700 dark:text-surface-100 dark:placeholder-surface-500'
    );
  }

  return classes.join(' ');
});

function handleInput(event) {
  emit('update:modelValue', event.target.value);
}

function handleBlur(event) {
  emit('blur', event);
}

function handleFocus(event) {
  emit('focus', event);
}
</script>
