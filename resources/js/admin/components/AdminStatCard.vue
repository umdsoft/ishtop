<template>
  <div class="bg-surface-0 dark:bg-surface-900 border border-surface-200 dark:border-surface-800 rounded-xl p-4 shadow-sm">
    <div class="flex items-start justify-between">
      <div>
        <p class="text-sm text-surface-500 dark:text-surface-400">{{ label }}</p>
        <p class="text-2xl font-bold text-surface-900 dark:text-surface-100 mt-1">{{ value }}</p>
        <p v-if="description" class="text-xs mt-1" :class="descriptionColor">
          <span v-if="trend !== null" class="inline-flex items-center gap-0.5">
            <component :is="trend >= 0 ? ArrowTrendingUpIcon : ArrowTrendingDownIcon" class="w-3 h-3" />
            {{ trend }}%
          </span>
          {{ description }}
        </p>
      </div>
      <div :class="['w-10 h-10 rounded-lg flex items-center justify-center', iconBg]">
        <component :is="icon" :class="['w-5 h-5', iconColor]" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { ArrowTrendingUpIcon, ArrowTrendingDownIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  label: { type: String, required: true },
  value: { type: [String, Number], required: true },
  description: { type: String, default: '' },
  trend: { type: Number, default: null },
  icon: { type: [Object, Function], required: true },
  color: { type: String, default: 'brand' },
});

const iconBg = computed(() => ({
  brand: 'bg-brand-100 dark:bg-brand-900/30',
  success: 'bg-success-100 dark:bg-success-900/30',
  warning: 'bg-warning-100 dark:bg-warning-900/30',
  danger: 'bg-danger-100 dark:bg-danger-900/30',
  info: 'bg-info-100 dark:bg-info-900/30',
  gray: 'bg-surface-100 dark:bg-surface-800',
}[props.color] || 'bg-brand-100 dark:bg-brand-900/30'));

const iconColor = computed(() => ({
  brand: 'text-brand-600 dark:text-brand-400',
  success: 'text-success-600 dark:text-success-400',
  warning: 'text-warning-600 dark:text-warning-400',
  danger: 'text-danger-600 dark:text-danger-400',
  info: 'text-info-600 dark:text-info-400',
  gray: 'text-surface-600 dark:text-surface-400',
}[props.color] || 'text-brand-600 dark:text-brand-400'));

const descriptionColor = computed(() => {
  if (props.trend === null) return 'text-surface-500 dark:text-surface-400';
  return props.trend >= 0
    ? 'text-success-600 dark:text-success-400'
    : 'text-danger-600 dark:text-danger-400';
});
</script>
