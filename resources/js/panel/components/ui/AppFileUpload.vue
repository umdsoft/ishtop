<template>
  <div>
    <label v-if="label" class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">
      {{ label }}
      <span v-if="required" class="text-danger-500">*</span>
    </label>

    <div
      :class="[
        'relative border-2 border-dashed rounded-lg transition-colors duration-200',
        isDragging
          ? 'border-brand-500 bg-brand-50 dark:bg-brand-900/20'
          : error
          ? 'border-danger-300 dark:border-danger-700 bg-danger-50 dark:bg-danger-900/10'
          : 'border-surface-300 dark:border-surface-600 hover:border-surface-400 dark:hover:border-surface-500',
        disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer',
      ]"
      @click="!disabled && $refs.fileInput.click()"
      @dragover.prevent="!disabled && (isDragging = true)"
      @dragleave.prevent="isDragging = false"
      @drop.prevent="!disabled && handleDrop($event)"
    >
      <input
        ref="fileInput"
        type="file"
        :accept="accept"
        :multiple="multiple"
        :disabled="disabled"
        class="hidden"
        @change="handleFileChange"
      />

      <div class="p-8 text-center">
        <CloudArrowUpIcon class="mx-auto h-12 w-12 text-surface-400 dark:text-surface-500" />
        <p class="mt-2 text-sm text-surface-600 dark:text-surface-400">
          <span class="font-medium text-brand-600 dark:text-brand-400">Faylni tanlang</span>
          yoki bu yerga tashlang
        </p>
        <p v-if="accept" class="mt-1 text-xs text-surface-500 dark:text-surface-400">
          {{ accept }}
        </p>
        <p v-if="maxSize" class="mt-1 text-xs text-surface-500 dark:text-surface-400">
          Maksimal hajm: {{ formatFileSize(maxSize) }}
        </p>
      </div>
    </div>

    <div v-if="files.length > 0" class="mt-4 space-y-2">
      <div
        v-for="(file, index) in files"
        :key="index"
        class="flex items-center justify-between p-3 bg-surface-50 dark:bg-surface-900 rounded-lg"
      >
        <div class="flex items-center gap-3 flex-1 min-w-0">
          <DocumentIcon class="h-5 w-5 text-surface-400 dark:text-surface-500 flex-shrink-0" />
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-surface-900 dark:text-surface-100 truncate">
              {{ file.name }}
            </p>
            <p class="text-xs text-surface-500 dark:text-surface-400">
              {{ formatFileSize(file.size) }}
            </p>
          </div>
        </div>
        <button
          v-if="!disabled"
          type="button"
          class="ml-3 p-1 rounded-md text-surface-400 hover:text-danger-600 dark:hover:text-danger-400 transition-colors"
          @click.stop="removeFile(index)"
        >
          <XMarkIcon class="h-5 w-5" />
        </button>
      </div>
    </div>

    <p v-if="error" class="mt-1.5 text-sm text-danger-600 dark:text-danger-400">{{ error }}</p>
    <p v-else-if="hint" class="mt-1.5 text-sm text-surface-500 dark:text-surface-400">{{ hint }}</p>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { CloudArrowUpIcon, DocumentIcon, XMarkIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  modelValue: {
    type: [File, FileList, Array],
    default: null,
  },
  label: {
    type: String,
    default: '',
  },
  accept: {
    type: String,
    default: '',
  },
  multiple: {
    type: Boolean,
    default: false,
  },
  maxSize: {
    type: Number,
    default: null, // in bytes
  },
  required: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  error: {
    type: String,
    default: '',
  },
  hint: {
    type: String,
    default: '',
  },
});

const emit = defineEmits(['update:modelValue', 'error']);

const fileInput = ref(null);
const files = ref([]);
const isDragging = ref(false);

watch(() => props.modelValue, (newValue) => {
  if (!newValue) {
    files.value = [];
  } else if (newValue instanceof FileList || Array.isArray(newValue)) {
    files.value = Array.from(newValue);
  } else if (newValue instanceof File) {
    files.value = [newValue];
  }
}, { immediate: true });

function handleFileChange(event) {
  const selectedFiles = Array.from(event.target.files);
  processFiles(selectedFiles);
}

function handleDrop(event) {
  isDragging.value = false;
  const droppedFiles = Array.from(event.dataTransfer.files);
  processFiles(droppedFiles);
}

function processFiles(newFiles) {
  if (props.maxSize) {
    const oversizedFiles = newFiles.filter(file => file.size > props.maxSize);
    if (oversizedFiles.length > 0) {
      emit('error', `Fayl hajmi ${formatFileSize(props.maxSize)} dan oshmasligi kerak`);
      return;
    }
  }

  if (props.multiple) {
    files.value = [...files.value, ...newFiles];
    emit('update:modelValue', files.value);
  } else {
    files.value = [newFiles[0]];
    emit('update:modelValue', newFiles[0]);
  }
}

function removeFile(index) {
  files.value.splice(index, 1);

  if (props.multiple) {
    emit('update:modelValue', files.value.length > 0 ? files.value : null);
  } else {
    emit('update:modelValue', null);
  }
}

function formatFileSize(bytes) {
  if (bytes === 0) return '0 B';
  const k = 1024;
  const sizes = ['B', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
}
</script>
