<template>
  <TransitionRoot :show="show" as="template">
    <Dialog as="div" class="relative z-50" @close="handleClose">
      <TransitionChild
        as="template"
        enter="duration-300 ease-out"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="duration-200 ease-in"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-surface-900/50 dark:bg-surface-950/70 backdrop-blur-sm" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
          <TransitionChild
            as="template"
            enter="duration-300 ease-out"
            enter-from="opacity-0 scale-95"
            enter-to="opacity-100 scale-100"
            leave="duration-200 ease-in"
            leave-from="opacity-100 scale-100"
            leave-to="opacity-0 scale-95"
          >
            <DialogPanel :class="modalClasses">
              <div v-if="title || $slots.header" class="flex items-center justify-between px-6 py-4 border-b border-surface-200 dark:border-surface-800">
                <DialogTitle as="h3" class="text-lg font-semibold text-surface-900 dark:text-surface-100">
                  <slot name="header">
                    {{ title }}
                  </slot>
                </DialogTitle>
                <button
                  v-if="closable"
                  type="button"
                  class="p-1 hover:bg-surface-100 dark:hover:bg-surface-800 rounded-lg transition-colors"
                  @click="handleClose"
                >
                  <XMarkIcon class="w-5 h-5 text-surface-500" />
                </button>
              </div>

              <div class="px-6 py-4">
                <slot />
              </div>

              <div v-if="$slots.footer" class="flex items-center justify-end gap-3 px-6 py-4 border-t border-surface-200 dark:border-surface-800 bg-surface-50 dark:bg-surface-900/50">
                <slot name="footer" />
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { computed } from 'vue';
import { Dialog, DialogPanel, DialogTitle, TransitionRoot, TransitionChild } from '@headlessui/vue';
import { XMarkIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  show: {
    type: Boolean,
    required: true,
  },
  title: {
    type: String,
    default: '',
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

const modalClasses = computed(() => {
  const classes = [
    'relative bg-surface-0 dark:bg-surface-900 rounded-2xl shadow-xl w-full',
    'border border-surface-200 dark:border-surface-800',
  ];

  const sizeClasses = {
    sm: 'max-w-sm',
    md: 'max-w-md',
    lg: 'max-w-lg',
    xl: 'max-w-xl',
    '2xl': 'max-w-2xl',
    full: 'max-w-full mx-4',
  };

  classes.push(sizeClasses[props.size]);

  return classes.join(' ');
});

function handleClose() {
  if (props.closable) {
    emit('close');
  }
}
</script>
