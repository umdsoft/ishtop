<template>
  <div class="relative">
    <label v-if="label" :for="inputId" class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">
      {{ label }}
      <span v-if="required" class="text-danger-500">*</span>
    </label>

    <Listbox v-model="selectedValue" :disabled="disabled">
      <div class="relative">
        <ListboxButton
          ref="buttonRef"
          :id="inputId"
          :class="buttonClasses"
        >
          <span v-if="selectedValue" class="block truncate">{{ getOptionLabel(selectedValue) }}</span>
          <span v-else class="block truncate text-surface-400 dark:text-surface-500">{{ placeholder }}</span>
          <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
            <ChevronUpDownIcon class="h-5 w-5 text-surface-400" aria-hidden="true" />
          </span>
        </ListboxButton>

        <transition
          enter-active-class="transition duration-150 ease-out"
          enter-from-class="opacity-0 scale-95"
          enter-to-class="opacity-100 scale-100"
          leave-active-class="transition duration-100 ease-in"
          leave-from-class="opacity-100 scale-100"
          leave-to-class="opacity-0 scale-95"
          @before-enter="updateDropdownPosition"
        >
          <ListboxOptions
            :class="[
              'absolute z-[60] max-h-60 w-full overflow-auto rounded-lg bg-white dark:bg-surface-800 py-1 text-base shadow-xl ring-1 ring-black/10 dark:ring-white/10 focus:outline-none sm:text-sm select-dropdown',
              flipUp ? 'bottom-full mb-1' : 'top-full mt-1'
            ]"
          >
            <div v-if="searchable" class="sticky top-0 z-10 bg-white dark:bg-surface-800 px-2 py-1.5 border-b border-surface-200 dark:border-surface-700">
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Qidirish..."
                class="w-full px-3 py-1.5 text-sm border border-surface-300 dark:border-surface-600 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-500 dark:bg-surface-900 dark:text-surface-100"
                @click.stop
              />
            </div>

            <ListboxOption
              v-for="option in filteredOptions"
              :key="getOptionValue(option)"
              v-slot="{ active, selected }"
              :value="option"
              as="template"
            >
              <li
                :class="[
                  active ? 'bg-brand-50 dark:bg-brand-900/20 text-brand-700 dark:text-brand-300' : 'text-surface-900 dark:text-surface-100',
                  'relative cursor-pointer select-none py-2 pl-10 pr-4',
                ]"
              >
                <span :class="[selected ? 'font-semibold' : 'font-normal', 'block truncate']">
                  {{ getOptionLabel(option) }}
                </span>
                <span
                  v-if="selected"
                  class="absolute inset-y-0 left-0 flex items-center pl-3 text-brand-600 dark:text-brand-400"
                >
                  <CheckIcon class="h-5 w-5" aria-hidden="true" />
                </span>
              </li>
            </ListboxOption>

            <div v-if="filteredOptions.length === 0" class="relative cursor-default select-none py-2 px-4 text-surface-500 dark:text-surface-400 text-center">
              Hech narsa topilmadi
            </div>
          </ListboxOptions>
        </transition>
      </div>
    </Listbox>

    <p v-if="error" class="mt-1.5 text-sm text-danger-600 dark:text-danger-400">{{ error }}</p>
    <p v-else-if="hint" class="mt-1.5 text-sm text-surface-500 dark:text-surface-400">{{ hint }}</p>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { Listbox, ListboxButton, ListboxOptions, ListboxOption } from '@headlessui/vue';
import { CheckIcon, ChevronUpDownIcon } from '@heroicons/vue/20/solid';

const props = defineProps({
  modelValue: {
    type: [String, Number, Object, null],
    default: null,
  },
  options: {
    type: Array,
    required: true,
  },
  label: {
    type: String,
    default: '',
  },
  placeholder: {
    type: String,
    default: 'Tanlang',
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
  searchable: {
    type: Boolean,
    default: false,
  },
  valueKey: {
    type: String,
    default: 'value',
  },
  labelKey: {
    type: String,
    default: 'label',
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value),
  },
});

const emit = defineEmits(['update:modelValue']);

const inputId = `select-${Math.random().toString(36).substr(2, 9)}`;
const searchQuery = ref('');
const buttonRef = ref(null);
const flipUp = ref(false);

const selectedValue = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value),
});

const filteredOptions = computed(() => {
  if (!props.searchable || !searchQuery.value) {
    return props.options;
  }

  const query = searchQuery.value.toLowerCase();
  return props.options.filter((option) => {
    const label = getOptionLabel(option).toLowerCase();
    return label.includes(query);
  });
});

function updateDropdownPosition() {
  const el = buttonRef.value?.$el || buttonRef.value;
  if (!el) return;
  const rect = el.getBoundingClientRect();
  const spaceBelow = window.innerHeight - rect.bottom;
  const dropdownHeight = Math.min(filteredOptions.value.length * 40 + 16, 240); // max-h-60 = 240px
  flipUp.value = spaceBelow < dropdownHeight + 8;
}

const buttonClasses = computed(() => {
  const classes = [
    'relative w-full cursor-pointer rounded-lg bg-white dark:bg-surface-900 text-left border',
    'focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500',
    'transition-colors duration-200',
  ];

  // Size
  const sizeClasses = {
    sm: 'py-1.5 pl-3 pr-10 text-sm',
    md: 'py-2 pl-3 pr-10 text-sm',
    lg: 'py-2.5 pl-4 pr-10 text-base',
  };
  classes.push(sizeClasses[props.size]);

  // State
  if (props.error) {
    classes.push('border-danger-500 dark:border-danger-500');
  } else {
    classes.push('border-surface-300 dark:border-surface-600 hover:border-surface-400 dark:hover:border-surface-500');
  }

  if (props.disabled) {
    classes.push('opacity-50 cursor-not-allowed bg-surface-50 dark:bg-surface-800');
  }

  return classes.join(' ');
});

function getOptionLabel(option) {
  if (typeof option === 'string' || typeof option === 'number') {
    return option;
  }
  return option[props.labelKey];
}

function getOptionValue(option) {
  if (typeof option === 'string' || typeof option === 'number') {
    return option;
  }
  return option[props.valueKey];
}

watch(() => selectedValue.value, () => {
  searchQuery.value = '';
});
</script>

<style scoped>
.select-dropdown {
  scrollbar-width: thin;
  scrollbar-color: rgba(156, 163, 175, 0.4) transparent;
}

.select-dropdown::-webkit-scrollbar {
  width: 6px;
}

.select-dropdown::-webkit-scrollbar-track {
  background: transparent;
}

.select-dropdown::-webkit-scrollbar-thumb {
  background-color: rgba(156, 163, 175, 0.4);
  border-radius: 3px;
}

.select-dropdown::-webkit-scrollbar-thumb:hover {
  background-color: rgba(156, 163, 175, 0.6);
}
</style>
