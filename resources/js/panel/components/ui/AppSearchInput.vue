<template>
  <div class="relative">
    <div class="relative">
      <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
        <MagnifyingGlassIcon class="h-5 w-5 text-surface-400 dark:text-surface-500" />
      </div>

      <input
        :value="modelValue"
        type="text"
        :placeholder="placeholder"
        :disabled="disabled"
        :class="inputClasses"
        @input="handleInput"
        @focus="isFocused = true"
        @blur="isFocused = false"
      />

      <div v-if="modelValue || loading" class="absolute inset-y-0 right-0 pr-3 flex items-center">
        <AppLoadingSpinner v-if="loading" size="xs" />
        <button
          v-else
          type="button"
          class="p-1 rounded-md text-surface-400 hover:text-surface-600 dark:hover:text-surface-300 transition-colors"
          @click="handleClear"
        >
          <XMarkIcon class="h-4 w-4" />
        </button>
      </div>
    </div>

    <p v-if="hint" class="mt-1.5 text-sm text-surface-500 dark:text-surface-400">{{ hint }}</p>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { MagnifyingGlassIcon, XMarkIcon } from '@heroicons/vue/20/solid';
import AppLoadingSpinner from './AppLoadingSpinner.vue';

const props = defineProps({
  modelValue: {
    type: String,
    default: '',
  },
  placeholder: {
    type: String,
    default: 'Qidirish...',
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  hint: {
    type: String,
    default: '',
  },
  debounce: {
    type: Number,
    default: 300,
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value),
  },
});

const emit = defineEmits(['update:modelValue', 'search']);

const isFocused = ref(false);
let debounceTimeout = null;

const inputClasses = computed(() => {
  const classes = [
    'block w-full pl-10 pr-10 border border-surface-300 dark:border-surface-600 rounded-lg',
    'focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500',
    'placeholder:text-surface-400 dark:placeholder:text-surface-500',
    'bg-white dark:bg-surface-900 text-surface-900 dark:text-surface-100',
    'transition-all duration-200',
  ];

  // Size
  const sizeClasses = {
    sm: 'py-1.5 text-sm',
    md: 'py-2 text-sm',
    lg: 'py-2.5 text-base',
  };
  classes.push(sizeClasses[props.size]);

  if (props.disabled) {
    classes.push('opacity-50 cursor-not-allowed bg-surface-50 dark:bg-surface-800');
  }

  if (isFocused.value) {
    classes.push('ring-2 ring-brand-500');
  }

  return classes.join(' ');
});

function handleInput(event) {
  const value = event.target.value;
  emit('update:modelValue', value);

  if (debounceTimeout) {
    clearTimeout(debounceTimeout);
  }

  debounceTimeout = setTimeout(() => {
    emit('search', value);
  }, props.debounce);
}

function handleClear() {
  emit('update:modelValue', '');
  emit('search', '');

  if (debounceTimeout) {
    clearTimeout(debounceTimeout);
  }
}
</script>
