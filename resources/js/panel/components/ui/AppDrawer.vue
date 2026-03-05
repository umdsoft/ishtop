<template>
  <TransitionRoot as="template" :show="open">
    <Dialog as="div" class="relative z-50" @close="handleClose">
      <TransitionChild
        as="template"
        enter="ease-in-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in-out duration-300"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-surface-950/50 backdrop-blur-sm transition-opacity" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-hidden">
        <div class="absolute inset-0 overflow-hidden">
          <div :class="['pointer-events-none fixed inset-y-0 flex max-w-full', positionClasses]">
            <TransitionChild
              as="template"
              enter="transform transition ease-in-out duration-300"
              :enter-from="enterFromClasses"
              enter-to="translate-x-0"
              leave="transform transition ease-in-out duration-300"
              leave-from="translate-x-0"
              :leave-to="leaveToClasses"
            >
              <DialogPanel :class="['pointer-events-auto relative', widthClasses]">
                <TransitionChild
                  v-if="closable"
                  as="template"
                  enter="ease-in-out duration-300"
                  enter-from="opacity-0"
                  enter-to="opacity-100"
                  leave="ease-in-out duration-300"
                  leave-from="opacity-100"
                  leave-to="opacity-0"
                >
                  <div :class="['absolute top-0 flex pt-4 pr-2', closeButtonPositionClasses]">
                    <button
                      type="button"
                      class="relative rounded-md text-surface-400 hover:text-surface-500 dark:hover:text-surface-300 focus:outline-none focus:ring-2 focus:ring-brand-500"
                      @click="handleClose"
                    >
                      <span class="absolute -inset-2.5" />
                      <span class="sr-only">Close panel</span>
                      <XMarkIcon class="h-6 w-6" aria-hidden="true" />
                    </button>
                  </div>
                </TransitionChild>

                <div class="flex h-full flex-col overflow-y-auto bg-white dark:bg-surface-950 shadow-xl">
                  <div v-if="$slots.header || title" class="px-6 py-6 border-b border-surface-200 dark:border-surface-800">
                    <slot name="header">
                      <DialogTitle class="text-xl font-semibold text-surface-900 dark:text-surface-100">
                        {{ title }}
                      </DialogTitle>
                    </slot>
                  </div>

                  <div class="flex-1 px-6 py-6">
                    <slot />
                  </div>

                  <div v-if="$slots.footer" class="border-t border-surface-200 dark:border-surface-800 px-6 py-4">
                    <slot name="footer" />
                  </div>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { computed } from 'vue';
import {
  Dialog,
  DialogPanel,
  DialogTitle,
  TransitionChild,
  TransitionRoot,
} from '@headlessui/vue';
import { XMarkIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  open: {
    type: Boolean,
    required: true,
  },
  title: {
    type: String,
    default: '',
  },
  position: {
    type: String,
    default: 'right',
    validator: (value) => ['left', 'right'].includes(value),
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg', 'xl', '2xl', 'full'].includes(value),
  },
  closable: {
    type: Boolean,
    default: true,
  },
});

const emit = defineEmits(['close']);

const widthClasses = computed(() => {
  const widths = {
    sm: 'w-screen max-w-sm',
    md: 'w-screen max-w-md',
    lg: 'w-screen max-w-lg',
    xl: 'w-screen max-w-xl',
    '2xl': 'w-screen max-w-2xl',
    full: 'w-screen',
  };
  return widths[props.size];
});

const positionClasses = computed(() => {
  return props.position === 'right' ? 'right-0' : 'left-0';
});

const enterFromClasses = computed(() => {
  return props.position === 'right' ? 'translate-x-full' : '-translate-x-full';
});

const leaveToClasses = computed(() => {
  return props.position === 'right' ? 'translate-x-full' : '-translate-x-full';
});

const closeButtonPositionClasses = computed(() => {
  return props.position === 'right' ? '-ml-8 sm:-ml-10' : '-mr-8 sm:-mr-10';
});

function handleClose() {
  if (props.closable) {
    emit('close');
  }
}
</script>
