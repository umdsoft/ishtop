<template>
  <div>
    <label v-if="label" :for="inputId" class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">
      {{ label }}
      <span v-if="required" class="text-danger-500">*</span>
    </label>

    <div class="flex items-center gap-3">
      <div class="relative">
        <input
          :id="inputId"
          :value="modelValue"
          type="color"
          :disabled="disabled"
          :required="required"
          class="h-10 w-10 rounded-lg border border-surface-300 dark:border-surface-600 cursor-pointer disabled:cursor-not-allowed disabled:opacity-50"
          @input="handleChange"
        />
      </div>

      <AppInput
        :model-value="modelValue"
        placeholder="#000000"
        :disabled="disabled"
        :error="error"
        class="flex-1"
        @update:model-value="handleInputChange"
      />

      <div
        v-if="swatches.length > 0"
        class="flex gap-2"
      >
        <button
          v-for="(swatch, index) in swatches"
          :key="index"
          type="button"
          :style="{ backgroundColor: swatch }"
          :class="[
            'h-8 w-8 rounded-md border-2 transition-all',
            modelValue === swatch
              ? 'border-brand-500 ring-2 ring-brand-500 ring-offset-2 dark:ring-offset-surface-900'
              : 'border-surface-300 dark:border-surface-600 hover:border-surface-400 dark:hover:border-surface-500',
            disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer',
          ]"
          :disabled="disabled"
          @click="handleSwatchClick(swatch)"
        />
      </div>
    </div>

    <p v-if="error" class="mt-1.5 text-sm text-danger-600 dark:text-danger-400">{{ error }}</p>
    <p v-else-if="hint" class="mt-1.5 text-sm text-surface-500 dark:text-surface-400">{{ hint }}</p>
  </div>
</template>

<script setup>
import AppInput from './AppInput.vue';

const props = defineProps({
  modelValue: {
    type: String,
    default: '#000000',
  },
  label: {
    type: String,
    default: '',
  },
  swatches: {
    type: Array,
    default: () => [],
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
});

const emit = defineEmits(['update:modelValue']);

const inputId = `color-${Math.random().toString(36).substr(2, 9)}`;

function handleChange(event) {
  emit('update:modelValue', event.target.value);
}

function handleInputChange(value) {
  if (/^#[0-9A-F]{6}$/i.test(value)) {
    emit('update:modelValue', value);
  }
}

function handleSwatchClick(color) {
  if (!props.disabled) {
    emit('update:modelValue', color);
  }
}
</script>
