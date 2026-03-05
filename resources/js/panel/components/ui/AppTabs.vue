<template>
  <div>
    <TabGroup :selected-index="selectedIndex" @change="handleChange">
      <TabList :class="['flex gap-1', variantClasses]">
        <Tab
          v-for="tab in tabs"
          :key="tab.key"
          v-slot="{ selected }"
          as="template"
        >
          <button
            :class="[
              'px-4 py-2.5 text-sm font-medium transition-all duration-200 focus:outline-none',
              selected ? selectedClasses : unselectedClasses,
              tab.disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer',
            ]"
            :disabled="tab.disabled"
          >
            <div class="flex items-center gap-2">
              <component v-if="tab.icon" :is="tab.icon" class="h-4 w-4" />
              <span>{{ tab.label }}</span>
              <AppBadge v-if="tab.badge" variant="primary" size="sm" class="ml-1">
                {{ tab.badge }}
              </AppBadge>
            </div>
          </button>
        </Tab>
      </TabList>

      <TabPanels class="mt-4">
        <TabPanel
          v-for="tab in tabs"
          :key="tab.key"
          :class="[
            'focus:outline-none',
            animated ? 'transition-opacity duration-200' : '',
          ]"
        >
          <slot :name="`panel-${tab.key}`" />
        </TabPanel>
      </TabPanels>
    </TabGroup>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { TabGroup, TabList, Tab, TabPanels, TabPanel } from '@headlessui/vue';
import AppBadge from './AppBadge.vue';

const props = defineProps({
  tabs: {
    type: Array,
    required: true,
    // Expected format: [{ key: 'profile', label: 'Profile', icon: UserIcon, badge: 3, disabled: false }, ...]
  },
  modelValue: {
    type: [String, Number],
    default: null,
  },
  variant: {
    type: String,
    default: 'underline',
    validator: (value) => ['underline', 'pills', 'bordered'].includes(value),
  },
  animated: {
    type: Boolean,
    default: true,
  },
});

const emit = defineEmits(['update:modelValue', 'change']);

const selectedIndex = ref(0);

// Initialize selectedIndex from modelValue
watch(() => props.modelValue, (newValue) => {
  if (newValue !== null) {
    const index = props.tabs.findIndex(tab => tab.key === newValue);
    if (index !== -1) {
      selectedIndex.value = index;
    }
  }
}, { immediate: true });

const variantClasses = computed(() => {
  const variants = {
    underline: 'border-b border-surface-200 dark:border-surface-700',
    pills: 'bg-surface-100 dark:bg-surface-900 p-1 rounded-lg',
    bordered: 'border border-surface-200 dark:border-surface-700 rounded-lg p-1',
  };
  return variants[props.variant];
});

const selectedClasses = computed(() => {
  const variants = {
    underline: 'text-brand-600 dark:text-brand-400 border-b-2 border-brand-600 dark:border-brand-400',
    pills: 'bg-white dark:bg-surface-800 text-brand-700 dark:text-brand-300 shadow-sm rounded-md',
    bordered: 'bg-white dark:bg-surface-800 text-brand-700 dark:text-brand-300 shadow-sm rounded-md',
  };
  return variants[props.variant];
});

const unselectedClasses = computed(() => {
  const variants = {
    underline: 'text-surface-600 dark:text-surface-400 hover:text-surface-900 dark:hover:text-surface-100 border-b-2 border-transparent',
    pills: 'text-surface-700 dark:text-surface-300 hover:text-surface-900 dark:hover:text-surface-100 rounded-md',
    bordered: 'text-surface-700 dark:text-surface-300 hover:text-surface-900 dark:hover:text-surface-100 rounded-md',
  };
  return variants[props.variant];
});

function handleChange(index) {
  selectedIndex.value = index;
  const tab = props.tabs[index];
  emit('update:modelValue', tab.key);
  emit('change', tab.key);
}
</script>
