<template>
  <AppModal
    :open="open"
    size="sm"
    :closable="!loading"
    @close="handleCancel"
  >
    <template #header>
      <div class="flex items-center gap-3">
        <div
          :class="[
            'flex-shrink-0 flex items-center justify-center w-12 h-12 rounded-full',
            iconBgClasses,
          ]"
        >
          <component :is="icon" :class="['h-6 w-6', iconColorClasses]" />
        </div>
        <h3 class="text-lg font-semibold text-surface-900 dark:text-surface-100">
          {{ title }}
        </h3>
      </div>
    </template>

    <p class="text-sm text-surface-600 dark:text-surface-400">
      {{ message }}
    </p>

    <template #footer>
      <div class="flex gap-3 justify-end">
        <AppButton
          variant="secondary"
          :disabled="loading"
          @click="handleCancel"
        >
          {{ cancelText }}
        </AppButton>
        <AppButton
          :variant="confirmVariant"
          :loading="loading"
          @click="handleConfirm"
        >
          {{ confirmText }}
        </AppButton>
      </div>
    </template>
  </AppModal>
</template>

<script setup>
import { computed } from 'vue';
import { ExclamationTriangleIcon, TrashIcon, CheckCircleIcon, InformationCircleIcon } from '@heroicons/vue/24/outline';
import AppModal from './AppModal.vue';
import AppButton from './AppButton.vue';

const props = defineProps({
  open: {
    type: Boolean,
    required: true,
  },
  title: {
    type: String,
    default: 'Tasdiqlash',
  },
  message: {
    type: String,
    default: 'Ushbu amalni bajarishni tasdiqlaysizmi?',
  },
  type: {
    type: String,
    default: 'warning',
    validator: (value) => ['warning', 'danger', 'success', 'info'].includes(value),
  },
  confirmText: {
    type: String,
    default: 'Tasdiqlash',
  },
  cancelText: {
    type: String,
    default: 'Bekor qilish',
  },
  loading: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['confirm', 'cancel', 'close']);

const icon = computed(() => {
  const icons = {
    warning: ExclamationTriangleIcon,
    danger: TrashIcon,
    success: CheckCircleIcon,
    info: InformationCircleIcon,
  };
  return icons[props.type];
});

const iconBgClasses = computed(() => {
  const classes = {
    warning: 'bg-warning-100 dark:bg-warning-900/20',
    danger: 'bg-danger-100 dark:bg-danger-900/20',
    success: 'bg-success-100 dark:bg-success-900/20',
    info: 'bg-info-100 dark:bg-info-900/20',
  };
  return classes[props.type];
});

const iconColorClasses = computed(() => {
  const classes = {
    warning: 'text-warning-600 dark:text-warning-400',
    danger: 'text-danger-600 dark:text-danger-400',
    success: 'text-success-600 dark:text-success-400',
    info: 'text-info-600 dark:text-info-400',
  };
  return classes[props.type];
});

const confirmVariant = computed(() => {
  const variants = {
    warning: 'primary',
    danger: 'danger',
    success: 'success',
    info: 'primary',
  };
  return variants[props.type];
});

function handleConfirm() {
  emit('confirm');
}

function handleCancel() {
  if (!props.loading) {
    emit('cancel');
    emit('close');
  }
}
</script>
