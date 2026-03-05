<template>
  <div class="relative inline-block" @mouseenter="show = true" @mouseleave="show = false">
    <slot />
    <Transition
      enter-active-class="transition ease-out duration-100"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="show && content"
        :class="[
          'absolute z-50 px-3 py-1.5 text-xs font-medium text-white bg-surface-900 dark:bg-surface-700 rounded-lg shadow-lg whitespace-nowrap pointer-events-none',
          positionClasses,
        ]"
      >
        {{ content }}
        <div :class="['absolute w-2 h-2 bg-surface-900 dark:bg-surface-700 transform rotate-45', arrowClasses]" />
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  content: {
    type: String,
    required: true,
  },
  position: {
    type: String,
    default: 'top',
    validator: (value) => ['top', 'bottom', 'left', 'right'].includes(value),
  },
});

const show = ref(false);

const positionClasses = computed(() => {
  const positions = {
    top: 'bottom-full left-1/2 -translate-x-1/2 mb-2',
    bottom: 'top-full left-1/2 -translate-x-1/2 mt-2',
    left: 'right-full top-1/2 -translate-y-1/2 mr-2',
    right: 'left-full top-1/2 -translate-y-1/2 ml-2',
  };
  return positions[props.position];
});

const arrowClasses = computed(() => {
  const arrows = {
    top: 'top-full left-1/2 -translate-x-1/2 -mt-1',
    bottom: 'bottom-full left-1/2 -translate-x-1/2 -mb-1',
    left: 'left-full top-1/2 -translate-y-1/2 -ml-1',
    right: 'right-full top-1/2 -translate-y-1/2 -mr-1',
  };
  return arrows[props.position];
});
</script>
